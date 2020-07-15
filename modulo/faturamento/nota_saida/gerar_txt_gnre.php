<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/faturamentos.php');
require('../../../lib/data.php');
session_start('funcionarios');

//Busca de alguns dados de NF que podem implicar no Imposto Abaixo ...
$sql = "SELECT ufs.sigla, ufs.convenio, nfs.id_empresa, DATE_FORMAT(nfs.data_emissao,'%d/%m/%Y') AS data_emissao, 
        nnn.numero_nf, e.cnpj, MONTH(nfs.data_emissao) AS ref_mes, YEAR(nfs.data_emissao) AS ref_ano, 
        e.razaosocial, e.endereco, e.numero, e.cidade, e.id_uf id_uf_emp, e.cep cep_emp, e.telefone_comercial, 
        e.ddd_comercial, e.id_pais, nfs.suframa 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        INNER JOIN `ufs` ON ufs.id_uf = c.id_uf 
        INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota 
        INNER JOIN `empresas` e ON e.id_empresa = nfs.id_empresa 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' 
        AND nfs.`id_empresa` IN (1, 2) LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) > 0) {
    $sigla_empresa          = ($campos[0]['id_empresa'] == 1) ? 'alba' : 'tool';
    $sigla_uf               = $campos[0]['sigla'];
    $data_emissao           = $campos[0]['data_emissao'];
    $numero_nf              = str_pad($campos[0]['numero_nf'], 6, "0", STR_PAD_LEFT);
    $cnpj                   = $campos[0]['cnpj'];
    $ref_mes                = (strlen($campos[0]['ref_mes'])==1)?"0".$campos[0]['ref_mes']:$campos[0]['ref_mes'];
    $Ref_mes_ano            = "1".$ref_mes.$campos[0]['ref_ano'];
    $id_pais                = $campos[0]['id_pais'];
    $suframa_nf             = $campos[0]['suframa'];
    
    $calculo_total_impostos = calculos::calculo_impostos(0, $_GET[id_nf], 'NF');
    $valor_icms_st          = number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '');
    $atualiz_monetaria      = '0,00';
    $juros                  = '0,00';//Juros + TAB + { formato #0,00 }
    $multa                  = '0,00';//Multa + TAB + { formato #0,00 }
    $totalrecolher          = $valor_icms_st;
    $data_vencimento        = data::adicionar_data_hora(date("d-m-Y"), 3);
    $convenio               = $campos[0]['convenio'];
    $razaosocial            = $campos[0]['razaosocial'];
    $IE                     = '000000000';
    $endereco               = $campos[0]['endereco'];
    $numero                 = $campos[0]['numero'];
    $cidade                 = $campos[0]['cidade'];
    $id_uf_empresa          = $campos[0]['id_uf_emp'];///////////////// fazer o select da empresa UF da empresa novamente se náo dá erro
    //Busca a Sigla da Unidade Federal da Empresa ...
    $sql = "SELECT sigla 
            FROM `ufs` 
            WHERE `id_uf` = '$id_uf_empresa' limit 1 ";
    $campos_uf_emp      = bancos::sql($sql);
    $uf_empresa         = $campos_uf_emp[0]['sigla'];
    $cep_emp            = $campos[0]['cep_emp'];
    $telefone_emp       = $campos[0]['ddd_comercial'].str_replace('-', '', $campos[0]['telefone_comercial']);
    $informacao         = 'x';
    $produtos           = 99;
    ////////////////////Usando as nomeclaturas da receia /////////////
    $uf_favorecida          = $sigla_uf;//UFFavorecida + TAB + { código da UF }
    $receita                = 100099;//Receita + TAB + ( formato 000000 }
    $CICContribuinte        = $cnpj;//CICContribuinte + TAB + { CNPJ ou CPF não editado }
    $DocumentoOrigem        = $numero_nf;//DocumentoOrigem + TAB + { ver instruções abaixo }
    $Referencia             = $Ref_mes_ano;//Referencia + TAB + { ver instruções abaixo }
    $ValorPrincipal         = $valor_icms_st;//ValorPrincipal + TAB + { formato #0,00 }
    $AtualizacaoMonetaria   = $atualiz_monetaria;//AtualizacaoMonetaria + TAB + { formato #0,00 }
    $Juro                   = $juros;//Juros + TAB + { formato #0,00 }
    $Multa                  = $multa;//Multa + TAB + { formato #0,00 }
    $TotalRecolher          = $totalrecolher;//TotalRecolher + TAB + { formato #0,00 }
    $DataVencimento         = $data_vencimento;//DataVencimento + TAB + { formato dd/mm/aaaa }
    $Convenio               = $convenio;//Convenio + TAB + { texto }
    $RazaoSocial            = $razaosocial;//RazaoSocial + TAB + { texto }
    $InscricaoEstadual      = $IE;//InscricaoEstadual + TAB + { não editada }
    $Endereco               = $endereco.','.$numero;//Endereco + TAB + { texto }
    $Municipio              = $cidade;//Municipio + TAB + { texto }
    $UF                     = $uf_empresa;//UF + TAB + { código da UF }
    $CEP                    = $cep_emp;//CEP + TAB + { formato 00000-000 }
    $Telefone               = $telefone_emp;//Telefone + TAB + { não editado }
    $Informacoes            = $informacao;//Informacoes + TAB + { texto }
    $Produto                = $produtos;//Produto { ver instruções abaixo }*/
}else {
    exit('Erro ao gerar GNRE !');
}

$filename                   = $sigla_empresa."_nf_".$numero_nf.".txt";
$texto                      = $uf_favorecida."\t".$receita."\t".$CICContribuinte."\t".$DocumentoOrigem."\t".$Referencia."\t".$ValorPrincipal."\t".$AtualizacaoMonetaria."\t".$Juro."\t".$Multa."\t".$TotalRecolher."\t".$DataVencimento."\t".$Convenio."\t".$RazaoSocial."\t".$InscricaoEstadual."\t".$Endereco."\t".$Municipio."\t".$UF."\t".$CEP."\t".$Telefone."\t".$Informacoes."\t".$Produto;
$file                       = fopen($filename, 'w+');
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