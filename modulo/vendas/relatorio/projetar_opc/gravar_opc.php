<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../../');

/**************************************************Gravação da Projeção OPC**************************************************/
$tipo_opc = ($_POST['opt_tipo_opc'] == 1) ? 'C' : 'NC';

/*Foi comentado esse trecho de código no dia 20/06/2017 porque hoje em dia só o Nishimura é quem faz esse tipo de Serviço, antes o vendedor
podia ficar clicando milhares de vezes no botão da qual gerava milhares de OPCS ...

//Primeiramente eu verifico se já foi feita alguma projeção de OPC para o determinado e respectivo Cliente ...
$sql = "SELECT `id_opc` 
        FROM `opcs` 
        WHERE `id_cliente` = '$_POST[id_cliente]' 
        AND `id_funcionario` = '$_SESSION[id_funcionario]' 
        AND `tipo_nota` = '$_POST[opt_tipo_nota]' 
        AND `tipo_opc`  = '$tipo_opc' 
        AND `qtde_anos` = '$_POST[cmb_qtde_anos]' 
        AND `data_sys`, 1, 10) = '".date('Y-m-d')."' LIMIT 1 ";
$campos_projecao_apv = bancos::sql($sql);
if(count($campos_projecao_apv) == 0) {//Se essa Projeção APV ainda não existe, eu gero uma então ...*/
    $sql    = "INSERT INTO `opcs` (`id_opc`, `id_cliente`, `id_funcionario`, `tipo_nota`, `tipo_opc`, `qtde_anos`, `prazo_a`, `prazo_b`, `prazo_c`, `prazo_d`, `data_sys`) VALUES (NULL, '$_POST[id_cliente]', '$_SESSION[id_funcionario]', '$_POST[opt_tipo_nota]', '$tipo_opc', '$_POST[cmb_qtde_anos]', '$_POST[txt_prazo_a]', '$_POST[txt_prazo_b]', '$_POST[txt_prazo_c]', '$_POST[txt_prazo_d]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $id_opc = bancos::id_registro();
/*}else {//Se já existir só trago a mesma para fazer novas atualizações ...
    //Aqui eu atualizo o horário da Projeção ...
    $sql    = "UPDATE `opcs` SET `prazo_a` = '$_POST[txt_prazo_a]', `prazo_b` = '$_POST[txt_prazo_b]', `prazo_c` = '$_POST[txt_prazo_c]', `prazo_d` = '$_POST[txt_prazo_d]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_opc` = '".$campos_projecao_apv[0]['id_opc']."' LIMIT 1 ";
    bancos::sql($sql);
    $id_opc = $campos_projecao_apv[0]['id_opc'];
}*/
/****************************************************************************************************************************/
foreach($_POST['hdd_produto_acabado'] as $id_produto_acabado) $string_produto_acabados.= "'".$id_produto_acabado."', ";
$string_produto_acabados = substr($string_produto_acabados, 0, strlen($string_produto_acabados) - 2);

//Aqui eu busco todos os PA(s) que foram passados por parâmetro da Tela de Baixo ...
$sql = "SELECT id_produto_acabado, referencia, discriminacao 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` IN ($string_produto_acabados) ORDER BY discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    if($_POST['txt_qtde_proposta'][$i] > 0) {//Aqui eu só apresento os Itens em que a Qtde Proposta seja maior do que Zero ...
        //Faço esse tratamento aqui, porque não trato na tela de baixo pelo JavaScript ...
        $desconto_extra = str_replace('.', '', $_POST['txt_desconto_extra'][$i]);
        $desconto_extra = str_replace(',', '.', $desconto_extra);
        
        $preco_unitario_proposto = str_replace('.', '', $_POST['txt_preco_unitario_proposto'][$i]);
        $preco_unitario_proposto = str_replace(',', '.', $preco_unitario_proposto);
        
        $margem_lucro_proposta = str_replace('.', '', $_POST['txt_margem_lucro_proposta'][$i]);
        $margem_lucro_proposta = str_replace(',', '.', $margem_lucro_proposta);
/**************************************************Gravação do Item Projeção OPC**************************************************/
        //Aqui eu verifico se já existe esse Item na última Projeção realizada pelo Usuário do determinado cliente ...
        $sql = "SELECT id_opc_item 
                FROM `opcs_itens` 
                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                AND `id_opc` = '$id_opc' LIMIT 1 ";
        $campos_projecao_apv_item = bancos::sql($sql);
        if(count($campos_projecao_apv_item) == 0) {//Aqui eu gravo os Itens da Projeção OPC do Cliente ...
            $sql = "INSERT INTO `opcs_itens` (`id_opc_item`, `id_opc`, `id_produto_acabado`, `qtde_proposta`, `desconto_extra`, `margem_lucro`, `preco_proposto`) VALUES (NULL, '$id_opc', '".$campos[$i]['id_produto_acabado']."', '".$_POST['txt_qtde_proposta'][$i]."', '$desconto_extra', '$margem_lucro_proposta', '$preco_unitario_proposto') ";
            bancos::sql($sql);
        }else {//Se já existir esse Item nessa OPC, só atualizo ...
            $sql = "UPDATE `opcs_itens` SET `qtde_proposta` = '".$_POST['txt_qtde_proposta'][$i]."', `desconto_extra` = '$desconto_extra', `margem_lucro` = '$margem_lucro_proposta', `preco_proposto` = '$preco_unitario_proposto' WHERE `id_opc_item` = '".$campos_projecao_apv_item[0]['id_opc_item']."' LIMIT 1 ";
            bancos::sql($sql);
        }
/*********************************************************************************************************************************/
        $total_geral_proposto+= $_POST['txt_qtde_proposta'][$i] * $preco_unitario_proposto;
    }
}

//Aqui eu gero um Follow-Up para o Cliente ...
$sql = "SELECT `id_cliente_contato` 
        FROM `clientes_contatos` 
        WHERE `id_cliente` = '$_POST[id_cliente]' 
        AND `ativo` = '1' LIMIT 1 ";
$campos_contato 	= bancos::sql($sql);

//Se for uma OPC de Curva ABC, será acrescida mais uma observação no Follow-UP do Cliente ...
if($_POST['opt_tipo_opc'] == 2) $complemento = '- (Curva ABC) ';

//Registrando Follow-UP(s) ...
$id_representante = genericas::buscar_id_representante($campos_contato[0]['id_cliente_contato']);

$sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_cliente]', '".$campos_contato[0]['id_cliente_contato']."', '$id_representante', '$_SESSION[id_funcionario]', '$id_opc', '14', '(OPC Projetado) ".$complemento." próximo passo gerar o Pedido - Valor da Projeção = R$ ".number_format($total_geral_proposto, 2, ',', '.')."', '".date('Y-m-d H:i:s')."') ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    window.location = 'imprimir_opc.php?id_opc=<?=$id_opc;?>'
</Script>