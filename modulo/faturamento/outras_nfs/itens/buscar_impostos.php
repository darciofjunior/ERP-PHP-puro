<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');
/***************Talvez eu utilize os resultados dessa busca mais abaixo***************/
//Busca dos Impostos de acordo com a Classificação Fiscal selecionada e do Estado de SP ...
$sql = "SELECT cf.ipi, cf.imposto_importacao, i.icms, i.reducao 
        FROM `classific_fiscais` cf 
        LEFT JOIN `icms` i ON i.id_classific_fiscal = cf.id_classific_fiscal AND i.`id_uf` = '1' 
        WHERE cf.`id_classific_fiscal` = '$_GET[cmb_classific_fiscal]' LIMIT 1 ";
$campos_class_fiscal = bancos::sql($sql);

/*************************************************************************************/
//Busca dos Impostos na Tabela de CFOP de acordo com a CFOP passado por parâmetro ...
$sql = "SELECT ipi, icms 
        FROM `cfops` 
        WHERE `id_cfop` = '".$_GET['id_cfop']."' LIMIT 1 ";
$campos_cfop = bancos::sql($sql);

/************************Tratamento com o IPI************************/
if($campos_cfop[0]['ipi'] == 1) {//Tributa - Busca da Classificação Fiscal ...
    $ipi                = $campos_class_fiscal[0]['ipi'];
    $disabled_ipi       = 'disabled';
    $textdisabled_ipi   = 'textdisabled';
}else if($campos_cfop[0]['ipi'] == 2) {//Isento ...
    $ipi                = 0;
    $disabled_ipi       = 'disabled';
    $textdisabled_ipi   = 'textdisabled';
}else if($campos_cfop[0]['ipi'] == 3) {//Digitado Manualmente ...
    $ipi                = '';
    $disabled_ipi       = '';
    $textdisabled_ipi   = 'caixadetexto';
}

/************************Tratamento com o ICMS************************/
if($campos_cfop[0]['icms'] == 1) {//Tributa - Busca da Classificação Fiscal ...
    $icms               = $campos_class_fiscal[0]['icms'];
    $reducao            = $campos_class_fiscal[0]['reducao'];
    $disabled           = 'disabled';
    $textdisabled       = 'textdisabled';
}else if($campos_cfop[0]['icms'] == 2) {//Isento ...
    $icms               = 0;
    $reducao            = 0;
    $disabled           = 'disabled';
    $textdisabled       = 'textdisabled';
}else if($campos_cfop[0]['icms'] == 3) {//Digitado Manualmente ...
    $icms               = '';
    $reducao            = '';
    $disabled           = '';
    $textdisabled       = 'caixadetexto';
}

//Por enquanto estou deixando habilitado, porque está dando muito problema - Dárcio - 07/05/2010 ...
$disabled_ipi           = '';
$textdisabled_ipi       = 'caixadetexto';
$disabled               = '';
$textdisabled           = 'caixadetexto';

//Imposto de Importação ...
$imposto_importacao     = $campos_class_fiscal[0]['imposto_importacao'];
?>
<Script Language = 'JavaScript'>
    top.document.form.txt_ipi.value                 = '<?=number_format($ipi, 2, ',', '.');?>'
    top.document.form.txt_icms.value                = '<?=number_format($icms, 2, ',', '.');?>'
    top.document.form.txt_reducao.value             = '<?=number_format($reducao, 2, ',', '.');?>'
    top.document.form.txt_imposto_importacao.value  = '<?=number_format($imposto_importacao, 2, ',', '.');?>'
//Habilitação dos objetos ...
    top.document.form.txt_ipi.disabled              = '<?=$disabled_ipi;?>'
    top.document.form.txt_icms.disabled             = '<?=$disabled;?>'
    top.document.form.txt_reducao.disabled          = '<?=$disabled;?>'
//Layout dos objetos ...
    top.document.form.txt_ipi.className             = '<?=$textdisabled_ipi;?>'
    top.document.form.txt_icms.className            = '<?=$textdisabled;?>'
    top.document.form.txt_reducao.className         = '<?=$textdisabled;?>'
</Script>