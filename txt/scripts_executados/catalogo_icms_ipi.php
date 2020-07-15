<?
require('../../lib/segurancas.php');

$sql = "SELECT cf.classific_fiscal, ufs.sigla, icms.iva 
        FROM `classific_fiscais` cf 
        INNER JOIN `icms` ON icms.id_classific_fiscal = cf.id_classific_fiscal 
        INNER JOIN `ufs` ON ufs.id_uf = icms.id_uf 
        AND icms.iva > '0' 
        WHERE cf.ativo = '1' 
        ORDER BY cf.classific_fiscal, ufs.sigla ";
?>