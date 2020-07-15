<?
require('../../lib/segurancas.php');
require('../../lib/data.php');

if(empty($indice)) $indice = 0;

/*Busco todos os Clientes ativos que estão cadastrados no ERP, ignoro os "Clientes" que são do Tipo 
Internacional, Fornecedor, Usina de Cana, Tele MKT, Tele MKT Inativo, Governamental ...*/
$sql = "SELECT COUNT(`id_cliente`) AS total_registro 
        FROM `clientes` 
        WHERE `id_cliente_tipo` NOT IN (7, 8, 9, 11, 12, 15) 
        AND `ativo` = '1' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

echo $total_registro.'/'.$indice;

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT `id_cliente` 
        FROM `clientes` 
        WHERE `id_cliente_tipo` NOT IN (7, 8, 9, 11, 12, 15) 
        AND `ativo` = '1' ";
$campos = bancos::sql($sql, $indice, 1);

//Busco todas as Divisões existentes p/ o determinado Cliente ...
$sql = "SELECT cr.`id_empresa_divisao`, ed.`razaosocial`, r.`id_representante`, r.`nome_fantasia` 
        FROM `clientes_vs_representantes` cr 
        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = cr.`id_empresa_divisao` 
        WHERE cr.`id_cliente` = '".$campos[0]['id_cliente']."' ";
$campos_empresa_divisao = bancos::sql($sql);
$linhas_empresa_divisao = count($campos_empresa_divisao);
for($i = 0; $i < $linhas_empresa_divisao; $i++) {
    //Busco a última Data de Compra do Cliente na respectiva Empresa Divisão ...
    $sql = "SELECT pv.`data_emissao` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
            WHERE pv.`id_cliente` = '".$campos[0]['id_cliente']."' ORDER BY pv.`data_emissao` DESC LIMIT 1 ";
    $campos_ultimo_pedido_venda = bancos::sql($sql);
    
    $vetor_data = data::diferenca_data($campos_ultimo_pedido_venda[0]['data_emissao'], date('Y-m-d'));
    $qtde_dias  = $vetor_data[0];
       
    if($qtde_dias > 730) {//Se a última compra do Cliente na respectiva Empresa Divisão foi superior há 2 anos, repasso este representante p/ outro ...
        if($campos_empresa_divisao[$i]['id_empresa_divisao'] == 6) {//Nessa Divisão de Heinz-Pinos, o representante será redirecionado p/ a Solange ...
            $observacao = "*** Representante(s) alterado(s) de ".$campos_empresa_divisao[$i]['nome_fantasia']." p/ o Representante \"SOLANGE\" na Empresa Divisão ".$campos_empresa_divisao[$i]['razaosocial']." em ".date('d/m/Y').' às '.date('H:i:s').'.';
        }else {//Do contrário DIRETO 2 ...
            $observacao = "*** Representante(s) alterado(s) de ".$campos_empresa_divisao[$i]['nome_fantasia']." p/ o Representante \"DIRETO 2\" na Empresa Divisão ".$campos_empresa_divisao[$i]['razaosocial']." em ".date('d/m/Y').' às '.date('H:i:s').'.';
        }
        
        //Inserindo um novo Follow-Up p / o Cliente ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `exibir_no_pdf`, `data_sys`) VALUES (NULL, '".$campos[0]['id_cliente']."', '136', '".$campos[0]['id_cliente']."', '11', '".$observacao."', 'N', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);

        //Atribuindo o novo Representante p/ o Cliente ...
        if($campos_empresa_divisao[$i]['id_empresa_divisao'] == 6) {//Nessa Divisão de Heinz-Pinos, o representante será redirecionado p/ a Solange ...
            $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '36', `desconto_cliente` = '0.00' WHERE `id_cliente` = '".$campos[0]['id_cliente']."' AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' LIMIT 1 ";
        }else {//Do contrário DIRETO 2 ...
            $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '120', `desconto_cliente` = '0.00' WHERE `id_cliente` = '".$campos[0]['id_cliente']."' AND `id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
}
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'passar_clientes_para_direto2.php?indice=<?=++$indice;?>'
</Script>