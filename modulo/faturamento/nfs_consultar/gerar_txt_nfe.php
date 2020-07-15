<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../');

$caracteres_invalidos 	= '���������������������������Ǫ��"�';
$caracteres_validos 	= 'AAEIOUAOAEIOUCAAEIOUAOAEIOUC     ';
$cofins_importacao      = genericas::variavel(61);
$vetor_classific_fiscal = array();//Utilizado mais abaixo ...
$vetor_iva              = array();//Utilizado mais abaixo ...

$quebrar_linha          = chr(13).chr(10);
$texto 			= 'NOTA FISCAL|1'.$quebrar_linha;

//Tratamento com as vari�veis que vem por par�metro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf              = $_POST['id_nf'];
    $id_nf_outra        = $_POST['id_nf_outra'];
    $txt_quantidade     = $_POST['txt_quantidade'];
    $txt_especie        = $_POST['txt_especie'];
    $txt_peso_bruto     = $_POST['txt_peso_bruto'];
    $txt_peso_liquido	= $_POST['txt_peso_liquido'];
}else {
    $id_nf              = $_GET['id_nf'];
    $id_nf_outra        = $_GET['id_nf_outra'];
    $txt_quantidade     = $_GET['txt_quantidade'];
    $txt_especie        = $_GET['txt_especie'];
    $txt_peso_bruto     = $_GET['txt_peso_bruto'];
    $txt_peso_liquido	= $_GET['txt_peso_liquido'];
}

/*
Estrutura do Layout ...

1) Cabe�alho Unificados p/ NF de Sa�da / NF Devolu��o / NF Outras ...
    1.1 Dados de Nota Fiscal
    1.2 Dados de Empresa
    1.3 Dados de Cliente

2) Itens
    2.1 Itens de Nota Fiscal de Sa�da / NF Devolu��o ...
 
    2.2 Itens de NF Outras / Complementar ...
    2.2.1 NF de Importa��o
    2.2.2 QQ outro Tipo de Nota ...

3) Com certeza � poss�vel estar unificando Dados de Rodap�, mas no momento isso n�o ser� feito ...*/

//Layout para NF de Sa�da e de Devolu��o ...
if($id_nf > 0) {
    //Busca dos dados da NF com o id_nf que foi passado por par�metro ...
    $sql = "SELECT `id_nf`, `id_cliente`, `id_empresa`, `id_nf_num_nota`, `id_nf_vide_nota`, `finalidade`, `frete_transporte`, `natureza_operacao`, 
            `snf_devolvida`, `despesas_acessorias`, `valor_frete`, DATE_FORMAT(`data_emissao_snf`, '%d/%m/%Y') AS data_emissao_snf, `data_emissao`, 
            `data_bl`, REPLACE(`chave_acesso`, ' ', '') AS chave_acesso, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4`, 
            `peso_bruto_balanca`, `data_saida_entrada`, `id_transportadora`, `texto_nf`, `trading`, `suframa`, `suframa_ativo`, `status` 
            FROM `nfs` 
            WHERE `id_nf` = '$id_nf' LIMIT 1 ";
    $campos_nfs                 = bancos::sql($sql);
    $id_empresa_nota            = $campos_nfs[0]['id_empresa'];//Essa vari�vel � renomeada porque temos uma $id_empresa na Sess�o ...
    $id_nf_num_nota             = $campos_nfs[0]['id_nf_num_nota'];
    $texto_nf			= $campos_nfs[0]['texto_nf'];
    $id_nf_vide_nota		= $campos_nfs[0]['id_nf_vide_nota'];
    $finalidade                 = $campos_nfs[0]['finalidade'];
    $frete_transporte		= $campos_nfs[0]['frete_transporte'];
    $natureza_operacao		= $campos_nfs[0]['natureza_operacao'];
    $data_emissao               = $campos_nfs[0]['data_emissao'];
    $status                     = $campos_nfs[0]['status'];
    //Aqui verifica o Tipo de Nota
    $nota_sgd                   = ($id_empresa_nota == 1 || $id_empresa_nota == 2) ? 'N' : 'S';//var surti efeito l� embaixo
    $peso_bruto_balanca         = $campos_nfs[0]['peso_bruto_balanca'];
    $tipo_nf                    = ($status == 6) ? 0 : 1;//Entrada = 0, Sa�da = 1 ...
    $data_saida                 = ($campos_nfs[0]['data_saida_entrada'] != '0000-00-00') ? $campos_nfs[0]['data_saida_entrada'].'T'.date('H:i').':00-03:00' : '';
    
    //Busca dos dados da Empresa ...
    $sql = "SELECT e.*, p.`pais`, u.`sigla` 
            FROM `empresas` e 
            INNER JOIN `ufs` u ON u.`id_uf` = e.`id_uf` 
            INNER JOIN `paises` p ON p.`id_pais` = e.`id_pais` 
            WHERE e.`id_empresa` = '$id_empresa_nota' LIMIT 1 ";
    $campos_empresas = bancos::sql($sql);
    
    //Busca dos dados do Cliente ...
    $sql = "SELECT c.*, p.`codigo_pais`, p.`pais`, u.`sigla` 
            FROM `clientes` c 
            INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
            LEFT JOIN `ufs` u ON u.`id_uf` = c.`id_uf` 
            WHERE c.`id_cliente` = '".$campos_nfs[0]['id_cliente']."' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    
    /*******************************************************************************************************/
    /*Se a Nota Fiscal for uma Devolu��o coloco essa Letra E que equivale a Entrada, sen�o 
    S que equivale a Sa�da ...*/
    if($status == 6) {//Est� sendo acessada uma NF de Devolu��o ...
        $numero_nf          = faturamentos::buscar_numero_nf($id_nf, 'D');
        $tipo_negociacao    = 'E';
        $finalidade_emissao = 4;//Devolu��o de Mercadoria ...
        
        if($campos_cliente[0]['id_uf'] == 1) {//Estado de S�o Paulo ...
            $id_cfop = 139;
        }else {//Fora do Estado de S�o Paulo ...
            $id_cfop = 147;
        }
    }else {//Est� sendo acessada uma NF Normal ...
        $numero_nf          = faturamentos::buscar_numero_nf($id_nf, 'S');
        $tipo_negociacao    = 'S';
        $finalidade_emissao = 1;//Nota Fiscal de Sa�da ...
        
        if($campos_cliente[0]['id_uf'] == 1) {//Estado de S�o Paulo ...
            $id_cfop = 3;
        }else {//Fora do Estado de S�o Paulo ...
            $id_cfop = 143;
        }
    }
    /**************************************************************************/
    /*******************Controle com a Natureza de Opera��o********************/
    /**************************************************************************/
    if($natureza_operacao == 'DEV') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'DEVOLU��O DE VENDA';
    }else if($natureza_operacao == 'PSE') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'PRESTA��O DE SERVI�OS';
    }else if($natureza_operacao == 'BON') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'REMESSA EM BONIFICA��O';
    }else if($natureza_operacao == 'VOF') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'VENDA ORIGINADA DE ENCOMENDA PARA ENTREGA FUTURA';
    }else if($natureza_operacao == 'REC') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'ENTRADA DE MERCADORIA DEVIDO A RECUSA DO CLIENTE';
    }else if($natureza_operacao == 'RAG') {//Quando for escolhida essa op��o no ERP, sempre ter� que vir esse Texto Padr�o como sendo Natureza de Opera��o ...
        $cfop_descritivo = 'REMESSA DE AMOSTRA GR�TIS';
    }else {//Do contr�rio, busco o texto de Natureza de Opera��o atrav�s do $id_cfop que foi selecionado em Nota Fiscal ...
        $sql = "SELECT `natureza_operacao_resumida` 
                FROM `cfops` 
                WHERE `id_cfop` = '$id_cfop' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_cfop        = bancos::sql($sql);
        $cfop_descritivo    = $campos_cfop[0]['natureza_operacao_resumida'];
    }
    /**************************************************************************/
}else if($id_nf_outra > 0) {
    //Busca dos dados da NF Outra com o id_nf_outra que foi passado por par�metro ...
    $sql = "SELECT `id_nf_outra`, `id_cliente`, `id_empresa`, `id_transportadora`, `id_nf_num_nota`, `id_cfop`, `id_nf_comp`, 
            `finalidade`, `tipo_nfe_nfs`, `valor_frete`, `data_emissao`, REPLACE(`chave_acesso`, ' ', '') AS chave_acesso, 
            `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4`, `data_saida_entrada`, `qtde_volume`, 
            `especie_volume`, `peso_bruto_volume`, `peso_liquido_volume`, `texto_nf`, `status`, `gerar_duplicatas` 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    $campos_nfso            = bancos::sql($sql);
    $id_empresa_nota        = $campos_nfso[0]['id_empresa'];//Essa vari�vel � renomeada porque temos uma $id_empresa na Sess�o ...
    $id_nf_num_nota         = $campos_nfso[0]['id_nf_num_nota'];
    $id_cfop                = $campos_nfso[0]['id_cfop'];
    $id_nf_comp             = $campos_nfso[0]['id_nf_comp'];
    $finalidade             = $campos_nfso[0]['finalidade'];
    $data_emissao           = $campos_nfso[0]['data_emissao'];
    //Aqui verifica o Tipo de Nota
    $nota_sgd               = ($id_empresa_nota == 1 || $id_empresa_nota == 2) ? 'N' : 'S';//var surti efeito l� embaixo

    $numero_nf              = faturamentos::buscar_numero_nf($id_nf_outra, 'O');
    
    if($id_nf_comp > 0) {//Sempre que a Nota Fiscal for Complementar ...
        $tipo_nf = 1;//Sa�da = 1 ...
    }else {
        $tipo_nf = ($campos_nfso[0]['tipo_nfe_nfs'] == 'E') ? 0 : 1;//Entrada = 0 - Importa��o, Sa�da = 1 ...
    }

    $data_saida 	= ($campos_nfso[0]['data_saida_entrada'] != '0000-00-00') ? $campos_nfso[0]['data_saida_entrada'].'T'.date('H:i').':00-03:00' : '';
    $gerar_duplicatas 	= $campos_nfso[0]['gerar_duplicatas'];
    
    //Busca dos dados da Empresa ...
    $sql = "SELECT e.*, p.`pais`, u.`sigla` 
            FROM `empresas` e 
            INNER JOIN `ufs` u ON u.`id_uf` = e.`id_uf` 
            INNER JOIN `paises` p ON p.`id_pais` = e.`id_pais` 
            WHERE e.`id_empresa` = '$id_empresa_nota' LIMIT 1 ";
    $campos_empresas = bancos::sql($sql);
    
    //Busca dos dados do Cliente ...
    $sql = "SELECT c.*, p.`codigo_pais`, p.`pais`, u.`sigla` 
            FROM `clientes` c 
            INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
            LEFT JOIN `ufs` u ON u.`id_uf` = c.`id_uf` 
            WHERE c.`id_cliente` = '".$campos_nfso[0]['id_cliente']."' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    
    //Busco o texto de Natureza de Opera��o atrav�s do $id_cfop que foi selecionado em Nota Fiscal ...
    $sql = "SELECT `natureza_operacao_resumida` 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_cfop        = bancos::sql($sql);
    $cfop_descritivo    = $campos_cfop[0]['natureza_operacao_resumida'];
    
    //Se existir NF Complementar ...
    if($id_nf_comp > 0) {
        $finalidade_emissao = 2;
    }else {
        //Aqui eu verifico se na Natureza de Opera��o existe a palavra "DEV", que equivale a Devolu��o ...
        if(strpos(strtoupper($cfop_descritivo), 'DEV') !== false) {//Encontrou "DEV" na String ...
            $finalidade_emissao = 4;//Devolu��o de Mercadoria ...
        }else {
            $finalidade_emissao = 1;//Nota Fiscal de Sa�da ...
        }
    }
}

if($campos_cliente[0]['id_pais'] == 31 && $campos_cliente[0]['id_uf'] == 1) {//Brasil e Estado de S�o Paulo ...
    $destino_operacao = 1;
}else if($campos_cliente[0]['id_pais'] == 31 && $campos_cliente[0]['id_uf'] <> 1) {//Brasil e Diferente S�o Paulo ...
    $destino_operacao = 2;
}else {//Fora do Pa�s ...
    $destino_operacao = 3;
}

/**************************************************************************************/
/***************************Layout - Cabe�alho de NF***********************************/
/**************************************************************************************/
//Layout de Cabe�alho independente do Tipo de Nota Fiscal NFS / NFD ou NFO ...
$texto.= 'A|4.00'.$quebrar_linha;

//Sempre que o Cliente n�o tiver Inscri��o Estadual ou for PF, ent�o este sempre ser� Consumidor Final ...
if(empty($insc_estadual) || $insc_estadual == 0 || strlen($campos_cliente[0]['cnpj_cpf']) == 11) {
    $consumidor_final = 1;
}else {
    $consumidor_final = 0;
}

$texto.= 'B|35||'.strtr(strtoupper($cfop_descritivo), $caracteres_invalidos, $caracteres_validos).'|55|1|'.$numero_nf.'|'.$data_emissao.'T'.date('H:i').':00-03:00|'.$data_saida.'|'.$tipo_nf.'|'.$destino_operacao.'|3550308|1|1||1|'.$finalidade_emissao.'|'.$consumidor_final.'|0|3|4.01_sebrae_b020|||'.$quebrar_linha;

if($status == 6) {//Est� sendo acessada uma NF de Devolu��o ...
    if($id_nf_num_nota != 0) {//Significa que � a Nossa pr�pria NF de Entrada ...
        //Nesse vetor eu vou armazenar todas as NF(s) que est�o atrelados a esta NF de Devolu��o ...
        $vetor_nfs = array();

        //Busco os Itens dessa Nota Fiscal que foi Devolvida ...
        $sql = "SELECT `id_nf_item_devolvida` 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$id_nf' ";
        $campos_itens   = bancos::sql($sql);
        $linhas_itens   = count($campos_itens);
        for($i = 0; $i < $linhas_itens; $i++) {
            //Busco o id_nf da Nota Fiscal de Sa�da ...
            $sql = "SELECT nfs.`id_nf`, REPLACE(nfs.`chave_acesso`, ' ', '') AS chave_acesso 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                    WHERE nfsi.`id_nfs_item` = '".$campos_itens[$i]['id_nf_item_devolvida']."' LIMIT 1 ";
            $campos_id_nfs = bancos::sql($sql);
            //Insiro nesse $vetor_nfs o id_nf corrente ...
            if(!in_array($campos_id_nfs[0]['id_nf'], $vetor_nfs)) {
                array_push($vetor_nfs, $campos_id_nfs[0]['id_nf']);
                $vetor_codigo_barras[$campos_id_nfs[0]['id_nf']] = $campos_id_nfs[0]['chave_acesso'];
            }
        }
        //Aqui eu fa�o Tratamento com a Parte das NF(s) ...
        if(count($vetor_nfs) > 0) {//Se existir pelo menos 1 NF atrelada, ent�o ...
            $texto.= 'BA|'.$quebrar_linha;
            for($i = 0; $i < count($vetor_nfs); $i++) $texto.= 'BA02|'.$vetor_codigo_barras[$campos_id_nfs[0]['id_nf']].$quebrar_linha;
        }
    }
}else {//Est� sendo acessada uma NF Normal ou uma NF Outra ...
    if($id_nf_comp > 0) {//Se existir NF Complementar, aponto a Chave de Acesso da NF que est� sendo Complementada ...
        $sql = "SELECT REPLACE(`chave_acesso`, ' ', '') AS chave_acesso 
                FROM `nfs` 
                WHERE `id_nf` = '$id_nf_comp' LIMIT 1 ";
        $campos_nf_comp = bancos::sql($sql);
        $texto.= 'BA|'.$quebrar_linha;
        $texto.= 'BA02|'.$campos_nf_comp[0]['chave_acesso'].$quebrar_linha;
    }
}
/**************************************************************************************/
/**************************Layout - Dados do Emitente**********************************/
/**************************************************************************************/
$insc_municipal = ($campos_empresas[0]['nomefantasia'] == 'ALBAFER') ? '10073949' : '20969210';
$cnae           = ($campos_empresas[0]['nomefantasia'] == 'ALBAFER') ? '2840200' : '2869100';

$texto.= 'C|'.strtr($campos_empresas[0]['razaosocial'], $caracteres_invalidos, $caracteres_validos).'|'.$campos_empresas[0]['nomefantasia'].'|'.$campos_empresas[0]['ie'].'||'.$insc_municipal.'|'.$cnae.'|3|'.$quebrar_linha;
$texto.= 'C02|'.$campos_empresas[0]['cnpj'].'|'.$quebrar_linha;
//3550308 -> � o c�digo do Munic�pio de S�o Paulo que nunca ir� mudar para n�s ...
$texto.= 'C05|'.strtr(strtoupper($campos_empresas[0]['endereco']), $caracteres_invalidos, $caracteres_validos).'|'.$campos_empresas[0]['numero'].'|'.$campos_empresas[0]['complemento'].'|'.strtoupper($campos_empresas[0]['bairro']).'|3550308|'.strtr(strtoupper($campos_empresas[0]['cidade']), $caracteres_invalidos, $caracteres_validos).'|'.$campos_empresas[0]['sigla'].'|'.str_replace('-', '', $campos_empresas[0]['cep']).'|1058|'.strtoupper($campos_empresas[0]['pais']).'|'.$campos_empresas[0]['ddd_comercial'].$campos_empresas[0]['telefone_comercial'].'|'.$quebrar_linha;
/**************************************************************************************/
$id_uf_cliente              = $campos_cliente[0]['id_uf'];
$id_pais                    = $campos_cliente[0]['id_pais'];
$insc_estadual              = ($campos_cliente[0]['insc_estadual'] == 0) ? '' : $campos_cliente[0]['insc_estadual'];
$numero_cliente             = strtok($campos_cliente[0]['num_complemento'], ',');
$complemento_cliente        = substr(strchr($campos_cliente[0]['num_complemento'], ','), 1);
$tributar_ipi_rev           = $campos_cliente[0]['tributar_ipi_rev'];
$optante_simples_nacional   = $campos_cliente[0]['optante_simples_nacional'];
$email_cliente              = $campos_cliente[0]['email'];
/**************************************************************************************/
/****************Layout - Dados do Destinat�rio / Cliente******************************/
/**************************************************************************************/
$cod_suframa                = str_replace('.', '', $campos_cliente[0]['cod_suframa']);
$cod_suframa                = str_replace('-', '', $cod_suframa);

/*Quando n�o existir Inscri��o Estadual no Cadastro do Cliente, dizemos que esse Cliente � N�o Contribuinte, 
Isso acontece com o SENAI por exemplo ...*/
$tipo_contribuinte          = (empty($insc_estadual) || $insc_estadual == 0) ? 9 : 1;

$texto.= 'E|'.strtr($campos_cliente[0]['razaosocial'], $caracteres_invalidos, $caracteres_validos).'|'.$tipo_contribuinte.'|'.$insc_estadual.'|'.$cod_suframa.'||'.$email_cliente.$quebrar_linha;
if(strlen($campos_cliente[0]['cnpj_cpf']) == 14) {//CNPJ ...
    $texto.= 'E02|'.$campos_cliente[0]['cnpj_cpf'].$quebrar_linha;
}else if(strlen($campos_cliente[0]['cnpj_cpf']) == 11) {//CPF ...
    $texto.= 'E03|'.$campos_cliente[0]['cnpj_cpf'].$quebrar_linha;
}
//Se o Pa�s for Brasil, ent�o eu fa�o a Busca do Munic�pio ...
if($id_pais == 31) {
    //Busca do C�digo do Munic�pio ...
    $condicao = (strlen($campos_cliente[0]['cidade']) <= 3) ? " '".str_replace("'", '%', stripslashes($campos_cliente[0]['cidade']))."' " : " '".str_replace("'", '%', stripslashes($campos_cliente[0]['cidade']))."%' ";
    
    //Busco pelo primeiro c�digo de Munic�pio que est� cadastrado ...
    $sql = "SELECT `codigo_municipio` 
            FROM `ufs_vs_municipios` 
            WHERE `municipio` LIKE $condicao 
            AND `id_uf` = '$id_uf_cliente' ORDER BY `codigo_municipio` LIMIT 1 ";
    $campos_municipio   = bancos::sql($sql);
    $codigo_municipio   = $campos_municipio[0]['codigo_municipio'];
    $bairro             = $campos_cliente[0]['bairro'];
    $cidade             = $campos_cliente[0]['cidade'];
    $sigla              = $campos_cliente[0]['sigla'];
}else {//Caso Internacional, ent�o vira Internacional ...
    $codigo_municipio   = 9999999;
    $bairro             = (!empty($campos_cliente[0]['bairro'])) ? $campos_cliente[0]['bairro'] : ' -- ';
    $cidade             = 'Exterior';
    $sigla              = 'EX';
}
$telcom 	= str_replace(' ', '', $campos_cliente[0]['telcom']);
$telcom 	= str_replace('-', '', $telcom);
$telcom 	= str_replace('(', '', $telcom);
$telcom 	= str_replace(')', '', $telcom);
$texto.= 'E05|'.strtr($campos_cliente[0]['endereco'], $caracteres_invalidos, $caracteres_validos).'|'.strtr($numero_cliente, $caracteres_invalidos, $caracteres_validos).'|'.strtr($complemento_cliente, $caracteres_invalidos, $caracteres_validos).'|'.strtr($bairro, $caracteres_invalidos, $caracteres_validos).'|'.$codigo_municipio.'|'.strtr($cidade, $caracteres_invalidos, $caracteres_validos).'|'.$sigla.'|'.str_replace('-', '', $campos_cliente[0]['cep']).'|'.intval($campos_cliente[0]['codigo_pais']).'|'.strtr(strtoupper($campos_cliente[0]['pais']), $caracteres_invalidos, $caracteres_validos).'|'.$campos_cliente[0]['ddd_com'].$telcom.$quebrar_linha;
/**************************************************************************************/

if($id_nf > 0) {
    //Fun��o para o c�lculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor cont�m Frete + Impostos e etc ...
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
    $peso_lote_total_kg     = $calculo_total_impostos['peso_lote_total_kg'];
    
    $perc_calculo_icms_st_recolhido_compra = (1 + (genericas::variavel(93) / 100));

    /*Observa��o: Se o Cliente Paga a Guia de Substitui��o Tribut�ria "GNRE", ent�o n�o existe IVA a ser Tributado em NF ...
    Busca dos Itens da NF ...

    Explica��o para o campo -> "valor_unit_compra" 

    (`valor_unitario` / 1.40) AS valor_unit_compra, 
    Essa � uma Margem de Lucro M�nima Razo�vel 40% ...
    Ex: est� vendendo por R$ 140,00, que significa que comprou R$ 100,00 ...

    */
    $sql = "SELECT cf.`id_unidade` AS id_unidade_tributavel, cf.`classific_fiscal`, cf.`classific_fiscal`, cf.`cest`, nfsi.`id_nfs_item`, 
            nfsi.`id_classific_fiscal`, nfsi.`peso_unitario`, 
            IF(nfsi.`qtde_nfe` > '0', nfsi.`qtde_nfe`, IF(nfs.`status` = '6', nfsi.`qtde_devolvida`, nfsi.`qtde`)) AS qtde_nota, 
            IF(nfsi.`preco_nfe` > 0, nfsi.`preco_nfe`, nfsi.`valor_unitario`) AS valor_item, 
            (`valor_unitario` / $perc_calculo_icms_st_recolhido_compra) AS valor_unit_compra, 
            nfsi.`ipi` AS ipi, nfsi.`icms`, nfsi.`reducao`, nfsi.`icms_intraestadual`, nfsi.`iva`, 
            ovi.`id_produto_acabado_discriminacao`, IF('$tributar_ipi_rev' = 'S', '0', pa.`operacao`) AS operacao, 
            pa.`id_produto_acabado`, pa.`id_unidade`, pa.`referencia`, pa.`discriminacao`, pa.`origem_mercadoria`, 
            pa.`fci_albafer`, pa.`fci_tool_master`, pa.`codigo_barra`, pcc.`cod_cliente`, 
            REPLACE(pv.`num_seu_pedido`, '-', '_') num_seu_pedido, u.`sigla` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` on nfs.id_nf = nfsi.id_nf 
            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = nfsi.`id_classific_fiscal` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            LEFT JOIN `pas_cod_clientes` pcc ON pcc.`id_produto_acabado` = pa.`id_produto_acabado` AND pcc.`id_cliente` = '".$campos_nfs[0]['id_cliente']."' 
            WHERE nfsi.`id_nf` = '$id_nf' ORDER BY pvi.`id_pedido_venda`, pa.`discriminacao` ";
    $campos_itens       = bancos::sql($sql);
    $linhas_nfs_itens   = count($campos_itens);
    for($i = 0; $i < $linhas_nfs_itens; $i++) {
        /*Esse controle � de extrema import�ncia porque em casos de "Gato por Lebre", preciso pegar 
        os impostos do Gato ...

        Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
        $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];

        //Essas vari�veis ser�o utilizadas mais abaixo ...
        $dados_produto          = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $campos_nfs[0]['id_cliente'], $id_empresa_nota, $finalidade, $tipo_negociacao, $id_nf);
        $icms_cadastrado        = $dados_produto['icms_cadastrado'];
        $icms_intraestadual     = $dados_produto['icms_intraestadual'];
        $fecp                   = $dados_produto['fecp'];
        $iva_cadastrado         = $dados_produto['iva_cadastrado'];
        $situacao_tributaria    = $dados_produto['situacao_tributaria'];
        $cfop                   = $dados_produto['cfop'];
        $pis                    = $dados_produto['pis'];
        $cofins                 = $dados_produto['cofins'];
        
//Busca o Peso do Lote do Item Espec�fico em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
        $sql = "SELECT IF(nfs.`status` = '6', (nfsi.`qtde_devolvida` * pa.`peso_unitario`), (nfsi.`qtde` * pa.`peso_unitario`)) AS peso_lote_total_kg 
                FROM `nfs_itens` nfsi 
                INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                WHERE nfsi.`id_nf` = '$id_nf' 
                AND nfsi.`id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
        $campos_lote                = bancos::sql($sql);
        $peso_lote_total_kg_item    = round($campos_lote[0]['peso_lote_total_kg'], 4);

/**********************************************Vari�veis**********************************************/
        $calculo_impostos_item                  = calculos::calculo_impostos($campos_itens[$i]['id_nfs_item'], $id_nf, 'NF');
        $base_calculo_icms_item                 = $calculo_impostos_item['base_calculo_icms'];
        $valor_icms_item                        = $calculo_impostos_item['valor_icms'];
        
        if($id_uf_cliente == 1) {//Clientes que s�o do nosso Estado de S�o Paulo pagam ST normalmente se existir ...
            $base_calculo_icms_st_item          = $calculo_impostos_item['base_calculo_icms_st'];
            $valor_icms_st_item                 = $calculo_impostos_item['valor_icms_st'];
        }else {
            //Verifico se no Estado do Cliente existe algum Conv�nio ...
            $sql = "SELECT convenio 
                    FROM `ufs` 
                    WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
            $campos_convenio = bancos::sql($sql);
            /*Se n�o existe conv�nio entre Estados, ent�o zero os valores de ST p/ cada Item ...

            Observa��o: O nosso Sistema poderia ignorar o c�lculo de ST na Fun��o de C�lculo de Impostos uma vez sabendo 
            que n�o se destaca esse Tributo em NF quando n�o existe conv�nio entre o nosso Estado "S�o Paulo" e o Estado 
            do Cliente, por�m ele calcula o IVA porque no caso do Rio de Janeiro, somos n�s quem pagamos a 
            Guia DARJ p/ esse Estado e os Clientes de l� n�o sabem como fazer o C�lculo ...*/
            if($campos_convenio[0]['convenio'] == '') {
                $base_calculo_icms_st_item          = 0;
                $valor_icms_st_item                 = 0;
            }else {//Como existe Conv�nio, ent�o eu destaco os Impostos de ST normalmente no Layout ...
                $base_calculo_icms_st_item          = $calculo_impostos_item['base_calculo_icms_st'];
                $valor_icms_st_item                 = $calculo_impostos_item['valor_icms_st'];
            }
        }
        $valor_total_produtos_item              = $calculo_impostos_item['valor_total_produtos'];
        $valor_ipi_item                         = $calculo_impostos_item['valor_ipi'];
        $base_calculo_ipi_item                  = $calculo_impostos_item['base_calculo_ipi'];
        $frete_despesas_acessorias_item         = $calculo_impostos_item['frete_despesas_acessorias'];
        $ipi_frete_despesas_acessorias_item     = $calculo_impostos_item['ipi_frete_despesas_acessorias'];
        $icms_frete_despesas_acessorias_item    = $calculo_impostos_item['icms_frete_despesas_acessorias'];
        $iva_ajustado_item                      = ($calculo_impostos_item['iva_ajustado'] * 100);

        //Essas vari�veis s�o utilizada mais abaixo ...
        $valor_ipi_item_old = $valor_ipi_item;

        /*Verifico a Finalidade da NF - sempre que a NF for revenda eu zero o valor dessas vari�veis que 
        foi calculado anteriormente, porque ir� influenciar nos resultados de bases de c�lculo ...*/
        if($finalidade == 'R') {
            $valor_ipi_item = 0;////////////////////////////////////D�rcio ...
            $ipi_frete_despesas_acessorias_item = 0;
        }
        //Insere no vetor o Elemento corrente ...
        if(!in_array($campos_itens[$i]['id_classific_fiscal'], $vetor_classific_fiscal)) array_push($vetor_classific_fiscal, $campos_itens[$i]['id_classific_fiscal']);
//Alguns arredondamentos para facilitar ...
        $isento_rs          = $valor_total_produtos_item + $frete_despesas_acessorias_item - $base_calculo_icms_item;
        $valor_contabil_rs  = $valor_total_produtos_item + $frete_despesas_acessorias_item + $valor_ipi_item_old + $valor_icms_st_item;
        //Total dos Pesos ...$valor_total_produtos_item
        $peso_nf            = faturamentos::calculo_peso_nf($id_nf);
        $peso_pro_rata_item = round($peso_lote_total_kg_item / $peso_nf['peso_liq_total_nf'] * $campos_nfs[0]['valor_frete'], 2);
/*****************************************************************************************************/
/*********************************IVAS UNIFICADOS PARA FORA DO ESTADO*********************************/
/*****************************************************************************************************/
        //Essa vari�vel ser� utilizada na parte Final do Sistema em Informa��es Complementares ...
        $vetor_cfops_da_nf[] = $cfop;
        $vetor_valor_contabil[$cfop]+=      $valor_contabil_rs;
        $vetor_valor_total_ipi[$cfop]+=     $valor_ipi_item_old;
        $vetor_base_total_ipi[$cfop]+=      $base_calculo_ipi;
        $vetor_valor_total_icms[$cfop]+=    $valor_icms_item;
        $vetor_base_total_icms[$cfop]+=     $base_calculo_icms_item;
        $vetor_valor_total_icms_st[$cfop]+= $valor_icms_st_item;
        $vetor_base_total_icms_st[$cfop]+=  $base_calculo_icms_st_item;
        $vetor_isento[$cfop]+=              $isento_rs;
        
        if($fecp > 0) {
            $fecp_item              = number_format($fecp, 2, '.', '');//� a pr�pria Al�quota FECP do Estado ...
            $base_calculo_fecp_item = number_format($base_calculo_icms_st_item, 2, '.', '');
            $valor_fecp_item        = number_format(round($base_calculo_fecp_item * ($fecp_item / 100), 2), 2, '.', '');

            $base_calculo_fecp+=    $base_calculo_icms_st_item;
            $valor_fecp+=           $valor_fecp_item;
        }else {
            $base_calculo_fecp      = 0;
            $valor_fecp             = 0;
        }
        
/**************************************************************************************/
/****************Layout - Dados do Item da NF - Refer�ncia, Qtde, Pre�o****************/
/**************************************************************************************/
        if(!empty($campos_itens[$i]['cod_cliente'])) {//Se existir c�digo do Cliente - normalmente s� � utilizado para FG ...
            $texto.= 'H|'.($i + 1).'|cProdCliente:"'.$campos_itens[$i]['cod_cliente'].'"|'.$quebrar_linha;
        }else {
            if(!empty($base_calculo_fecp_item)) {
                $texto.= 'H|'.($i + 1).'|BC-FECP: '.number_format($base_calculo_fecp_item, 2, ',', '.').' - %FECP: '.number_format($fecp_item, 2, ',', '.').' - FECP: '.number_format($valor_fecp_item, 2, ',', '.').$quebrar_linha;
                //$rotulo_fecp = ' - BC-FECP: '.number_format($base_calculo_fecp_item, 2, ',', '.').' - %FECP: '.number_format($fecp_item, 2, ',', '.').' - FECP: '.number_format($valor_fecp_item, 2, ',', '.');
            }else {
                $texto.= 'H|'.($i + 1).'|'.$quebrar_linha;
            }
        }
        $frete_despesas_acessorias_item_layout = ($frete_despesas_acessorias_item > 0) ? number_format($frete_despesas_acessorias_item, 2, '.', '') : '';
/********************************************************************************************************/
/*******************************Controle referente aos PA(s) Substitutivos*******************************/ 
/********************************************************************************************************/
/*Significa que aqui foi utilizado um  P.A. substitutivo "Gato por Lebre" e sendo assim eu apresento 
detalhes desse a� p/ o Cliente ...

Exemplo: o Cliente pediu MC, mas n�s estamos mandando TM, sendo assim pego os dados do MC que foi o que o 
Cliente pediu ...***/
        if(!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) {
            $sql = "SELECT `referencia`, `discriminacao`, `fci_albafer`, `fci_tool_master`, `codigo_barra` 
                    FROM `produtos_acabados` 
                    WHERE id_produto_acabado = '".$campos_itens[$i]['id_produto_acabado_discriminacao']."' LIMIT 1 ";
            $campos_pa_substitutivo     = bancos::sql($sql);
            $referencia 		= $campos_pa_substitutivo[0]['referencia'];
            $discriminacao 		= $campos_pa_substitutivo[0]['discriminacao'];
            $codigo_barra		= $campos_pa_substitutivo[0]['codigo_barra'];
            $codigo_barra_trib          = $campos_pa_substitutivo[0]['codigo_barra'];
            $fci_albafer                = $campos_pa_substitutivo[0]['fci_albafer'];
            $fci_tool_master            = $campos_pa_substitutivo[0]['fci_tool_master'];
//Aqui foi feito a Compra de um PA Normal, n�o foi feito o Gato por Lebre ...
        }else {
            $referencia 		= $campos_itens[$i]['referencia'];
            $discriminacao 		= $campos_itens[$i]['discriminacao'];
            $codigo_barra		= $campos_itens[$i]['codigo_barra'];
            $codigo_barra_trib          = $campos_itens[$i]['codigo_barra'];
            $fci_albafer                = $campos_itens[$i]['fci_albafer'];
            $fci_tool_master            = $campos_itens[$i]['fci_tool_master'];
        }
/********************************************************************************************************/
        //Aqui eu verifico pela CFOP da NF se essa foi feita como sendo 'PRESTA��O DE SERVI�O' ...
        $prestacao_servicos = (strtr(strtoupper($cfop_descritivo), $caracteres_invalidos, $caracteres_validos) == 'PRESTACAO DE SERVICO') ? 0 : 1;
        
        if($id_pais != 31 || $campos_nfs[0]['trading'] == 1) {//Clientes Estrangeiros "Exporta��o" ...
            if($campos_itens[$i]['id_unidade'] == $campos_itens[$i]['id_unidade_tributavel']) {//Unidades Iguais ...
                $unidade                    = $campos_itens[$i]['sigla'];
                $unidade_tributavel         = $campos_itens[$i]['sigla'];
                
                $qtde_nota                      = $campos_itens[$i]['qtde_nota'];
                $valor_item                     = $campos_itens[$i]['valor_item'];
            
                $qtde_nota_un_tributaria        = $campos_itens[$i]['qtde_nota'];
                $valor_item_un_tributaria       = $campos_itens[$i]['valor_item'];
            }else {//Unidades Diferentes ...
                //Busco a Sigla da Unidade ...
                $sql = "SELECT `sigla` 
                        FROM `unidades` 
                        WHERE `id_unidade` = '".$campos_itens[$i]['id_unidade_tributavel']."' LIMIT 1 ";
                $campos_unidade_tributavel      = bancos::sql($sql);
                $unidade_tributavel             = $campos_unidade_tributavel[0]['sigla'];

                if($campos_itens[$i]['id_unidade_tributavel'] == 1) {//Se for Kilo ...
                    //Fa�o arredondamento p/ 2 casas porque assim � mais f�cil de fazer um arredondamento mais preciso ...
                    $qtde_nota_un_tributaria    = round($campos_itens[$i]['peso_unitario'] * 10 * $campos_itens[$i]['qtde_nota'] / 10, 2);
                    $valor_item_un_tributaria   = round($valor_total_produtos_item / $qtde_nota_un_tributaria, 10);
                    /**************************************************Explica��o Importante**************************************************/
                    /*Quando for Exporta��o, a Unidade Comercial tem que ser igual a Unidade Tribut�vel porque n�s "Albafer" estamos exportando 
                    diretamente para o Cliente ...

                    No caso de Trading, isso n�o pode ser feito porque fazemos uma Exporta��o Indireta, ou seja, temos a� um despachante que far� 
                    todo o tr�mite comercial p/ exportar pra gente ...*/
                    /*************************************************************************************************************************/
                    if($id_pais != 31) {//Somente p/ Exporta��o, trading nunca ...
                        $unidade                    = $unidade_tributavel;
                        $qtde_nota                  = $qtde_nota_un_tributaria;
                        $valor_item                 = $valor_item_un_tributaria;
                        //Nesse caso em espec�fico essa vari�vel deixa de herdar o valor que j� vinha carregado da fun��o de impostos ...
                        $valor_total_produtos_item  = $qtde_nota_un_tributaria * $valor_item_un_tributaria;
                    }else {
                        $unidade                    = $campos_itens[$i]['sigla'];
                        $qtde_nota                  = $campos_itens[$i]['qtde_nota'];
                        $valor_item                 = $campos_itens[$i]['valor_item'];
                    }
                }else {
                    echo 'CHAMAR PROGRAMADOR OU ROBERTO';
                    exit;
                }
            }
        }else {//Clientes do Brasil ...
            //Tratamento de Unidade somente p/ o cliente "Ferramentas Gerais" ...
            $vetor_ferramentas_gerais = array(647, 649, 687, 801, 802, 854, 3573, 3820, 3971);

            if(in_array($campos_nfs[0]['id_cliente'], $vetor_ferramentas_gerais)) {
                if($campos_itens[$i]['sigla'] == 'UN') {//Se a Unidade for UN, n�o coloco UN direto, existe a regra abaixo ...
                    $vetor_referencia       = split('-', $referencia);
                    $bitola_pa              = $vetor_referencia[1];//Apenas a parte num�rica ...
                    $unidade                = (strpos($bitola_pa, 'S') > 0) ? 'CT'/*Cartela*/ : 'PC';/*Pe�a*/
                    $unidade_tributavel     = $unidade;
                }else {
                    $unidade                = $campos_itens[$i]['sigla'];
                    $unidade_tributavel     = $campos_itens[$i]['sigla'];
                }
            }else {//Outro Cliente ...
                $unidade                    = $campos_itens[$i]['sigla'];
                $unidade_tributavel         = $campos_itens[$i]['sigla'];
            }

            $qtde_nota                      = $campos_itens[$i]['qtde_nota'];
            $valor_item                     = $campos_itens[$i]['valor_item'];

            $qtde_nota_un_tributaria        = $campos_itens[$i]['qtde_nota'];
            $valor_item_un_tributaria       = $campos_itens[$i]['valor_item'];
        }
        $desconto_item  = (abs($calculo_impostos_item['desconto']) > 0) ? number_format(abs($calculo_impostos_item['desconto']), 2, '.', '') : '';
        
        //N�mero de Controle de FCI ...
        if($id_empresa_nota == 1) {
            $numero_controle_fci        = $fci_albafer;
            $rotulo_numero_controle_fci = '-FCI:'.$numero_controle_fci;
        }else if($id_empresa_nota == 2) {
            $numero_controle_fci = $fci_tool_master;
            $rotulo_numero_controle_fci = '-FCI:'.$numero_controle_fci;
        }
        
        /*Observa��o: o pen�ltimo campo dessa linha I -> "xPed" s� comporta 15 caracteres no m�ximo, por isso 
        que eu utilizo o comando -> "substr($campos_itens[$i]['num_seu_pedido'], 0, 15)" ...*/
        if(empty($codigo_barra))        $codigo_barra       = 'SEM GTIN';
        if(empty($codigo_barra_trib))   $codigo_barra_trib  = 'SEM GTIN';
        
        $texto.= 'I|'.$referencia.'|'.$codigo_barra.'|'.strtr(strtoupper($discriminacao), $caracteres_invalidos, $caracteres_validos).$rotulo_numero_controle_fci.'-N.PED:"'.$campos_itens[$i]['num_seu_pedido'].'"'.$rotulo_fecp.'|'.str_replace('.', '', $campos_itens[$i]['classific_fiscal']).'||'.str_replace('.', '', $cfop).'|'.$unidade.'|'.number_format($qtde_nota, 4, '.', '').'|'.number_format($valor_item, 8, '.', '').'|'.number_format($valor_total_produtos_item, 2, '.', '').'|'.$codigo_barra_trib.'|'.$unidade_tributavel.'|'.number_format($qtde_nota_un_tributaria, 4, '.', '').'|'.number_format($valor_item_un_tributaria, 8, '.', '').'|'.$frete_despesas_acessorias_item_layout.'||'.$desconto_item.'||'.$prestacao_servicos.'|'.substr($campos_itens[$i]['num_seu_pedido'], 0, 15).'|'.($i + 1).'|'.$numero_controle_fci.'|'.$quebrar_linha;
        
        //CEST ...
        if(!empty($campos_itens[$i]['cest'])) $texto.= 'I05c|'.str_replace('.', '', $campos_itens[$i]['cest']).'|'.$quebrar_linha;
        
        $texto.= 'M|'.$quebrar_linha;

        if($prestacao_servicos == 0) {//Significa que essa NF � de Presta��o de Servi�o ...
/**************************************************************************************/
/*******************************Layout - Dados de ISSQN *******************************/
/**************************************************************************************/		
            $base_calculo_icms_item = round($valor_total_produtos_item, 2);//A BC � o pr�prio Valor Parcial do Produto ...
            $valor_issqn            = round($valor_total_produtos_item * (5 / 100), 2);
            $texto.= 'U|'.number_format($base_calculo_icms_item, 2, '.', '').'|5.00|'.$valor_issqn.'|3550308|1401|N|'.$quebrar_linha;
        }else {
/**************************************************************************************/
/******************Layout - Dados de ICMS do Item, Al�quota, Valor ********************/
/**************************************************************************************/
            /****************************************************************************************************/
            //Fa�o esse tratamento espec�fico p/ n�o dar erro nessa parte do Layout que come�a com a Letra N\ ...
            $iva_layout = ($campos_itens[$i]['iva'] > 0) ? number_format($iva_ajustado_item, 2, '.', '') : '';
            /****************************************************************************************************/
            $texto.= 'N|'.$quebrar_linha;
            
            if($situacao_tributaria == '00') {//Tributada Integralmente ...
                $texto.= 'N02|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
            }else if($situacao_tributaria == '10') {//Tributada com cobran�a de ICMS por Substitui��o Tribut�ria ...
                $texto.= 'N03|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4|'.$iva_layout.'||'.number_format($base_calculo_icms_st_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms_intraestadual'], 2, '.', '').'|'.number_format($valor_icms_st_item, 2, '.', '').'|'.$base_calculo_fecp_item.'|'.$fecp_item.'|'.$valor_fecp_item.$quebrar_linha;
            }else if($situacao_tributaria == '20') {//Com redu��o de Base de C�lculo ...
                $texto.= 'N04|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($campos_itens[$i]['reducao'], 2, '.', '').'|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
                //40 - Isenta ou 41 - N�o tributada ou 50 - Suspens�o ...
            }else if($situacao_tributaria == '40' || $situacao_tributaria == '41' || $situacao_tributaria == '50') {
                $texto.= 'N06|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.$quebrar_linha;
            }else if($situacao_tributaria == '51') {//51 - Diferimento ...	
                $texto.= 'N07|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|0.00|0.00|0.00|0.00'.$quebrar_linha;
            }else if($situacao_tributaria == '60') {//Cobrado anteriormente por substitui��o tribut�ria ...
                $valor_total_produtos_compra_item   = ($qtde_nota * $campos_itens[$i]['valor_unit_compra']);
                $valor_icms_compra_item             = ($icms_cadastrado / 100) * $valor_total_produtos_compra_item;
                           
                //C�lculo do Valor de ICMS ST - por ser de compra, por isso que passo 2 vezes a mesma vari�vel $icms_cadastrado ...
                $vetor_dados_substituicao_tributaria = calculos::calculos_substituicao_tributaria($ipi, $icms_cadastrado, $icms_cadastrado, $iva_cadastrado, $valor_total_produtos_compra_item, $valor_icms_compra_item);

                //Acumula o Total de Todas as vari�veis referentes ao ST ...
                $aliq_suport_cons_final             = ($icms_cadastrado + $fecp);
                $base_calculo_icms_st_ret_ant_item  = $vetor_dados_substituicao_tributaria['base_calculo_icms_st_item_current_rs'];
                $valor_icms_st_ret_ant_item         = $vetor_dados_substituicao_tributaria['valor_icms_st_item_current_rs'];
                
                $texto.= 'N08|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|'.number_format($base_calculo_icms_st_ret_ant_item, 2, '.', '').'|'.number_format($aliq_suport_cons_final, 2, '.', '').'|'.number_format($valor_icms_st_ret_ant_item, 2, '.', '').$quebrar_linha;
            }else if($situacao_tributaria == '90') {//Outras ...
                $texto.= 'N10|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($campos_itens[$i]['reducao'], 2, '.', '').'|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4|'.$iva_layout.'||'.number_format($base_calculo_icms_st_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms_intraestadual'], 2, '.', '').'|'.number_format($valor_icms_st_item, 2, '.', '').'|'.number_format($base_calculo_fecp_item, 2, '.', '').'|'.number_format($fecp_item, 2, '.', '').'|'.$valor_fecp_item.$quebrar_linha;
            }
/**************************************************************************************/
/*******************Layout - Dados de IPI do Item, Al�quota, Valor ********************/
/**************************************************************************************/
            if($campos_itens[$i]['ipi'] > 0) {
                $texto.= 'O||||999'.$quebrar_linha;
                $texto.= 'O07|50|'.number_format($valor_ipi_item_old, 2, '.', '').$quebrar_linha;
                $texto.= 'O10|'.number_format($valor_total_produtos_item + $frete_despesas_acessorias_item, 2, '.', '').'|'.number_format($campos_itens[$i]['ipi'], 2, '.', '').$quebrar_linha;
            }
        }
/**************************************************************************************/
/********Layout - Dados de PIS do Item que existe mesmo independente de Suframa********/
/**************************************************************************************/
        $texto.= 'Q|'.$quebrar_linha;
        /*Se o Suframa do Cliente for Zona Franca de Manaus ou a NF tem como venda o fim expec�fico 
        de "Exporta��o" ou a Nota Fiscal for de Bonifica��o, o Cliente � Suspenso de PIS ...*/
        $vetor_cfops_exportacao = array('5.501', '5.502', '6.501', '6.502');
        if($campos_nfs[0]['suframa'] == 2 || in_array($cfop, $vetor_cfops_exportacao) || $natureza_operacao == 'BON') {
            $calculo_pis_pro_rata_item      = 0;
            
            $texto.= 'Q04|08'.$quebrar_linha;
        }else {
            //Aqui eu retiro o Valor de ICMS do Item porque o Valor Total dos Produtos j� cont�m esse Embutido, sen�o ter�amos uma Bi-Tributa��o ...
            $base_calculo_pis_pro_rata_item = round(($valor_total_produtos_item + $frete_despesas_acessorias_item - $valor_icms_item), 2);
            $calculo_pis_pro_rata_item      = round($base_calculo_pis_pro_rata_item * $pis / 100, 2);
            
            $texto.= 'Q02|01|'.number_format($base_calculo_pis_pro_rata_item, 2, '.', '').'|'.number_format($pis, 2, '.', '').'|'.number_format($calculo_pis_pro_rata_item, 2, '.', '').$quebrar_linha;
        }
/**************************************************************************************/
/*******Layout - Dados de Cofins do Item que existe mesmo independente de Suframa******/
/**************************************************************************************/
        $texto.= 'S|'.$quebrar_linha;
        /*Se o Suframa do Cliente for Zona Franca de Manaus ou a NF tem como venda o fim expec�fico 
        de "Exporta��o" ou a Nota Fiscal for de Bonifica��o, o Cliente � Suspenso de COFINS ...*/
        if($campos_nfs[0]['suframa'] == 2 || in_array($cfop, $vetor_cfops_exportacao) || $natureza_operacao == 'BON') {
            $calculo_cofins_pro_rata_item       = 0;
            
            $texto.= 'S04|08'.$quebrar_linha;
        }else {
            //Aqui eu retiro o Valor de ICMS do Item porque o Valor Total dos Produtos j� cont�m esse Embutido, sen�o ter�amos uma Bi-Tributa��o ...
            $base_calculo_cofins_pro_rata_item  = round(($valor_total_produtos_item + $frete_despesas_acessorias_item - $valor_icms_item), 2);
            $calculo_cofins_pro_rata_item       = round($base_calculo_cofins_pro_rata_item * $cofins / 100, 2);
            
            $texto.= 'S02|01|'.number_format($base_calculo_cofins_pro_rata_item, 2, '.', '').'|'.number_format($cofins, 2, '.', '').'|'.number_format($calculo_cofins_pro_rata_item, 2, '.', '').$quebrar_linha;
        }
/**************************************************************************************/
/****************************************Difal*****************************************/
/**************************************************************************************/
        if($calculo_impostos_item['difal'] > 0) $texto.= 'NA|'.number_format($base_calculo_icms_item, 2, '.', '').'|||'.$campos_itens[$i]['icms_intraestadual'].'|'.$campos_itens[$i]['icms'].'|100||'.number_format($calculo_impostos_item['valor_icms_destino'], 2, '.', '').'|'.number_format($calculo_impostos_item['valor_icms_remetente'], 2, '.', '').'|'.$quebrar_linha;
    }
    /**************************************************************************************/
    /***********************Layout - Dados do Total de Impostos da NF**********************/
    /**************************************************************************************/
    $desconto   = (abs($calculo_total_impostos['desconto']) > 0) ? number_format(abs($calculo_total_impostos['desconto']), 2, '.', '') : '';
    
    $texto.= 'W|'.$quebrar_linha;
    $texto.= 'W02|'.number_format($calculo_total_impostos['base_calculo_icms'], 2, '.', '').'|'.number_format($calculo_total_impostos['valor_icms'], 2, '.', '').'|0.00|0.00|'.number_format($calculo_total_impostos['base_calculo_icms_st'], 2, '.', '').'|'.number_format($calculo_total_impostos['valor_icms_st'], 2, '.', '').'|'.number_format($valor_fecp, 2, '.', '').'|0.00|'.number_format($calculo_total_impostos['valor_total_produtos'], 2, '.', '').'|'.number_format($calculo_total_impostos['valor_frete'], 2, '.', '').'|0.00|'.$desconto.'|0.00|'.number_format(round($calculo_total_impostos['valor_ipi'], 2), 2, '.', '').'|0.00|'.number_format($calculo_pis, 2, '.', '').'|'.number_format($calculo_cofins, 2, '.', '').'|'.number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, '.', '').'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
    /**************************************************************************************/
    /*******************************Layout - Dados de ISSQN *******************************/
    /**************************************************************************************/
    //Significa que essa NF � de Presta��o de Servi�o ...
    if($prestacao_servicos == 0) $texto.= 'W17|'.number_format($valor_total_produtos_item, 2, '.', '').'|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.$valor_issqn.'|'.number_format($calculo_pis_pro_rata_item, 2, '.', '').'|'.number_format($calculo_cofins_pro_rata_item, 2, '.', '').'|'.$quebrar_linha;
    
    //Busca dos dados da Transportadora ...
    $sql = "SELECT * 
            FROM `transportadoras` t 
            WHERE `id_transportadora` = '".$campos_nfs[0]['id_transportadora']."' LIMIT 1 ";
    $campos_transportadoras = bancos::sql($sql);
    $ie_transportadora      = (strlen($campos_transportadoras[0]['ie']) == 12) ? $campos_transportadoras[0]['ie'] : '';
    $cnpj_transportadora    = ($campos_transportadoras[0]['cnpj'] != '00000000000000') ? $campos_transportadoras[0]['cnpj'] : '';
    
    if($frete_transporte == 'C') {//CIF ...
        $frete_por_conta = 0;//Remetente ...
    }else if($frete_transporte == 'F') {//FOB ...
        $frete_por_conta = 1;//Destinat�rio ...
    }
    /**************************************************************************************/
    /***************************Layout - Dados da Transportadora***************************/
    /**************************************************************************************/
    $texto.= 'X|'.$frete_por_conta.$quebrar_linha;
    $texto.= 'X03|'.strtr(strtoupper($campos_transportadoras[0]['nome']), $caracteres_invalidos, $caracteres_validos).'|'.$ie_transportadora.'|'.strtr(strtoupper($campos_transportadoras[0]['endereco']), $caracteres_invalidos, $caracteres_validos).', '.strtr(strtoupper($campos_transportadoras[0]['num_complemento']), $caracteres_invalidos, $caracteres_validos).'|'.strtr(strtoupper($campos_transportadoras[0]['cidade']), $caracteres_invalidos, $caracteres_validos).'|'.strtoupper($campos_transportadoras[0]['uf']).$quebrar_linha;
    $texto.= 'X04|'.$cnpj_transportadora.$quebrar_linha;
    /************************************************************************************/
    if($id_nf_vide_nota == 0) {//Se n�o existe Vide Nota ent�o apresenta a linha de forma normal ...
        if(!empty($txt_quantidade)) {//O �nico caso em que o usu�rio ter� que preencher esses campos � p/ as NFes de Exporta��o 7.101 ...
            $texto.= 'X26|'.$txt_quantidade.'|'.$txt_especie.'|||'.$txt_peso_liquido.'|'.$txt_peso_bruto.$quebrar_linha;
        }else {
            $texto.= 'X26|'.$peso_nf['qtde_caixas'].'|'.$peso_nf['especie'].'|||'.number_format($peso_nf['peso_liq_total_nf'], 3, '.', '').'|'.number_format($peso_bruto_balanca, 3, '.', '').$quebrar_linha;
        }
    }else {//Se existe Vide Nota, ent�o apresenta da seguite maneira ...
        $texto.= 'X26|0|VIDE NOTA = '.faturamentos::buscar_numero_nf($id_nf_vide_nota, 'S').'|||0.000|0.000'.$quebrar_linha;
    }
    /************************************************************************************/
    //Busca dos dados de Vencimento da NF para poder gerar as Duplicatas ...
    $data_emissao	= data::datetodata($campos_nfs[0]['data_emissao'], '/');
    $data_bl		= data::datetodata($campos_nfs[0]['data_bl'], '/');

    //S� existe este campo, p/ Clientes Internacionais, caso esteja preenchido, os vencs. ser�o feitos em cima deste ...
    if($campos_nfs[0]['data_bl'] != '0000-00-00') {
        $data_vencimento1 = data::adicionar_data_hora($data_bl, $campos_nfs[0]['vencimento1']);
    }else {
        if($campos_nfs[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $campos_nfs[0]['vencimento1']);
    }
    $qtde_vias_duplic = 1;//Pelo menos 1 via ir� existir na Duplicata ...

    if($campos_nfs[0]['vencimento2'] > 0) {
        //S� existe este campo, p/ Clientes Internacionais, caso esteja preenchido, os vencs. ser�o feitos em cima deste ...
        if($campos_nfs[0]['data_bl'] != '0000-00-00') {
            $data_vencimento2 = data::adicionar_data_hora($data_bl, $campos_nfs[0]['vencimento2']);
        }else {
            if($campos_nfs[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $campos_nfs[0]['vencimento2']);
        }
        $qtde_vias_duplic++;
    }

    if($campos_nfs[0]['vencimento3'] > 0) {
        //S� existe este campo, p/ Clientes Internacionais, caso esteja preenchido, os vencs. ser�o feitos em cima deste ...
        if($campos_nfs[0]['data_bl'] != '0000-00-00') {
            $data_vencimento3 = data::adicionar_data_hora($data_bl, $campos_nfs[0]['vencimento3']);
        }else {
            if($campos_nfs[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $campos_nfs[0]['vencimento3']);
        }
        $qtde_vias_duplic++;
    }

    if($campos_nfs[0]['vencimento4'] > 0) {
        //S� existe este campo, p/ Clientes Internacionais, caso esteja preenchido, os vencs. ser�o feitos em cima deste ...
        if($campos_nfs[0]['data_bl'] != '0000-00-00') {
            $data_vencimento4 = data::adicionar_data_hora($data_bl, $campos_nfs[0]['vencimento4']);
        }else {
            if($campos_nfs[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $campos_nfs[0]['vencimento4']);
        }
        $qtde_vias_duplic++;
    }
    $valor_duplicata	= faturamentos::valor_duplicata($id_nf, $campos_nfs[0]['suframa'], $nota_sgd, $id_pais);
    if($id_pais != 31) {
        $valor_itens_rs 	= $calculo_total_impostos['valor_total_produtos'];
        /*Fun��o que ser� utilizada somente quando o Cliente for estrangeiro, pois nesse caso, 
        n�s gravamos o valor da Duplicata em U$ na Base de Dados, da� passo o Valor da NF em
        reais e l� dentro da Fun��o ele divide pelo n�mero de Vencimentos ...*/
        $valor_duplicata    = faturamentos::valor_duplicata_rs($valor_itens_rs, $qtde_vias_duplic);
    }
    if($qtde_vias_duplic == 1) {//Uma �nica Via ...
        $duplicata_array = array($numero_nf);
    }else {//Mais de uma via ...
        $duplicata_array = array($numero_nf.'A', $numero_nf.'B', $numero_nf.'C', $numero_nf.'D');	
    }
    $data_vencimento_array = array($data_vencimento1, $data_vencimento2, $data_vencimento3, $data_vencimento4);
    /**************************************************************************************/
    /*******************Layout - Dados das Duplicatas da NF********************************/
    /**************************************************************************************/
    //Esse Layout gera um texto autom�tico na parte de Informa��es Complementares ...
    $texto.= 'Y'.$quebrar_linha;
    
    if($desconto == 0) {
        $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_nota']), 2, '.', '').'|0.00|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
    }else {
        if($calculo_total_impostos['valor_total_produtos'] == 0) {//Dependendo da NF Complementar podemos ter o "valor_total_produtos" Nulo ...
            $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_nota']), 2, '.', '').'|'.$desconto.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
        }else {//Foi feito qualquer outro Tipo de NF ...
            $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_produtos'] + $calculo_total_impostos['valor_frete']), 2, '.', '').'|'.$desconto.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
        }
    }
    
    for($i = 0; $i < $qtde_vias_duplic; $i++) {
        $texto.= 'Y07|00'.($i + 1).'|'.data::datatodate($data_vencimento_array[$i], '-').'|'.number_format($valor_duplicata[$i], 2, '.', '').$quebrar_linha;
        //Essa informa��o tamb�m � apresentanda nas informa��es complementares ...
        if(($i + 1) == $qtde_vias_duplic) {//Se for a �ltima via ent�o coloca-se o Enter no final ...
            $faturas_inf_compl.= $duplicata_array[$i].' - '.$data_vencimento_array[$i].' - R$ '.number_format($valor_duplicata[$i], 2, ',', '.').'  ****'.$quebrar_linha;
        }else {//Enquanto n�o for a �ltima via, coloca-se ; no final da Duplicata ...
            $faturas_inf_compl.= $duplicata_array[$i].' - '.$data_vencimento_array[$i].' - R$ '.number_format($valor_duplicata[$i], 2, ',', '.').'____';
        }
    }
    /**************************************************************************************/
    /***********************Layout - Forma de Pagamento************************************/
    /**************************************************************************************/
    //Esse Layout gera um texto autom�tico na parte de Informa��es Complementares ...
    $texto.= 'YA'.$quebrar_linha;
    
    $forma_pagamento    = ($campos_nfs[0]['vencimento1'] == 0) ? 0 : 1;
    //Notas Fiscais de Ajuste ou Devolu��o, n�o tem o que receber do Cliente ...
    $meio_de_pagamento  = ($finalidade_emissao == 3 || $finalidade_emissao == 4) ? 90 : 99;
    
    $texto.= 'YA01|'.$forma_pagamento.'|'.$meio_de_pagamento.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
    /******************************Dados Adicionais de Nota Fiscal********************************/
    $dados_adicionais = '(CLIENTE: '.$campos_cliente[0]['cod_cliente'].') ';
    //Dados Adicionais ...
    $linhas_classific_fiscal    = count($vetor_classific_fiscal);
    $vetor_iva_show             = array();
    $ultimo_iva_armazenado      = '';

    for($i = 0; $i < count($vetor_iva); $i++) {
        //O Array Unique aqui nessa Parte n�o funciona de Jeito nenhum, tive que retirar os valores duplicados na m�o ...
        if($vetor_iva[$i] != $ultimo_iva_armazenado) {
            array_push($vetor_iva_show, $vetor_iva[$i]);
            $ultimo_iva_armazenado = $vetor_iva[$i];
        }
    }
//Classifica��es Fiscais ...
    for($i = 0; $i < $linhas_classific_fiscal; $i++) {
        if($id_pais == 31) {//Se a NF for do Brasil ...
            $sql = "SELECT cf.`classific_fiscal`, i.`reducao` 
                    FROM `classific_fiscais` cf 
                    INNER JOIN `icms` i ON i.`id_classific_fiscal` = cf.`id_classific_fiscal` AND i.`id_uf` = '$id_uf_cliente' 
                    WHERE cf.`id_classific_fiscal` = '".$vetor_classific_fiscal[$i]."' LIMIT 1 ";
        }else {//Se for de Outros Pa�ses - por exemplo Internacional ...
            $sql = "SELECT cf.`classific_fiscal` 
                    FROM `classific_fiscais` cf 
                    WHERE cf.`id_classific_fiscal` = '".$vetor_classific_fiscal[$i]."' LIMIT 1 ";
        }
        $campos_classific_fiscal    = bancos::sql($sql);
        if($vetor_classific_fiscal[$i] == 1 || $vetor_classific_fiscal[$i] == 2) $mensagem_classific_fiscal = 1;

        $valor_iva_classific_fiscal = '';
/*Se existir valor de IVA (ST), ent�o apresento o valor ao lado das classifica��es Fiscais na NF ...*/
        if($vetor_iva_show[$i] > 0) {
            if($id_uf_cliente == 3) {//Se for Minas Gerais, ent�o o r�tulo a ser exibido ser� MVA ...
                $rotulo_st = 'MVA';
            }else {//Outro Estado ...
                $rotulo_st = 'IVA';
            }
            $valor_iva_classific_fiscal = ' ('.$rotulo_st.' = '.number_format($vetor_iva_show[$i], 2, ',', '.').')';
        }
        $letra_r = '';//Default � Vazio ...
        //Se existir Redu��o de ICMS na Base de C�lculo, ent�o eu apresento a letra R, ao lado do N.� da Classific. Fiscal
        if($campos_classific_fiscal[0]['reducao'] > 0) $letra_r = '/R';
        $classificacoes_fiscais.= $vetor_classific_fiscal[$i].$letra_r.'-'.$campos_classific_fiscal[0]['classific_fiscal'].$valor_iva_classific_fiscal.'; ';
    }
    $classificacoes_fiscais = substr($classificacoes_fiscais, 0, strlen($classificacoes_fiscais) - 2);
    $dados_adicionais.= 'CF: '.$classificacoes_fiscais.$quebrar_linha;

    if($id_pais == 31) {//S� existem esses textos de Isento e de Bases de C�lculo quando for daqui do Brasil ...
//Isento - Bases de C�lculo ICMS Bits Bedames Riscador ...
        $dados_adicionais.= 'ISENTO=R$ '.number_format($calculo_total_impostos['isento'], 2, ',', '.').'; BC ICMS BITS BED RISC=R$ '.number_format($calculo_total_impostos['base_calculo_icms_bits_bedames_riscador'], 2, ',', '.').$quebrar_linha;
//Bases de C�lculo ICMS Bits C/ Redu��o
        $dados_adicionais.= 'BC ICMS C RED=R$ '.number_format($calculo_total_impostos['base_calculo_icms_c_red'], 2, ',', '.').'; BC ICMS S RED=R$ '.number_format($calculo_total_impostos['base_calculo_icms_s_red'], 2, ',', '.').$quebrar_linha;
    }else {//Se for Exporta��o, todos as Bases de C�lculo = 0 e o Isento fica sendo o Valor Total da NF ...
//Isento - Bases de C�lculo ICMS Bits Bedames Riscador ...
        $dados_adicionais.= 'Isento=R$ '.number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.').$quebrar_linha;
    }
/******************************************************************/
/*****************************Suframa******************************/
/******************************************************************/
//Mensagem que sempre ser� mostrado no fim, depois de todos os Itens ...
    if($campos_nfs[0]['suframa'] > 0 && $campos_nfs[0]['suframa_ativo'] == 'S') {//Se o Cliente possui Suframa e este est� Ativo ...
/*Sempre que existir suframa, ent�o ter� que printar esse texto na Tela de Itens, pois nele 
existe um valor que tamb�m acarretar� no Valor Total da NF ...*/
        if($campos_nfs[0]['suframa'] == 1) {//�rea de Livre Com�rcio ...
            $dados_adicionais.= 'Desconto de ICMS = R$ '.number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.').$quebrar_linha;
        }else if($campos_nfs[0]['suframa'] == 2) {//Zona Franca de Manaus ...
            $dados_adicionais.= 'Desconto de PIS + Cofins = '.number_format((genericas::variavel(20) + genericas::variavel(21)), 2, ',', '.').' % e ICMS = R$ '.number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.').$quebrar_linha;
        }
    }
/**************************************************************************************/
/*******************************Layout - Dados de ISSQN *******************************/
/**************************************************************************************/
    if($prestacao_servicos == 0) {//Significa que essa NF � de Presta��o de Servi�o ...
        $dados_adicionais.= 'Esta NFE est� sendo emitida em car�ter provis�rio considerando-se o seu n� de emiss�o, para todos os efeitos, como um RPS (Recibo provis�rio de Servi�os Prestados) o qual ser� substitu�do de forma definitiva pela emiss�o da Nota Fiscal Eletr�nica de Servi�os da Prefeitura do Munic�pio de S�o Paulo.'.$quebrar_linha;
    }
    $linhas_dados_adicionais = explode($quebrar_linha, trim($dados_adicionais));
    $dados_adicionais = '';//Zero essa vari�vel porque aqui ela vai ser tratada ...
    for($l = 0; $l < count($linhas_dados_adicionais); $l++) {
        $dados_adicionais.= trim($linhas_dados_adicionais[$l]);
        if(($l + 1) < count($linhas_dados_adicionais)) $dados_adicionais.= '; ';
    }
    /******************************Informa��es Complementares de Nota Fiscal********************************/
    if($mensagem_classific_fiscal == 1) {
        $sql = "SELECT icms.`reducao`, cf.`reducao_governo` 
                FROM `icms` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = icms.`id_classific_fiscal` 
                WHERE icms.`id_uf` = '$id_uf_cliente' 
                AND icms.`id_classific_fiscal` = '1' 
                AND icms.`ativo` = '1' LIMIT 1 ";
        $campos_texto               = bancos::sql($sql);
        $informacoes_complementares = str_replace('?', number_format($campos_texto[0]['reducao'], 2, ',', '.'), $campos_texto[0]['reducao_governo']).$quebrar_linha;
    }
/********************************************************************************/
/*****************************Textos Particularizados****************************/
/********************************************************************************/
    $informacoes_complementares.= $texto_nf.$quebrar_linha;
/********************************************************************************/
/*5.949 / 6.949 - NF de Conserto 
5.501 / 6.501 - NF Trading...
5.912 / 6.912 - Remessa para Demonstra��o
1.503 / 2.503 - Devolu��o de NF Trading... 
1.201 (1.202) / 2.201 (2.202) - Devolu��o de NF
3.201 / 7.201 - Exporta��o
6.109 / 6.110 - Suframa
5.101 / 6.101 - Nota de Venda
7.101 - Nota de Exporta��o ******/
    $vetor_cfops = array('5.949', '6.949', '5.501', '6.501', '5.912', '6.912', '1.503', '2.503', '1.201', '1.202', '2.201', '2.202', '3.201', '7.201', '6.109', '6.110', '5.101', '6.101', '7.101');

    if(in_array($cfop, $vetor_cfops)) {
/*Se o Cliente for dessas CFOP(s) que correspondem a Suframa, ent�o tem alguns dizeres a + 
nas Informa��es Complementares ...*/
        if($cfop == '6.109' || $cfop == '6.110') $frete_a_pagar = ' - FRETE A PAGAR, MERC. DESTINADA COMERCIALIZA��O.';
        $informacoes_complementares.= $frete_a_pagar.$quebrar_linha;
    }
/*****************************************************************************************************/
/************************************LEI DE SUBSTITUI��O TRIBUT�RIA***********************************/
/*****************************************************************************************************/
/*Sempre que existir pelo menos 1 item em nossa NF que possuir ST, eu apresentarei algum descritivo 
nas informa��es complementares ...*/
    if($calculo_total_impostos['valor_icms_st'] > 0) {
        /*********************************NFs de Consumo com IVA*********************************/
        /*Nesses casos, o Valor do IVA n�o � acrescido do Valor Total da NF, devido a Finalidade da NF 
        ser Consumo, sendo assim tenho que printar esse dizer a mais nesse artigo que Justifica 
        esse caso ...*/
        if($finalidade == 'C') {//Mas ...
            $informacoes_complementares.= 'NF emitida nos termos do INCISO I do Artigo 264 do RICMS/00.'.$quebrar_linha;
        }else {//Revenda apresento normal ...
            $informacoes_complementares.= "Conforme lei N. 6374/89 art 67 parag 1. e conv de 15-12-70 SINIEF, art 19 I, 'I' e V, c e d e parag. 23, � destacado o valor de ICMS/ST em nossa Nota Fiscal.".$quebrar_linha;
        }
    }
/*****************************************************/
/*Caso exista Suframa, ent�o eu exibo esse Texto no Fim da Nota Fiscal, apresentando o 
C�digo do Suframa do Cliente na Parte de Dados Adicionais ...*/
    if($campos_nfs[0]['suframa'] > 0) {
//Se o Suframa for Inativo, ent�o exibo essa Mensagem de N�o Habilitado ao lado ...
        if($campos_nfs[0]['suframa_ativo'] == 'N') $complemento_suframa = ' (N�O HABILITADO)';
        $informacoes_complementares.= $cod_suframa.$complemento_suframa.$quebrar_linha;
    }
/*****************************************************************************************************/
/**********************IVAS UNIFICADOS PARA FORA DO ESTADO - 18 de Junho de 2012**********************/
/*****************************************************************************************************/
    $vetor_cfops_da_nf = array_unique($vetor_cfops_da_nf);
    sort($vetor_cfops_da_nf);
    for($i = 0; $i < count($vetor_cfops_da_nf); $i++) {
        $final_cfop = strstr($vetor_cfops_da_nf[$i], '.');

        if($vetor_base_total_ipi[$vetor_cfops_da_nf[$i]] > 0 && $vetor_valor_total_ipi[$vetor_cfops_da_nf[$i]] <= 0) {
            $base_ipi = ' / Base IPI ISENTO: '.number_format($vetor_base_total_ipi[$vetor_cfops_da_nf[$i]], 2, ',', '.');
        }else {
            $base_ipi = ' / Base IPI: '.number_format($vetor_base_total_ipi[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Val. IPI: '.number_format($vetor_valor_total_ipi[$vetor_cfops_da_nf[$i]], 2, ',', '.');
        }

        if($final_cfop == '.401' || $final_cfop == '.403') {//PA's com IVA - OF (Industrial)
            $informacoes_complementares.= '///CFOP '.$vetor_cfops_da_nf[$i].' ==> Val. Cont�bil Total: '.number_format($vetor_valor_contabil[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Base ICMS: '.number_format($vetor_base_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Val. ICMS: '.number_format($vetor_valor_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').$base_ipi.' / Base ST: '.number_format($vetor_base_total_icms_st[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / ICMS-ST: '.number_format($vetor_valor_total_icms_st[$vetor_cfops_da_nf[$i]], 2, ',', '.').$quebrar_linha;
        }else if($final_cfop == '.404' || $final_cfop == '.405') {//PA's com IVA - OF (Revenda)
            $informacoes_complementares.= '///CFOP '.$vetor_cfops_da_nf[$i].' ==> Val. Cont�bil Total: '.number_format($vetor_valor_contabil[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Base ICMS: '.number_format($vetor_base_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Val. ICMS: '.number_format($vetor_valor_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Base ST: '.number_format($vetor_base_total_icms_st[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / ICMS-ST: '.number_format($vetor_valor_total_icms_st[$vetor_cfops_da_nf[$i]], 2, ',', '.').$quebrar_linha;
        }else if($final_cfop == '.101') {//PA's sem IVA - OF (Industrial)
            $informacoes_complementares.= '///CFOP '.$vetor_cfops_da_nf[$i].' ==> Val. Cont�bil Total: '.number_format($vetor_valor_contabil[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Base ICMS: '.number_format($vetor_base_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Val. ICMS: '.number_format($vetor_valor_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Isento ICMS: '.number_format($vetor_isento[$vetor_cfops_da_nf[$i]], 2, ',', '.').$base_ipi.$quebrar_linha;
        }else if($final_cfop == '.102') {//PA's sem IVA - OF (Revenda)
            $informacoes_complementares.= '///CFOP '.$vetor_cfops_da_nf[$i].' ==> Val. Cont�bil Total: '.number_format($vetor_valor_contabil[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Base ICMS: '.number_format($vetor_base_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Val. ICMS: '.number_format($vetor_valor_total_icms[$vetor_cfops_da_nf[$i]], 2, ',', '.').' / Isento ICMS: '.number_format($vetor_isento[$vetor_cfops_da_nf[$i]], 2, ',', '.').$quebrar_linha;
        }
    }
/*****************************************************************************************************/
    $informacoes_complementares.= 'BASE FECP '.number_format($base_calculo_fecp, 2, ',', '.').' / FECP: '.number_format($valor_fecp, 2, ',', '.').$quebrar_linha;
    
//Se existir Faturas/Duplicatas, ent�o essa informa��o tamb�m � apresentada no fim da Impress�o da Nota ...
    if(!empty($faturas_inf_compl)) $informacoes_complementares.= '****FATURA(S)____'.$faturas_inf_compl;
    $linhas_inf_compl           = explode($quebrar_linha, trim($informacoes_complementares));
    $informacoes_complementares = '';//Zero essa vari�vel porque aqui ela vai ser tratada ...
    for($l = 0; $l < count($linhas_inf_compl); $l++) {
        $informacoes_complementares.= trim($linhas_inf_compl[$l]);
        if(($l + 1) < count($linhas_inf_compl)) $informacoes_complementares.= '; ';
    }
    /***************************************************************************************/
    /***********************************Cliente paga GNRE***********************************/
    /***************************************************************************************/
    if($calculo_total_impostos['valor_icms_st'] > 0) {
        $complemento_cobranca = '';
        //Se a Qtde de Vias for mais do que uma ...
        if($qtde_vias_duplic > 1) $complemento_cobranca = ' na Primeira Duplicata';
        if($id_uf_cliente != 1) {//Se for de um Estado diferente de S�o Paulo ...
            if($campos_convenio[0]['convenio'] == '') {//� existe conv�nio, ent�o significa que o Cliente ir� pagar a GNRE se existir ST ...
                $informacoes_complementares.= ' - Cobran�a de R$ '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.').$complemento_cobranca.' referente Documento de Arrecada��o Estadual (Antecipa��o de ICMS)';
            }else {//Existe conv�nio, ent�o significa que o Cliente n�o ir� pagar a GNRE se existir ST e sim n�s "Empresa" ...
                //$informacoes_complementares.= ' - Cobran�a de R$ '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.').$complemento_cobranca.' referente Substitui��o Tribut�ria (Antecipa��o de ICMS)'.$quebrar_linha;
            }
        }else {//Estado de S�o Paulo, nunca ir� pagar porque n�s somos de S�o Paulo igual ao Cliente ...
            //$informacoes_complementares.= ' - Cobran�a de R$ '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.').$complemento_cobranca.' referente Substitui��o Tribut�ria (Antecipa��o de ICMS)'.$quebrar_linha;
        }
    }
    /***************************************************************************************/
    //Aqui eu busco todos os N.� de Pedidos do Cliente que foram atrelados a essa Nota ...
    $sql = "SELECT DISTINCT(pv.`num_seu_pedido`) 
            FROM `nfs_itens` nfsi 
            INNER JOIN `pedidos_vendas_itens` pvi ON nfsi.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE nfsi.`id_nf` = '$id_nf' ";
    $campos_pedido_vendas = bancos::sql($sql);
    $linhas_pedido_vendas = count($campos_pedido_vendas);
    //Lista dos N.� de Pedidos do Cliente - Seu n�mero de Pedido ...
    for($i = 0; $i < $linhas_pedido_vendas; $i++) {
        $num_seu_pedidos.= $campos_pedido_vendas[$i]['num_seu_pedido'].', ';
    }
    $num_seu_pedidos = substr($num_seu_pedidos, 0, strlen($num_seu_pedidos) - 2);
    $informacoes_complementares.= ' - (S/N PEDIDO(S): '.$num_seu_pedidos.')';

    /******************************************************************************/
    /*Se o Cliente � Optante Simples Nacional e esta no Estado de "SC", devido ao 
    Decreto 3.467/10 � 3� de 19.08.10, existe uma redu��o de 70% no IVA ...*/
    if($optante_simples_nacional == 'S' && $id_uf_cliente == 7) $informacoes_complementares.= ' - Decreto 3.467/10 Par�grafo 3� estabelece a utiliza��o de apenas 30% das MVA e MVA-Ajustada quando o adquirente for contribuinte enquadrado e com apura��o pelo Simples Nacional.'.$quebrar_linha;
    /******************************************************************************/

    /**************************************************************************************/
    /*************Layout - Dados Adicionais e Informa��es Complementares da NF*************/
    /**************************************************************************************/
    $texto.= 'Z|'.strtr(strtoupper($dados_adicionais), $caracteres_invalidos, $caracteres_validos).'|'.strtr(strtoupper($informacoes_complementares), $caracteres_invalidos, $caracteres_validos).$quebrar_linha;
    /************************************************************************************************************/
    /************************************** CFOP 7.101 - Venda p/ Exporta��o*************************************/ 
    /************************************************************************************************************/
    if($cfop == '7.101') $texto.= 'ZA|SP|SAO PAULO|'.$quebrar_linha;
}else {//Layout para NF Outras ...
    //Fun��o para o c�lculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor cont�m Frete + Impostos e etc ...
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf_outra, 'NFO');
    $peso_lote_total_kg     = $calculo_total_impostos['peso_lote_total_kg'];

    //Busca dos Itens da NF Outras ...
    $sql = "SELECT cf.`classific_fiscal`, cf.`cest`, nfsoi.`id_nf_outra_item`, nfsoi.`id_produto_acabado`, nfsoi.`id_produto_insumo`, 
            nfsoi.`referencia`, nfsoi.`discriminacao`, nfsoi.`id_classific_fiscal`, nfsoi.`origem_mercadoria`, nfsoi.`situacao_tributaria`, 
            nfsoi.`qtde` AS qtde_nota, nfsoi.`valor_unitario` AS valor_item, nfsoi.`ipi` AS ipi, nfsoi.`icms`, 
            nfsoi.`reducao`, nfsoi.`icms_intraestadual`, nfsoi.`iva`, nfsoi.`peso_unitario`, nfsoi.`imposto_importacao`, 
            nfsoi.`valor_cif`, nfsoi.`bc_icms_item`, nfsoi.`pis`, nfsoi.`cofins`, nfsoi.`bc_pis_cofins`, nfsoi.`despesas_aduaneiras`, 
            nfsoi.`despesas_acessorias`, u.`sigla` 
            FROM `nfs_outras_itens` nfsoi 
            INNER JOIN `unidades` u ON u.`id_unidade` = nfsoi.`id_unidade` 
            INNER JOIN `nfs_outras` nfso ON nfso.`id_nf_outra` = nfsoi.`id_nf_outra` 
            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = nfsoi.`id_classific_fiscal` 
            WHERE nfsoi.`id_nf_outra` = '$id_nf_outra' ";
    $campos_itens       = bancos::sql($sql);
    $linhas_nfso_itens  = count($campos_itens);
    
/*Busco a CFOP equivalente ao id_cfop que foi selecionado no Cabe�alho da Nota Fiscal ou do que foi encontrado encontrado a� 
pelo caminho desse Script se esta CFOP for pertinente a uma Nota Fiscal Complementar ...*/
    $sql = "SELECT `cfop`, `num_cfop` 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_cfop    = bancos::sql($sql);
    $cfop_numero    = $campos_cfop[0]['cfop'].'.'.$campos_cfop[0]['num_cfop'];
    /************************************************************************************************************/
    /********************************** CFOP 3.101 - Compra p/ Industrializa��o**********************************/
    /************************************************************************************************************/
    //Somente nessa CFOP que os c�lculos s�o totalmente diferentes ...
    if($cfop_numero == '3.101' && $id_empresa_nota != 4) {
        //Nesses casos de Importa��o, n�o preciso verificar a Opera��o de Fat., trato todos os Itens como se fossem Ind.
        $cfop = $cfop_industrial;
        for($i = 0; $i < $linhas_nfso_itens; $i++) {
            //Essas vari�veis ser�o utilizadas mais abaixo ...
            $dados_produto      = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $id_uf_cliente, $campos_nfso[0]['id_cliente'], $id_empresa_nota, $finalidade, 'S', 0, $id_nf_outra);
            $cfop               = $dados_produto['cfop'];
            
            $calculo_impostos_item              = calculos::calculo_impostos($campos_itens[$i]['id_nf_outra_item'], $id_nf_outra, 'NFO');
            $base_calculo_icms_item             = $calculo_impostos_item['base_calculo_icms'];
            $valor_icms_item                    = $calculo_impostos_item['valor_icms'];
            $valor_total_produtos_item          = $calculo_impostos_item['valor_total_produtos'];
            $valor_ipi_item                     = $calculo_impostos_item['valor_ipi'];
            $imposto_importacao_item            = $calculo_impostos_item['imposto_importacao'];
            $frete_despesas_acessorias_item     = $calculo_impostos_item['frete_despesas_acessorias'];
            $ipi_frete_despesas_acessorias_item = $calculo_impostos_item['ipi_frete_despesas_acessorias'];
            $iva_ajustado_item                  = ($calculo_impostos_item['iva_ajustado'] * 100);
            
            $despesas_acessorias                = ($campos_itens[$i]['despesas_acessorias'] != '0.00') ? number_format($campos_itens[$i]['despesas_acessorias'], 2, '.', '') : '';
    /**************************************************************************************/
    /****************Layout - Dados do Item da NF - Refer�ncia, Qtde, Pre�o****************/
    /**************************************************************************************/	
            $texto.= 'H|'.($i + 1).'|'.$quebrar_linha;
            $texto.= 'I| - ||'.strtr(strtoupper($campos_itens[$i]['discriminacao']), $caracteres_invalidos, $caracteres_validos).'|'.str_replace('.', '', $campos_itens[$i]['classific_fiscal']).'||'.str_replace('.', '', $dados_produto['cfop']).'|'.$campos_itens[$i]['sigla'].'|'.number_format($campos_itens[$i]['qtde_nota'], 4, '.', '').'|'.number_format($campos_itens[$i]['valor_item'], 8, '.', '').'|'.number_format($valor_total_produtos_item, 2, '.', '').'||'.$campos_itens[$i]['sigla'].'|'.number_format($campos_itens[$i]['qtde_nota'], 4, '.', '').'|'.number_format($campos_itens[$i]['valor_item'], 8, '.', '').'||||'.$despesas_acessorias.'|1|||'.$quebrar_linha;
    /**************************************************************************************/
    /****************************Layout - Dados de Importa��o *****************************/
    /**************************************************************************************/	
            //� o Pr�prio Texto da NF ...
            $linhas_texto = explode(chr(13), $campos_nfso[0]['texto_nf']);

            $numero_di = strchr($linhas_texto[0], ' ');
            $numero_di = str_replace(' ', '', (strtr($numero_di, '/-', '  ')));
            $numero_di = str_replace('D.I.', '', $numero_di);
            
            $data_registro = trim(strtr(strchr($linhas_texto[1], '- '), '-', ' '));
            $data_registro = data::datatodate($data_registro, '-');
            
            /*Estamos supondo que esse campo sempre ser� A�reo quando o "$despesas_acessorias / AFRMM" for vazio, o AFRMM � totalmente obrigat�rio
            quando for mar�timo conforme Despachante LOLIS ...*/
            if(empty($despesas_acessorias)) {//�ereo ...
                $local                          = 'AEROPORTO INTARNACIONAL DE S�O PAULO / GUARULHOS';
                $via_transporte_internacional   = 4;
            }else {//Mar�timo ...
                $local                          = 'SANTOS';
                $via_transporte_internacional   = 1;
            }

            $texto.= 'I18|'.$numero_di.'|'.$data_registro.'|'.$local.'|SP|'.date('Y-m-d').'|'.$via_transporte_internacional.'|'.$despesas_acessorias.'|1|||'.$campos_cliente[0]['cod_cliente'].'|'.$quebrar_linha;
            $texto.= 'I25|1|'.($i + 1).'|'.$campos_cliente[0]['cod_cliente'].'|0.01'.$quebrar_linha;

            $texto.= 'M|'.$quebrar_linha;
    /**************************************************************************************/
    /******************Layout - Dados de ICMS do Item, Al�quota, Valor ********************/
    /**************************************************************************************/
            $situacao_tributaria    = $campos_itens[$i]['situacao_tributaria'];
            /****************************************************************************************************/
            //Fa�o esse tratamento espec�fico p/ n�o dar erro nessa parte do Layout que come�a com a Letra N\ ...
            $iva_layout = ($campos_itens[$i]['iva'] > 0) ? number_format($iva_ajustado_item, 2, '.', '') : '';
            /****************************************************************************************************/

            //Quando for Importa��o o Prefixo da Situa��o Tribut�ria � = 1 ...
            $texto.= 'N|'.$quebrar_linha;
            if($situacao_tributaria == '00') {//Tributada Integralmente ...
                $texto.= 'N02|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($campos_itens[$i]['bc_icms_item'], 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
            }else if($situacao_tributaria == '10') {//Tributada com cobran�a de ICMS por Substitui��o Tribut�ria ...
                $texto.= 'N03|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($campos_itens[$i]['bc_icms_item'], 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4||||'.$iva_layout.'|'.$quebrar_linha;
            }else if($situacao_tributaria == '20') {//Com redu��o de Base de C�lculo ...
                $texto.= 'N04|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($campos_itens[$i]['reducao'], 2, '.', '').'|'.number_format($campos_itens[$i]['bc_icms_item'], 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
            //40 - Isenta ou 41 - N�o tributada ou 50 - Suspens�o ...
            }else if($situacao_tributaria == '40' || $situacao_tributaria == '41' || $situacao_tributaria == '50') {
                $texto.= 'N06|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.$quebrar_linha;
            }else if($situacao_tributaria == '51') {//51 - Diferimento ...	
                $texto.= 'N07|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|0.00|0.00|0.00|0.00'.$quebrar_linha;
            }else if($situacao_tributaria == '60') {//Cobrado anteriormente por substitui��o tribut�ria ...
                $texto.= 'N08|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|0.00|0|0.00'.$quebrar_linha;
            }else if($situacao_tributaria == '90') {//Outras ...
                $texto.= 'N10|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3||'.number_format($campos_itens[$i]['bc_icms_item'], 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4|||0.00|0.00|0.00'.$quebrar_linha;
            }
    /**************************************************************************************/
    /*******************Layout - Dados de IPI do Item, Al�quota, Valor ********************/
    /**************************************************************************************/
            $texto.= 'O||||999'.$quebrar_linha;
            $texto.= 'O07|00|'.number_format($valor_ipi_item + $ipi_frete_despesas_acessorias_item, 2, '.', '').$quebrar_linha;
            $texto.= 'O10|'.number_format($valor_total_produtos_item + $frete_despesas_acessorias_item, 2, '.', '').'|'.number_format($campos_itens[$i]['ipi'], 2, '.', '').$quebrar_linha;
    /**************************************************************************************/
    /****************************Layout - Dados de Importa��o *****************************/
    /**************************************************************************************/
            $texto.= 'P|'.number_format($campos_itens[$i]['valor_cif'], 2, '.', '').'|'.number_format($campos_itens[$i]['despesas_aduaneiras'], 2, '.', '').'|'.number_format($imposto_importacao_item, 2, '.', '').'|0.00'.$quebrar_linha;
            $total_ii_itens_rs+= $imposto_importacao_item;
    /**************************************************************************************/
    /********Layout - Dados de PIS do Item que existe mesmo independente de Suframa********/
    /**************************************************************************************/
            $texto.= 'Q|'.$quebrar_linha;
            $texto.= 'Q05|70|'.$campos_itens[$i]['pis'].'|'.$quebrar_linha;//Isento
            $texto.= 'Q07|'.$campos_itens[$i]['bc_pis_cofins'].'|1.65|'.$quebrar_linha;//Isento
            $total_pis_itens_rs+= $campos_itens[$i]['pis'];
    /**************************************************************************************/
    /*******Layout - Dados de Cofins do Item que existe mesmo independente de Suframa******/
    /**************************************************************************************/                            
            $texto.= 'S|'.$quebrar_linha;
            $texto.= 'S05|70|'.$campos_itens[$i]['cofins'].'|'.$quebrar_linha;//Isento
            $texto.= 'S07|'.$campos_itens[$i]['bc_pis_cofins'].'|'.number_format($cofins_importacao, 2, '.', '').'|'.$quebrar_linha;//Isento
            $total_cofins_itens_rs+= $campos_itens[$i]['cofins'];

            //Insere no vetor o Elemento corrente ...
            if(!in_array($campos_itens[$i]['id_classific_fiscal'], $vetor_classific_fiscal)) array_push($vetor_classific_fiscal, $campos_itens[$i]['id_classific_fiscal']);
        }
    }else {
        for($i = 0; $i < $linhas_nfso_itens; $i++) {
            $dados_produto      = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $id_uf_cliente, $campos_nfso[0]['id_cliente'], $id_empresa_nota, $finalidade, 'S', 0, $id_nf_outra);
            $cfop               = $dados_produto['cfop'];
//Busca o Peso do Lote do Item Espec�fico em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $peso_lote_total_kg_item = round($campos_itens[$i]['qtde_nota'] * $campos_itens[$i]['peso_unitario'], 4);
            
/**********************************************Vari�veis**********************************************/
            $calculo_impostos_item              = calculos::calculo_impostos($campos_itens[$i]['id_nf_outra_item'], $id_nf_outra, 'NFO');
            $base_calculo_icms_item             = $calculo_impostos_item['base_calculo_icms'];
            $valor_icms_item                    = $calculo_impostos_item['valor_icms'];
            
            if($id_uf_cliente == 1) {//Clientes que s�o do nosso Estado de S�o Paulo pagam ST normalmente se existir ...
                $base_calculo_icms_st_item      = $calculo_impostos_item['base_calculo_icms_st'];
                $valor_icms_st_item             = $calculo_impostos_item['valor_icms_st'];
            }else {
                //Verifico se no Estado do Cliente existe algum Conv�nio ...
                $sql = "SELECT convenio 
                        FROM `ufs` 
                        WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                $campos_convenio = bancos::sql($sql);
                /*Se n�o existe conv�nio entre Estados, ent�o zero os valores de ST p/ cada Item ...

                Observa��o: O nosso Sistema poderia ignorar o c�lculo de ST na Fun��o de C�lculo de Impostos uma vez sabendo 
                que n�o se destaca esse Tributo em NF quando n�o existe conv�nio entre o nosso Estado "S�o Paulo" e o Estado 
                do Cliente, por�m ele calcula o IVA porque no caso do Rio de Janeiro, somos n�s quem pagamos a 
                Guia DARJ p/ esse Estado e os Clientes de l� n�o sabem como fazer o C�lculo ...*/
                if($campos_convenio[0]['convenio'] == '') {
                    $base_calculo_icms_st_item          = 0;
                    $valor_icms_st_item                 = 0;
                }else {//Como existe Conv�nio, ent�o eu destaco os Impostos de ST normalmente no Layout ...
                    $base_calculo_icms_st_item          = $calculo_impostos_item['base_calculo_icms_st'];
                    $valor_icms_st_item                 = $calculo_impostos_item['valor_icms_st'];
                }
            }
            $valor_total_produtos_item          = $calculo_impostos_item['valor_total_produtos'];
            $valor_ipi_item                     = $calculo_impostos_item['valor_ipi'];
            $frete_despesas_acessorias_item     = $calculo_impostos_item['frete_despesas_acessorias'];
            $ipi_frete_despesas_acessorias_item = $calculo_impostos_item['ipi_frete_despesas_acessorias'];
            $icms_frete_despesas_acessorias_item= $calculo_impostos_item['icms_frete_despesas_acessorias'];

            //Essa vari�veis s�o utilizadas mais abaixo ...
            $valor_ipi_item_old = $valor_ipi_item;

            /*Verifico a Finalidade da NF - sempre que a NF for revenda eu zero o 
            valor dessas vari�veis que foi calculado anteriormente, porque ir� influenciar nos resultados 
            de bases de c�lculo ...*/
            if($finalidade == 'R') {
                $valor_ipi_item = 0;
                $ipi_frete_despesas_acessorias_item = 0;
            }
            /******************************C�lculo da Substitui��o Tribut�ria (ST)********************************/
            /*Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo  

            1) Notas Fiscais de Revenda ...
            2) Quando possuir iva  ...
            3) Quando a NF for com Nota mesmo ...*/
            if($finalidade == 'R' && ($campos_itens[$i]['iva'] > 0 && $campos_itens[$i]['operacao'] == 0 && $id_uf_cliente == 1) || ($campos_itens[$i]['iva'] > 0 && $id_uf_cliente > 1)) {
                //Essa vari�vel aqui s� ser� utilizada nos dados adicionais ...
                $iva_ajustado_para_dados_add = (((1 + $campos_itens[$i]['iva'] / 100) * (1 - $campos_itens[$i]['icms'] / 100) / (1 - $campos_itens[$i]['icms_intraestadual'] / 100)) - 1) * 100;
                array_push($vetor_iva, $iva_ajustado_para_dados_add);
            }
/*****************************************************************************************************/
//Alguns arredondamentos para facilitar ...
            $isento_rs                              = $valor_total_produtos_item + $frete_despesas_acessorias_item - $base_calculo_icms_item;
            $valor_contabil_rs                      = $valor_total_produtos_item + $frete_despesas_acessorias_item + $valor_ipi_item_old + $valor_icms_st_item;
            //Total dos Pesos ...$valor_total_produtos_item
            $peso_nf                                = faturamentos::calculo_peso_outras_nfs($id_nf_outra);
            $peso_pro_rata_item                     = round($peso_lote_total_kg_item / $peso_nf['peso_liq_total_nf'] * $campos_nfs[0]['valor_frete'], 2);
/*****************************************************************************************************/
            if(!empty($campos_itens[$i]['id_produto_acabado'])) {//Se foi cadastrado o PA ...
                $sql = "SELECT `referencia`, `discriminacao`, `codigo_barra` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
                $campos_pa          = bancos::sql($sql);
                $referencia         = $campos_pa[0]['referencia'];
                $discriminacao      = $campos_pa[0]['discriminacao'];
                $codigo_barra       = $campos_pa[0]['codigo_barra'];
                $codigo_barra_trib  = $campos_pa[0]['codigo_barra'];
            }else if(!empty($campos_itens[$i]['id_produto_insumo'])) {//Se foi cadastrado o PI ...
                $sql = "SELECT g.`referencia`, pi.`discriminacao` 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                        WHERE pi.`id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi          = bancos::sql($sql);
                $referencia         = $campos_pi[0]['referencia'];
                $discriminacao      = $campos_pi[0]['discriminacao'];
                $codigo_barra       = '';
                $codigo_barra_trib  = '';
            }else if(!empty($campos_itens[$i]['referencia']) || !empty($campos_itens[$i]['discriminacao'])) {//Se foi cadastrado uma Refer�ncia ou Discrimina��o ...
                $referencia         = $campos_itens[$i]['referencia'];
                $discriminacao      = $campos_itens[$i]['discriminacao'];
                $codigo_barra       = '';
                $codigo_barra_trib  = '';
            }
    /**************************************************************************************/
    /****************Layout - Dados do Item da NF - Refer�ncia, Qtde, Pre�o****************/
    /**************************************************************************************/	
            $texto.= 'H|'.($i + 1).'|'.$quebrar_linha;
            $frete_despesas_acessorias_item_layout = ($frete_despesas_acessorias_item > 0) ? number_format($frete_despesas_acessorias_item, 2, '.', '') : '';

            if($campos_itens[$i]['sigla'] == 'UN') {//Se a Unidade for UN, n�o coloco UN direto, existe a regra abaixo ...
                $vetor_referencia   = split('-', $referencia);
                $bitola_pa          = $vetor_referencia[1];//Apenas a parte num�rica ...
                $unidade            = (strpos($bitola_pa, 'S') > 0) ? 'CT' : 'PC';	
            }else {
                $unidade            = $campos_itens[$i]['sigla'];
            }
            $despesas_acessorias    = ($campos_itens[$i]['despesas_acessorias'] != '0.00') ? number_format($campos_itens[$i]['despesas_acessorias'], 2, '.', '') : '';
            
            //Se existir NF Complementar ...
            if($id_nf_comp > 0) {
                $indTot = ($calculo_total_impostos['valor_total_produtos'] > 0) ? 1 : 0;
            }else {
                $indTot = 1;
            }
            
            if(empty($codigo_barra))        $codigo_barra       = 'SEM GTIN';
            if(empty($codigo_barra_trib))   $codigo_barra_trib  = 'SEM GTIN';

            $texto.= 'I|'.$referencia.'|'.$codigo_barra.'|'.strtr(strtoupper($discriminacao), $caracteres_invalidos, $caracteres_validos).'|'.str_replace('.', '', $campos_itens[$i]['classific_fiscal']).'||'.str_replace('.', '', $dados_produto['cfop']).'|'.$unidade.'|'.number_format($campos_itens[$i]['qtde_nota'], 4, '.', '').'|'.number_format($campos_itens[$i]['valor_item'], 8, '.', '').'|'.number_format($campos_itens[$i]['qtde_nota'] * $campos_itens[$i]['valor_item'], 2, '.', '').'|'.$codigo_barra_trib.'|'.$unidade.'|'.number_format($campos_itens[$i]['qtde_nota'], 4, '.', '').'|'.number_format($campos_itens[$i]['valor_item'], 8, '.', '').'|'.$frete_despesas_acessorias_item_layout.'|||'.$despesas_acessorias.'|'.$indTot.'|||'.$quebrar_linha;
            //CEST ...
            if(!empty($campos_itens[$i]['cest'])) $texto.= 'I05c|'.str_replace('.', '', $campos_itens[$i]['cest']).'|'.$quebrar_linha;

            $texto.= 'M|'.$quebrar_linha;
            /****************************************************************************************************/
            //Fa�o esse tratamento espec�fico p/ n�o dar erro nessa parte do Layout que come�a com a Letra N\ ...
            $iva_layout = ($campos_itens[$i]['iva'] > 0) ? number_format($iva_ajustado_item, 2, '.', '') : '';
            /****************************************************************************************************/

            /**************************************************************************************/
            /******************Layout - Dados de ICMS do Item, Al�quota, Valor ********************/
            /**************************************************************************************/
            $situacao_tributaria    = $campos_itens[$i]['situacao_tributaria'];
            /****************************************************************************************************/

            $texto.= 'N|'.$quebrar_linha;
            /*Existe uma fun��o que faz toda essa rotina, mas como em uma NF Outra, nem sempre n�s temos PA, 
            ent�o infelizmente tive que fazer esse trecho de c�digo isolado que est� dentro da Fun��o ...*/
            /*$sql = "SELECT `natureza_operacao_resumida` 
                    FROM `cfops` 
                    WHERE `id_cfop` = '$id_cfop_item' LIMIT 1 ";
            $campos_cfop        = bancos::sql($sql);
            $inicio_natureza    = strtoupper(strtok($campos_cfop[0]['natureza_operacao_resumida'], ' '));*/
            $reducao_bc_item    = ($campos_itens[$i]['reducao'] != 0.00) ? number_format($campos_itens[$i]['reducao'], 2, '.', '') : '0.00';
            
            //Quando n�o for Importa��o o Prefixo da Situa��o Tribut�ria � = 0 ...
            if($situacao_tributaria == '00') {//Tributada Integralmente ...
                $texto.= 'N02|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
            }else if($situacao_tributaria == '10') {//Tributada com cobran�a de ICMS por Substitui��o Tribut�ria ...
                $texto.= 'N03|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4|'.$iva_layout.'||'.number_format($base_calculo_icms_st_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms_intraestadual'], 2, '.', '').'|'.number_format($valor_icms_st_item, 2, '.', '').$quebrar_linha;
            }else if($situacao_tributaria == '20') {//Com redu��o de Base de C�lculo ...
                $texto.= 'N04|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.$reducao_bc_item.'|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').$quebrar_linha;
                //40 - Isenta ou 41 - N�o tributada ou 50 - Suspens�o ...
            }else if($situacao_tributaria == '40' || $situacao_tributaria == '41' || $situacao_tributaria == '50') {
                $texto.= 'N06|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.$quebrar_linha;
            }else if($situacao_tributaria == '51') {//51 - Diferimento ...	
                $texto.= 'N07|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|0.00|0.00|0.00|0.00'.$quebrar_linha;
            }else if($situacao_tributaria == '60') {//Cobrado anteriormente por substitui��o tribut�ria ...
                $texto.= 'N08|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|0.00|0|0.00'.$quebrar_linha;
            }else if($situacao_tributaria == '90') {//Outras ...
                $texto.= 'N10|'.$campos_itens[$i]['origem_mercadoria'].'|'.$situacao_tributaria.'|3|'.$reducao_bc_item.'|'.number_format($base_calculo_icms_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms'], 2, '.', '').'|'.number_format($valor_icms_item, 2, '.', '').'||||4|'.$iva_layout.'||'.number_format($base_calculo_icms_st_item, 2, '.', '').'|'.number_format($campos_itens[$i]['icms_intraestadual'], 2, '.', '').'|'.number_format($valor_icms_st_item, 2, '.', '').$quebrar_linha;
            }
            
            if($id_nf_comp > 0) {//Se existir NF Complementar ...
                $texto.= 'O||||999'.$quebrar_linha;
                $texto.= ($campos_nfso[0]['tipo_nfe_nfs'] == 'E') ? 'O08|02'.$quebrar_linha : 'O08|52'.$quebrar_linha;//Entrada Isenta ou Sa�da Isenta
                /**************************************************************************************/
                /********Layout - Dados de PIS do Item que existe mesmo independente de Suframa********/
                /**************************************************************************************/
                $texto.= 'Q|'.$quebrar_linha;
                $texto.= 'Q04|07'.$quebrar_linha;//Isento
                /**************************************************************************************/
                /*******Layout - Dados de Cofins do Item que existe mesmo independente de Suframa******/
                /**************************************************************************************/
                $texto.= 'S|'.$quebrar_linha;
                $texto.= 'S04|07'.$quebrar_linha;//Isento
            }else {//NF Outra ...
    /**************************************************************************************/
    /*******************Layout - Dados de IPI do Item, Al�quota, Valor ********************/
    /**************************************************************************************/
                if($campos_itens[$i]['ipi'] > 0) {
                    $texto.= 'O||||999'.$quebrar_linha;
                    $texto.= 'O07|50|'.number_format($valor_ipi_item_old, 2, '.', '').$quebrar_linha;
                    $texto.= 'O10|'.number_format($valor_total_produtos_item + $frete_despesas_acessorias_item, 2, '.', '').'|'.number_format($campos_itens[$i]['ipi'], 2, '.', '').$quebrar_linha;
                }
    /**************************************************************************************/
    /********Layout - Dados de PIS do Item que existe mesmo independente de Suframa********/
    /**************************************************************************************/
                $texto.= 'Q|'.$quebrar_linha;

                //Em outros tipos de NF em que a CFOP = "Remessa" ou = "Retorno" n�o existe Tributa��o ...
                if($inicio_natureza == 'REMESSA' || $inicio_natureza == 'RETORNO') {//N�o existe Impostos p/ algo que n�o est� sendo vendido ...
                    $texto.= 'Q04|08|'.$quebrar_linha;
                }else {
                    //Aqui eu retiro o Valor de ICMS do Item porque o Valor Total dos Produtos j� cont�m esse Embutido, sen�o ter�amos uma Bi-Tributa��o ...
                    $base_calculo_pis_pro_rata_item = round(($valor_total_produtos_item + $frete_despesas_acessorias_item - $valor_icms_item), 2);
                    $calculo_pis_pro_rata_item      = round($base_calculo_pis_pro_rata_item * $pis / 100, 2);
                    
                    $texto.= 'Q02|01|'.number_format($base_calculo_pis_pro_rata_item, 2, '.', '').'|'.number_format($pis, 2, '.', '').'|'.number_format($calculo_pis_pro_rata_item, 2, '.', '').$quebrar_linha;
                }
    /**************************************************************************************/
    /*******Layout - Dados de Cofins do Item que existe mesmo independente de Suframa******/
    /**************************************************************************************/
                $texto.= 'S|'.$quebrar_linha;

                //Em outros tipos de NF em que a CFOP = "Remessa" ou = "Retorno" n�o existe Tributa��o ...
                if($inicio_natureza == 'REMESSA' || $inicio_natureza == 'RETORNO') {//N�o existe Impostos p/ algo que n�o est� sendo vendido ...
                    $texto.= 'S04|08|'.$quebrar_linha;
                }else {
                    //Aqui eu retiro o Valor de ICMS do Item porque o Valor Total dos Produtos j� cont�m esse Embutido, sen�o ter�amos uma Bi-Tributa��o ...
                    $base_calculo_cofins_pro_rata_item  = round(($valor_total_produtos_item + $frete_despesas_acessorias_item - $valor_icms_item), 2);
                    $calculo_cofins_pro_rata_item       = round($base_calculo_cofins_pro_rata_item * $cofins / 100, 2);
                    
                    
                    $texto.= 'S02|01|'.number_format($base_calculo_cofins_pro_rata_item, 2, '.', '').'|'.number_format($cofins, 2, '.', '').'|'.number_format($calculo_cofins_pro_rata_item, 2, '.', '').$quebrar_linha;
                }
            }
            //Insere no vetor o Elemento corrente ...
            if(!in_array($campos_itens[$i]['id_classific_fiscal'], $vetor_classific_fiscal)) array_push($vetor_classific_fiscal, $campos_itens[$i]['id_classific_fiscal']);
        }
    }
    /**************************************************************************************/
    /***********************Layout - Dados do Total de Impostos da NF**********************/
    /**************************************************************************************/
    /*Quando o Cliente � Optante pelo Simples Nacional e existe Valor de ST ent�o eu zero a 
    Base de C�lculo de ICMS e Valor de ICMS por causa da lei ...*/
    if($optante_simples_nacional == 'S' && $calculo_total_impostos['valor_icms_st'] > 0) {
        $base_calculo_icms      = 0;
        $valor_icms             = 0;
    }else {
        $base_calculo_icms      = $calculo_total_impostos['base_calculo_icms'];
        $valor_icms             = $calculo_total_impostos['valor_icms'];
    }
    $desconto                   = (abs($calculo_total_impostos['desconto']) > 0) ? number_format(abs($calculo_total_impostos['desconto']), 2, '.', '') : '';
    
    $texto.= 'W|'.$quebrar_linha;
    $texto.= 'W02|'.number_format($base_calculo_icms, 2, '.', '').'|'.number_format($valor_icms, 2, '.', '').'|0.00|0.00|'.number_format($calculo_total_impostos['base_calculo_icms_st'], 2, '.', '').'|'.number_format($calculo_total_impostos['valor_icms_st'], 2, '.', '').'|0.00|0.00|'.number_format($calculo_total_impostos['valor_total_produtos'], 2, '.', '').'|'.number_format($calculo_total_impostos['valor_frete'], 2, '.', '').'|0.00|'.$desconto.'|'.number_format($total_ii_itens_rs, 2, '.', '').'|'.number_format(round($calculo_total_impostos['valor_ipi'], 2), 2, '.', '').'|0.00|'.number_format($total_pis_itens_rs, 2, '.', '').'|'.number_format($total_cofins_itens_rs, 2, '.', '').'|'.number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, '.', '').'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;

    //Busca dos dados da Transportadora ...
    $sql = "SELECT * 
            FROM `transportadoras` t 
            WHERE `id_transportadora` = '".$campos_nfso[0]['id_transportadora']."' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_transportadoras = bancos::sql($sql);
    //Frete por Conta ...
    //Se for Nosso Carro ou o "Cliente for Krahenbuhl o Ind�stria Nardini" ent�o ...
    if($campos_nfso[0]['id_transportadora'] == 795 || $campos_nfso[0]['id_cliente'] == 1020 || $campos_nfso[0]['id_cliente'] == 2234) {//Frete por Conta ...
        $frete_por_conta = 0;//Remetente ...
    }else {
        $frete_por_conta = 1;//Destinat�rio ...
    }
    $ie_transportadora 		= (strlen($campos_transportadoras[0]['ie']) == 12) ? $campos_transportadoras[0]['ie'] : '';
    $cnpj_transportadora 	= ($campos_transportadoras[0]['cnpj'] != '00000000000000') ? $campos_transportadoras[0]['cnpj'] : '';
    /**************************************************************************************/
    /***************************Layout - Dados da Transportadora***************************/
    /**************************************************************************************/
    $texto.= 'X|'.$frete_por_conta.$quebrar_linha;
    $texto.= 'X03|'.strtr(strtoupper($campos_transportadoras[0]['nome']), $caracteres_invalidos, $caracteres_validos).'|'.$ie_transportadora.'|'.strtr(strtoupper($campos_transportadoras[0]['endereco']), $caracteres_invalidos, $caracteres_validos).', '.strtr(strtoupper($campos_transportadoras[0]['num_complemento']), $caracteres_invalidos, $caracteres_validos).'|'.strtr(strtoupper($campos_transportadoras[0]['cidade']), $caracteres_invalidos, $caracteres_validos).'|'.strtoupper($campos_transportadoras[0]['uf']).$quebrar_linha;
    $texto.= 'X04|'.$cnpj_transportadora.$quebrar_linha;
    $texto.= 'X26|'.$campos_nfso[0]['qtde_volume'].'|'.$campos_nfso[0]['especie_volume'].'|||'.$campos_nfso[0]['peso_liquido_volume'].'|'.$campos_nfso[0]['peso_bruto_volume'].$quebrar_linha;

    //Busca dos dados de Vencimento da NF para poder gerar as Duplicatas ...
    $data_emissao	= data::datetodata($campos_nfso[0]['data_emissao'], '/');

    if($campos_nfso[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $campos_nfso[0]['vencimento1']);
    $qtde_vias_duplic = 1;//Pelo menos 1 via ir� existir na Duplicata ...

    if($campos_nfso[0]['vencimento2'] > 0) {
        if($campos_nfso[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $campos_nfso[0]['vencimento2']);
        $qtde_vias_duplic++;
    }

    if($campos_nfso[0]['vencimento3'] > 0) {
        if($campos_nfso[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $campos_nfso[0]['vencimento3']);
        $qtde_vias_duplic++;
    }

    if($campos_nfso[0]['vencimento4'] > 0) {
        if($campos_nfso[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $campos_nfso[0]['vencimento4']);
        $qtde_vias_duplic++;
    }
    $valor_duplicata	= faturamentos::valor_duplicata_outras_nfs($id_nf_outra, $nota_sgd, $id_pais);

    if($qtde_vias_duplic == 1) {//Uma �nica Via ...
        $duplicata_array = array($numero_nf);
    }else {//Mais de uma via ...
        $duplicata_array = array($numero_nf.'A', $numero_nf.'B', $numero_nf.'C', $numero_nf.'D');	
    }
    $data_vencimento_array = array($data_vencimento1, $data_vencimento2, $data_vencimento3, $data_vencimento4);

/**************************************************************************************/
/*******************Layout - Dados das Duplicatas da NF********************************/
/**************************************************************************************/
    //Esse Layout gera um texto autom�tico na parte de Informa��es Complementares ...
    $texto.= 'Y'.$quebrar_linha;
    
    if($desconto == 0) {
        $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_nota']), 2, '.', '').'|0.00|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
    }else {
        if($calculo_total_impostos['valor_total_produtos'] == 0) {//Dependendo da NF Complementar podemos ter o "valor_total_produtos" Nulo ...
            $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_nota']), 2, '.', '').'|'.$desconto.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
        }else {//Foi feito qualquer outro Tipo de NF ...
            $texto.= 'Y02|'.$numero_nf.'|'.number_format(abs($calculo_total_impostos['valor_total_produtos']), 2, '.', '').'|'.$desconto.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
        }
    }
    
    for($i = 0; $i < $qtde_vias_duplic; $i++) {
        $texto.= 'Y07|00'.($i + 1).'|'.data::datatodate($data_vencimento_array[$i], '-').'|'.number_format($valor_duplicata[$i], 2, '.', '').$quebrar_linha;
        //Essa informa��o tamb�m � apresentanda nas informa��es complementares ...
        if(($i + 1) == $qtde_vias_duplic) {//Se for a �ltima via ent�o coloca-se o Enter no final ...
            $faturas_inf_compl.= $duplicata_array[$i].' - '.$data_vencimento_array[$i].' - R$ '.number_format($valor_duplicata[$i], 2, ',', '.').'  ****'.$quebrar_linha;
        }else {//Enquanto n�o for a �ltima via, coloca-se ; no final da Duplicata ...
            $faturas_inf_compl.= $duplicata_array[$i].' - '.$data_vencimento_array[$i].' - R$ '.number_format($valor_duplicata[$i], 2, ',', '.').'____';
        }
    }
    /**************************************************************************************/
    /***********************Layout - Forma de Pagamento************************************/
    /**************************************************************************************/
    //Esse Layout gera um texto autom�tico na parte de Informa��es Complementares ...
    $texto.= 'YA'.$quebrar_linha;
    
    $forma_pagamento    = ($campos_nfso[0]['vencimento1'] == 0) ? 0 : 1;
    //Notas Fiscais de Ajuste ou Devolu��o, n�o tem o que receber do Cliente ...
    $meio_de_pagamento  = ($finalidade_emissao == 3 || $finalidade_emissao == 4) ? 90 : 99;
    
    $texto.= 'YA01|'.$forma_pagamento.'|'.$meio_de_pagamento.'|'.number_format(round($calculo_total_impostos['valor_total_nota'], 2), 2, '.', '').$quebrar_linha;
    /******************************Dados Adicionais de Nota Fiscal********************************/
    /******************************Dados Adicionais de Nota Fiscal********************************/
    $dados_adicionais = '(CLIENTE: '.$campos_cliente[0]['cod_cliente'].') ';
    //Dados Adicionais ...
    $vetor_classific_fiscal         = array_unique($vetor_classific_fiscal);//Retira os valores duplicados do Vetor ...
    $id_classific_fiscais           = implode(',', $vetor_classific_fiscal);//Transforma em String ...
    if($id_classific_fiscais == '') $id_classific_fiscais = 0;
//Aqui eu listo todas as Situa��es Tribut�rias que ficaram armazenadas no Vetor ...
    if($id_pais == 31) {//Se a NF for do Brasil ...
        $sql = "SELECT cf.id_classific_fiscal, cf.classific_fiscal, i.reducao 
                FROM `classific_fiscais` cf 
                INNER JOIN `icms` i ON i.id_classific_fiscal = cf.`id_classific_fiscal` AND i.`id_uf` = '$id_uf_cliente' 
                WHERE cf.`id_classific_fiscal` IN ($id_classific_fiscais) ORDER BY cf.id_classific_fiscal ";
    }else {//Se for de Outros Pa�ses - por exemplo Internacional ...
        $sql = "SELECT cf.id_classific_fiscal, cf.classific_fiscal 
                FROM `classific_fiscais` cf 
                WHERE cf.`id_classific_fiscal` IN ($id_classific_fiscais) ORDER BY cf.id_classific_fiscal ";
    }
    $campos_classific_fiscal = bancos::sql($sql);
    $linhas_classific_fiscal = count($campos_classific_fiscal);

//Classifica��es Fiscais ...
    for($i = 0; $i < $linhas_classific_fiscal; $i++) {
        if($campos_classific_fiscal[$i]['id_classific_fiscal'] == 1 || $campos_classific_fiscal[$i]['id_classific_fiscal'] == 2) $mensagem_classific_fiscal = 1;

        $valor_iva_classific_fiscal = '';
/*Se existir valor de IVA (ST) e o Cliente for do Estado de S�o Paulo, ent�o apresento o valor 
ao lado das classifica��es Fiscais na NF ...*/
        if($campos_classific_fiscal[$i]['iva'] > 0 && $id_uf_cliente == 1) {
            $valor_iva_classific_fiscal = ' (IVA = '.number_format($campos_classific_fiscal[$i]['iva'], 2, ',', '.').')';
        }
        $letra_r = '';//Default � Vazio ...
//Se existir Redu��o de ICMS na Base de C�lculo, ent�o eu apresento a letra R, ao lado do N.� da Classific. Fiscal
        if($campos_classific_fiscal[$i]['reducao'] > 0) {
            $letra_r = '/R';
        }
        $classificacoes_fiscais.= $campos_classific_fiscal[$i]['id_classific_fiscal'].$letra_r.'-'.$campos_classific_fiscal[$i]['classific_fiscal'].$valor_iva_classific_fiscal.'; ';
    }
    $classificacoes_fiscais = substr($classificacoes_fiscais, 0, strlen($classificacoes_fiscais) - 2);
    $dados_adicionais.= 'CF: '.$classificacoes_fiscais.$quebrar_linha;

    if($id_pais == 31) {//S� existem esses textos de Isento e de Bases de C�lculo quando for daqui do Brasil ...
/************************************************************************************************************/
/********************************** CFOP 5.908 - Remessa em Comodato**********************************/
/************************************************************************************************************/
//Nessa CFOP a �nica coisa que tem de sair na Impress�o, � a parte na qual se refere a Isento ...
        if($cfop == '5.908') {
//Isento - Bases de C�lculo ICMS Bits Bedames Riscador ...
            $dados_adicionais.= 'ISENTO=R$ '.number_format($calculo_total_impostos['isento'], 2, ',', '.').$quebrar_linha;
        }else {
//Isento - Bases de C�lculo ICMS Bits Bedames Riscador ...
            $dados_adicionais.= 'ISENTO=R$ '.number_format($calculo_total_impostos['isento'], 2, ',', '.').'; BC ICMS BITS BED RISC=R$ '.number_format($calculo_total_impostos['base_calculo_icms_bits_bedames_riscador'], 2, ',', '.').$quebrar_linha;
//Bases de C�lculo ICMS Bits C/ Redu��o
            $dados_adicionais.= 'BC ICMS C RED=R$ '.number_format($calculo_total_impostos['base_calculo_icms_c_red'], 2, ',', '.').'; BC ICMS S RED=R$ '.number_format($calculo_total_impostos['base_calculo_icms_s_red'], 2, ',', '.').$quebrar_linha;
        }
/**********************************************************************************/
    }else {
/************************************************************************************************************/
/********************************** CFOP 3.101 - Compra p/ Industrializa��o**********************************/
/************************************************************************************************************/
//Somente nessa CFOP que a Impress�o de Textos � diferente ...
        if($cfop == '3.101') {
//Listo aqui as Bases de C�lculo e Valores de ICMS dos Itens de NF ...
            for($i = 0; $i < $linhas_nfso_itens; $i++) {
                $valor_icms_item = round(($campos_itens[$i]['bc_icms_item'] * $campos_itens[$i]['icms'] / 100) * ((100 - $campos_itens[$i]['reducao']) / 100), 2);
                $dados_adicionais.= 'Base de C�lculo ICMS ITEM '.($i + 1).' - R$ '.number_format($campos_itens[$i]['bc_icms_item'], 2, ',', '.').$quebrar_linha;
                $dados_adicionais.= 'Valor do ICMS ITEM '.($i + 1).' - R$ '.number_format($valor_icms_item, 2, ',', '.').$quebrar_linha;
            }
        }
    }
    $linhas_dados_adicionais = explode($quebrar_linha, trim($dados_adicionais));
    $dados_adicionais = '';//Zero essa vari�vel porque aqui ela vai ser tratada ...
    for($l = 0; $l < count($linhas_dados_adicionais); $l++) {
        $dados_adicionais.= trim($linhas_dados_adicionais[$l]);
        if(($l + 1) < count($linhas_dados_adicionais)) $dados_adicionais.= '; ';
    }
    /******************************Informa��es Complementares de Nota Fiscal********************************/
    if($mensagem_classific_fiscal == 1) {
        $sql = "SELECT icms.`reducao`, cf.`reducao_governo` 
                FROM `icms` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = icms.`id_classific_fiscal` 
                WHERE icms.`id_uf` = '$id_uf_cliente' 
                AND icms.`id_classific_fiscal` = '1' 
                AND icms.`ativo` = '1' LIMIT 1 ";
        $campos_texto               = bancos::sql($sql);
        $informacoes_complementares = str_replace('?', number_format($campos_texto[0]['reducao'], 2, ',', '.'), $campos_texto[0]['reducao_governo']);
    }
    //� o Pr�prio Texto da NF ...
    $linhas_texto = explode(chr(13), $campos_nfso[0]['texto_nf']);
    for($l = 0; $l < count($linhas_texto); $l++) {
        $texto_nf.= trim($linhas_texto[$l]);
        if(($l + 1) < count($linhas_texto)) $texto_nf.= '; ';
    }
    $informacoes_complementares.= $texto_nf;
    /*****************************************************************************************************/
    //Se existir Faturas/Duplicatas, ent�o essa informa��o tamb�m � apresentada no fim da Impress�o da Nota ...
    if(!empty($faturas_inf_compl)) $informacoes_complementares.= '****FATURA(S)____'.$faturas_inf_compl;
    $linhas_inf_compl           = explode($quebrar_linha, trim($informacoes_complementares));
    $informacoes_complementares = '';//Zero essa vari�vel porque aqui ela vai ser tratada ...
    for($l = 0; $l < count($linhas_inf_compl); $l++) {
        $informacoes_complementares.= trim($linhas_inf_compl[$l]);
        if(($l + 1) < count($linhas_inf_compl)) $informacoes_complementares.= '; ';
    }
    /**************************************************************************************/
    /****************************Layout - Dados Adicionais da NF***************************/
    /**************************************************************************************/
    //Se a NF for do Tipo Cr�dito Ativo Imobilizado, ent�o igualo os dados Adicionais com as Informa��es Complementares ...
    if($cfop_numero == '1.604') $dados_adicionais = $informacoes_complementares;
    $texto.= 'Z|'.strtr(strtoupper($dados_adicionais), $caracteres_invalidos, $caracteres_validos).'|'.strtr(strtoupper($informacoes_complementares), $caracteres_invalidos, $caracteres_validos).$quebrar_linha;
    /************************************************************************************************************/
    /************************************** CFOP 7.101 - Venda p/ Exporta��o*************************************/
    /********************************02****************************************************************************/
    if($cfop_numero == '7.101') $texto.= 'ZA|SP|SAO PAULO|'.$quebrar_linha;
}

//Gerando o Arquivo p/ Download ...
$filename = 'NFE '.$campos_empresas[0]['nomefantasia'].' N.� '.$numero_nf.'.txt';
$file = fopen($filename, 'w+');
fwrite($file, $texto);
fclose($file);

$mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA') ? 'application/octetstream' : 'application/octet-stream';
header('Content-Type: ' . $mime_type);
if (PMA_USR_BROWSER_AGENT == 'IE') {
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
}else {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');
}
print $texto;
unlink($filename);
?>