<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../lib/intermodular.php');//Essa biblioteca é utilizada dentro da Biblioteca 'faturamentos' ...
session_start('funcionarios');

$sql = "SELECT c.`id_uf`, c.`id_pais`, ufs.`sigla`, nfs.`id_empresa` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);

$numero_nf  = 'NF '.faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');
$nota_sgd   = ($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) ? 'N' : 'S';

$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');

//Verifico se existe cadastro um Fornecedor c/ o nome de "SECRETARIA DA FAZENDA" p/ o Estado do Cliente ...
$sql = "SELECT `id_fornecedor` 
        FROM `fornecedores` 
        WHERE `razaosocial` LIKE '%SEC%FAZ%".$campos[0]['sigla']."' LIMIT 1 ";
$campos_fornecedor = bancos::sql($sql);
if(count($campos_fornecedor) == 0) {//Não existe fornecedor "SECRETARIA DA FAZENDA" cadastrado pelo financeiro ...
    $mensagem = 'NÃO EXISTE FORNECEDOR "SECRETARIA DA FAZENDA - '.$campos[0][sigla].'" CADASTRADO !!!\n\nSOLICITE O DEPTO. FINANCEIRO P/ CADASTRAR !';
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem;?>')
        window.close()
    </Script>
<?
}else {
    $id_fornecedor = $campos_fornecedor[0]['id_fornecedor'];
}
$semana = data::numero_semana(date('d'), date('m'), date('Y'));

//Verifico se já foi gerado "GNRE" ou "DIFAL" para esta NF ...
$sql = "SELECT `id_conta_apagar` 
        FROM `contas_apagares` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos_conta_apagar = bancos::sql($sql);
if(count($campos_conta_apagar) == 0) {//Se não, então gera ...
    //Verifico o tipo de Imposto à ser gerado para a Conta à Pagar ...
    if($calculo_total_impostos['valor_icms_st'] > 0) {
        $id_produto_financeiro  = 183;
        $valor                  = $calculo_total_impostos['valor_icms_st'];
        
        if($calculo_total_impostos['fecp'] > 0) {//Se tiver FECP, além do ST calculado acima eu ainda acrescento esse Imposto ...
            $base_calculo_fecp  = $calculo_total_impostos['base_calculo_icms_st'];
            $valor_fecp         = number_format(round($base_calculo_fecp * ($calculo_total_impostos['fecp'] / 100), 2), 2, '.', '');
            $valor              = round($calculo_total_impostos['valor_icms_st'] + $valor_fecp, 2);
        }
    }else if($calculo_total_impostos['difal'] > 0) {
        $id_produto_financeiro  = 239;
        $valor                  = round($calculo_total_impostos['difal'], 2);
    }
    //Gerando a Conta à Pagar ...
    $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_nf`, `id_fornecedor`, `id_tipo_moeda`, `id_empresa`, `id_tipo_pagamento_recebimento`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `valor`) 
            VALUES(NULL, '$_SESSION[id_funcionario]', '$_GET[id_nf]', '$id_fornecedor', '1', '".$campos[0][id_empresa]."', '12', '33', '$id_produto_financeiro', '100', '$numero_nf', '$semana', '".date("Y-m-d")."', '".date("Y-m-d")."', '".date("Y-m-d")."', '$valor') ";
    bancos::sql($sql);
    $id_conta_apagar    = bancos::id_registro();
    
    $observacao = 'VALOR DO ICMS ST = R$ '.number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');

    if($calculo_total_impostos['fecp'] > 0) {
        $base_calculo_fecp  = $calculo_total_impostos['base_calculo_icms_st'];
        $valor_fecp         = number_format(round($base_calculo_fecp * ($calculo_total_impostos['fecp'] / 100), 2), 2, '.', '');
        $total_icms_st      = round($calculo_total_impostos['valor_icms_st'] + $valor_fecp, 2);
                        
        $observacao.= ' e FECP = R$ '.number_format($valor_fecp, 2, ',', '.');
        $observacao.= ' => TOTAL ICMS ST = R$ '.number_format($total_icms_st, 2, ',', '.');
    }

    //Gerando Follow-Up da Conta à Pagar ...
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_fornecedor`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `exibir_no_pdf`, `data_sys`) 
            VALUES (NULL, NULL, '$id_fornecedor', NULL, NULL, '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '$observacao', 'S', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('CONTA À PAGAR GERADA COM SUCESSO !!!')
        window.close()
    </Script>
<?
}else {//Se sim, barra o usuário ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA CONTA À PAGAR JÁ FOI GERADA ANTERIORMENTE !!!')
        window.close()
    </Script>
<?
}
?>