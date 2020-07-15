<?
require('../../../lib/segurancas.php');
require('../../../lib/ajax.php');
session_start('funcionarios');

if(!empty($_POST['id_cliente'])) {//Aqui eu listo todos os Representantes de acordo com o id_cliente passado por parâmetro ...
    $sql = "SELECT DISTINCT(r.id_representante) 
            FROM `representantes` r 
            INNER JOIN `clientes_vs_representantes` cr ON cr.id_representante = r.id_representante AND cr.id_cliente = '$_POST[id_cliente]' 
            WHERE r.`ativo` = '1' ORDER BY r.nome_fantasia ";
}else {//Aqui eu listo todos os Representantes, porque não passei nenhum id_cliente por parâmetro ...
    $sql = "SELECT DISTINCT(r.id_representante) 
            FROM `representantes` r 
            WHERE r.`ativo` = '1' ORDER BY r.nome_fantasia ";
}
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Aqui eu confirmo se o Representante que foi retornado do Cliente é um funcionário ...
    $sql = "SELECT id_representante 
            FROM `representantes_vs_funcionarios` 
            WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    //Se não for funcionário e for diferente de Direto e PME, busco o Supervisor desse que é funcionário ...
    if(count($campos_funcionario) == 0 && ($campos[$i]['id_representante'] != 1 && $campos[$i]['id_representante'] != 71)) {
        $sql = "SELECT id_representante_supervisor AS id_representante 
                FROM `representantes_vs_supervisores` 
                WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        if(count($campos_representante) == 1) {//Encontrou um Supervisor ...
            $id_representantes.= "'".$campos_representante[0]['id_representante']."', ";
        }else {//Não encontrou Supervisor algum ...
            $id_representantes.= "'".$campos[$i]['id_representante']."', ";
        }
    }else {
        $id_representantes.= "'".$campos[$i]['id_representante']."', ";
    }
}
$id_representantes = substr($id_representantes, 0, strlen($id_representantes) - 2);

$sql = "SELECT id_representante, nome_fantasia 
        FROM `representantes` 
        WHERE `id_representante` IN ($id_representantes) ORDER BY nome_fantasia ";
$campos_rep     = bancos::sql($sql);
$auto_complete 	= ajax::combo($campos_rep, 'id_representante', 'nome_fantasia');
?>