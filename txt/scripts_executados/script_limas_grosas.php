<?
require('../../lib/segurancas.php');

/*
//4, 6, 8, 10, 12 ...
//85, 76, 99, 68, 34 ...
$sql = "SELECT id_produto_acabado, discriminacao 
		FROM `produtos_acabados` 
		WHERE `discriminacao` LIKE '%LIMA% 4%SKIN%' 
		AND `operacao_custo` = '0' 
		AND `ativo` = '1' ";*/
		
//4, 6, 8, 10, 12 ...
//0, 8, 9, 9, 5 ...
$sql = "SELECT id_produto_acabado, discriminacao 
        FROM `produtos_acabados` 
        WHERE `discriminacao` LIKE '%GROSA% 4%SKIN%' 
        AND `operacao_custo` = '0' 
        AND `ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT id_pa_pi_emb 
            FROM `pas_vs_pis_embs` 
            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    $campos_pas_pis = bancos::sql($sql);

    if(strpos($campos[$i]['discriminacao'], 'CABO') !== false) {//COM CABO ...
        $sql = "UPDATE `pas_vs_pis_embs` SET `pecas_por_emb` = '6' WHERE `id_pa_pi_emb` = '".$campos_pas_pis[0]['id_pa_pi_emb']."' LIMIT 1 ";
    }else {//SEM CABO ...
        $sql = "UPDATE `pas_vs_pis_embs` SET `pecas_por_emb` = '12' WHERE `id_pa_pi_emb` = '".$campos_pas_pis[0]['id_pa_pi_emb']."' LIMIT 1 ";
    }
    bancos::sql($sql);
}
echo 'TOTAL DE REGISTROS: '.$linhas;
?>