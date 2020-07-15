<?
require('../../lib/segurancas.php');
require('../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../lib/custos.php');
require('../../lib/data.php');
require('../../lib/estoque_acabado.php');
require('../../lib/intermodular.php');
require('../../lib/producao.php');
session_start('funcionarios');

$layout_produto_acabado = '';
$quebrar_linha          = chr(13).chr(10);

//Albafer ...

/*$layout_produto_acabado.= "0000|61399283000180|Albafer Industria e Comercio de Ferramentas Ltda|1.0".$quebrar_linha;
$layout_produto_acabado.= "0001|Texto em caracteres UTF-8: (dígrafo BR)'ção',(dígrafo espanhol-enhe)'ñ',(trema)'Ü',(ordinais)'ªº',(ligamento s+z alemão)'ß'.".$quebrar_linha;
$layout_produto_acabado.= "0010|61399283000180|Albafer Industria e Comercio de Ferramentas Ltda|105094779117|Rua Dias da Silva , 1183|02114002|São Paulo|SP".$quebrar_linha;
$layout_produto_acabado.= "0990|4".$quebrar_linha;*/

//Tool Master
 
$layout_produto_acabado.= "0000|68340926000160|Tool Master Industria Metalurgica Ltda|1.0".$quebrar_linha;
$layout_produto_acabado.= "0001|Texto em caracteres UTF-8: (dígrafo BR)'ção',(dígrafo espanhol-enhe)'ñ',(trema)'Ü',(ordinais)'ªº',(ligamento s+z alemão)'ß'.".$quebrar_linha;
$layout_produto_acabado.= "0010|68340926000160|Tool Master Industria Metalurgica Ltda|113525669117|Rua dias da Silva , 1173|02114002|São Paulo|SP".$quebrar_linha;
$layout_produto_acabado.= "0990|4".$quebrar_linha;

$layout_produto_acabado.= "5001".$quebrar_linha;

$sql = "SELECT cf.`classific_fiscal`, pa.`id_produto_acabado`, pa.`operacao_custo`, 
        pa.`operacao_custo_sub`, pa.`referencia`, pa.`discriminacao`, pa.`codigo_barra`, SUBSTRING(LOWER(u.`unidade`), 1, 4) AS unidade 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
        WHERE pa.`origem_mercadoria` = '8' 
        AND (pa.`fci_albafer` = '' AND pa.`fci_tool_master` = '') 
        AND pa.`ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);

$total_registros = 0;//Valor Inicial ...

for($i = 0; $i < $linhas; $i++) {
    $preco_fat_nac_nac_min_rs = round(custos::preco_custo_pa($campos[$i]['id_produto_acabado']), 2);
    
    if($preco_fat_nac_nac_min_rs > 0) {
        if($campos[$i]['operacao_custo'] == 0 && $campos[$i]['operacao_custo_sub'] == 0) {//Industrial ...
            //Aqui eu pego o Custo do PA do Loop ...
            $sql = "SELECT `id_produto_acabado_custo`, `id_produto_insumo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '0' LIMIT 1 ";
            $campos_pa_custo    = bancos::sql($sql);
            $etapa2             = round(custos::etapa2($campos[$i]['id_produto_acabado'], $campos[$i]['operacao_custo']), 2);
            
            //Busco um PA na 7ª Etapa desse Custo do PA do Loop ...
            if($etapa2 == 0) {
                $sql = "SELECT pp.`id_produto_acabado`, pa.`operacao_custo` 
                        FROM `pacs_vs_pas` pp 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                        WHERE `id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
                $campos_etapa7 = bancos::sql($sql);
                if(count($campos_etapa7) == 1) {//Encontrou um PA na 7ª Etapa ...
                    //Desse PA que foi encontrado na 7ª Etapa eu busco o seu Custo ...
                    $sql = "SELECT `id_produto_acabado_custo`, `id_produto_insumo` 
                            FROM `produtos_acabados_custos` 
                            WHERE `id_produto_acabado` = '".$campos_etapa7[0]['id_produto_acabado']."' 
                            AND `operacao_custo` = '".$campos_etapa7[0]['operacao_custo']."' LIMIT 1 ";
                    $campos_pa_custo = bancos::sql($sql);
                    //Busco o Total desse Custo apenas na 2ª Etapa ...
                    $etapa2 = round(custos::etapa2($campos_etapa7[0]['id_produto_acabado'], $campos_etapa7[0]['operacao_custo']), 2);
                    if($etapa2 == 0) {
                        $etapa2 = round($preco_fat_nac_nac_min_rs * rand(7100, 8500) / 10000, 2);
                    }
                }
            }
        }else {//Revenda ...
            $etapa2 = round($preco_fat_nac_nac_min_rs * rand(7100, 8500) / 10000, 2);
        }
        $etapa2 = round($etapa2, 4);
        $ci     = round($etapa2 / $preco_fat_nac_nac_min_rs * 100, 2);

        while($ci < 70) {
            $preco_fat_nac_nac_min_rs*= 0.95;
            $preco_fat_nac_nac_min_rs   = round($preco_fat_nac_nac_min_rs, 2);
            $ci                         = round($etapa2 / $preco_fat_nac_nac_min_rs * 100, 2);
        }
        $layout_produto_acabado.= "5020|".$campos[$i]['discriminacao']."|".str_replace('.', '', $campos[$i]['classific_fiscal'])."|".$campos[$i]['referencia']."|".$campos[$i]['codigo_barra']."|".$campos[$i]['unidade']."|".number_format($preco_fat_nac_nac_min_rs, 2, ',', '')."|".number_format($etapa2, 2, ',', '')."|".number_format($ci, 2, ',', '').$quebrar_linha;
        $total_registros++;//Essa variável só irá contabilizar os PA(s) cujo "Preço Fat. Nac. Min." > '0' ...
    }
}

$layout_produto_acabado.= "5990|".(2 + $total_registros).$quebrar_linha;//Equivale a 1ª Linha do Bloco 2 + Ultima Linha do bloco 2 ...
$layout_produto_acabado.= "9001".$quebrar_linha;
$layout_produto_acabado.= "9900|0000|1".$quebrar_linha;
$layout_produto_acabado.= "9900|0010|1".$quebrar_linha;
$layout_produto_acabado.= "9900|5020|".$total_registros.$quebrar_linha;
$layout_produto_acabado.= "9990|5".$quebrar_linha;
$layout_produto_acabado.= "9999|".(5 + $total_registros + 7).$quebrar_linha;//4 primeiras linha do bloco 1 + 1ª Linha do bloco 2 + Ultima Linha do bloco 2 + 6 linhas do bloco 3 ...

//Gerando o Arquivo p/ Download ...
$filename = 'Listagem FCI.txt';
$file = fopen($filename, 'w+');
fwrite($file, $layout_produto_acabado);
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
print $layout_produto_acabado;
unlink($filename);
?>