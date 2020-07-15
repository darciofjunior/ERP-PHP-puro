<?
require('../../lib/segurancas.php');

if(empty($indice)) $indice = 0;

/*Somente os Orçamentos em Aberto onde o Tipo de Nota será Alba ou Tool, Congelados, que são do Tipo Revenda 
e de São Paulo ...*/
$sql = "SELECT count(id_estoque_acabado_erro) total_registro 
        FROM estoques_acabados_erros ";
$campos_total   = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit('FIM !');

//Listagem de todas as Baixas e Estornos ...
$sql = "SELECT * 
        FROM `estoques_acabados_erros` ";
$campos = bancos::sql($sql, $indice, 1);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $id_produto_acabado = $campos[$i]['id_produto_acabado'];
    $id_funcionario     = $campos[$i]['id_funcionario'];
    $id_op              = $campos[$i]['id_op'];
    $qtde               = $campos[$i]['qtde'];
    $qtde_producao      = $campos[$i]['qtde_producao'];
    $justificativa      = $campos[$i]['justificativa'];

    if($campos[$i]['status_tipos'] == 0) {
        $acao = 'M';
    }else if($campos[$i]['status_tipos'] == 1) {
        $acao = 'E';
    }else if($campos[$i]['status_tipos'] == 2) {
        $acao = 'O';
    }
    $status             = $campos[$i]['status'];
    $tipo_manipulacao   = $campos[$i]['tipo_manipulacao'];
    $data_sys           = $campos[$i]['data_sys'];

    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `qtde`, `qtde_producao`, `observacao`, `acao`, `status`, `tipo_manipulacao`, `data_sys`) VALUES (NULL, '$id_produto_acabado', '$id_funcionario', '$qtde', '$qtde_producao', '$justificativa', '$acao', '$status', '$tipo_manipulacao', '$data_sys') ";
    bancos::sql($sql);
    $id_baixa_manipulacao_pa = bancos::id_registro();

    if($id_op > 0) {
        $sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '$id_produto_acabado', '$id_op', '$id_baixa_manipulacao_pa', '$qtde', '$justificativa', '$data_sys', '2') ";
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_baixas_ops_vs_pas.php?indice=<?=++$indice;?>'
</Script>