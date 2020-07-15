<?
require('../../lib/segurancas.php');

$sql = "SELECT id_conta_apagar, numero_conta 
        FROM `contas_apagares` 
        WHERE `id_pedido` = '0'	
        AND `id_antecipacao` = '0'	
        AND `id_nfe` = '0' 
        AND `id_representante` = '0' 
        AND `id_vale_data` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $sql = "SELECT id_conta_apagar_vs_pff 
            FROM `contas_apagares_vs_pffs` 
            WHERE `id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' LIMIT 1 ";
    $campos_pffs = bancos::sql($sql);
    if(count($campos_pffs) == 0) {
        $sql = "INSERT INTO `contas_apagares_vs_pffs` (`id_conta_apagar_vs_pff`, `id_conta_apagar`, `ativo`) VALUES (NULL, '".$campos[$i]['id_conta_apagar']."', '1') ";
        bancos::sql($sql);
    }
}
?>