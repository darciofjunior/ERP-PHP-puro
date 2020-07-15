<?
require('../../../lib/ssp.class.php');

$sql = "SELECT g.`referencia`, g.`nome`, ccp.`conta_caixa`, 
        CASE g.`tipo_custo` 
            WHEN 'V' THEN 'VARIÁVEL' 
            WHEN 'P' THEN 'PROCESSO' 
            ELSE 'FIXO' 
        END AS tipo_custo, 
        g.`observacao` 
        FROM `grupos` g 
        INNER JOIN `contas_caixas_pagares` ccp ON ccp.`id_conta_caixa_pagar` = g.`id_conta_caixa_pagar` AND g.`ativo` = '1' 
        WHERE g.`nome` LIKE '%$_POST[txt_consultar]%' ";
echo json_encode(
    SSP::simple($_POST, $sql)
);