<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é requerida dentro da Custos ...
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/estoque_pa/estoque_pa.php', '../../../../');

/******************************************************************************/
/*Função Nativa do PHP, Retorna diferença de Data e Horas, show de bola ...
Exemplo completo de Retorno: 
echo $diff->format('%y ano(s), %m mês(s), %d dia(s), %H hora(s), %i minuto(s) e %s segundo(s)');*/
function tempo_transcorrido($data_atual, $data_futura) {
    $date_time  = new DateTime($data_atual);
    $diff       = $date_time->diff(new DateTime($data_futura));
    return $diff->format('%s');
}
/******************************************************************************/

$data_atual                 = date('Y-m-d H:i:s');
$qtde_registros_processar   = 50;

//Aqui é a Busca da Variável de Vendas
$fator_desc_maximo_venda    = genericas::variavel(19);
if(!isset($registro_atual)) $registro_atual = 0;//Controle feito somente na Primeira vez que carregarmos a Tela ...
?>
<html>
<head>
<title>.:: Atualizar Lista dos PA(s) ESP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body topmargin='150'>
<center>
    <font class='confirmacao'>
        <?
            //Só vou exibir esse trecho de código na Tela, depois que for processado o 1º Lote ...
            if($_GET['registro_atual'] >= $qtde_registros_processar) {
        ?>
        Total já atualizado =>
        <?
            echo ($_GET['registro_atual']).' Registro(s) de um Total de '.$_GET['total_registro'].'.';
        ?>
        <br/><br/>
        <font color='darkblue'>
            Tempo Estimado p/ Término => 
            <?
                $segundos                           = tempo_transcorrido($_GET['data_atual'], $_GET['data_futura']);
                $qtde_vezes_restante_para_processar = (($_GET['total_registro'] - $_GET['registro_atual']) / $qtde_registros_processar);
                $tempo_estimado_termino             = $qtde_vezes_restante_para_processar * $segundos;

                //Divido por 60, p/ transformar em minutos ...
                if($tempo_estimado_termino > 59)    $tempo_estimado_termino/= 60;
                echo number_format($tempo_estimado_termino, 2, ',', '.').' minuto(s)';
            ?>
        </font>
        <?
            }
        ?>
        <br/><br/>
        <font size='6' color='brown'>
            <b>Atualizando Lista dos PA(s) ESP ...</b>
        </font>
    </font>
</center>
</body>
</html>
<?
//A 1ª Query traz PAs que são ESP e a 2ª Query traz PAs que são da família Componentes ...
$sql = "SELECT pa.id_produto_acabado, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        WHERE pa.`ativo` = '1' 
        AND pa.`referencia` = 'ESP' 
        AND pa.`status_custo` = '1' 
        UNION ALL 
        SELECT pa.id_produto_acabado, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        AND gpa.`id_familia` IN (23, 24) 
        WHERE pa.`ativo` = '1' 
        AND pa.`status_custo` = '1' ";
$campos = bancos::sql($sql, $registro_atual, $qtde_registros_processar);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_desc_maximo_venda;
    //Existe esse novo tratamento p/ evitar que dê erro de Divisão por Zero - Dárcio ...
    if($campos[$i]['desc_base_a_nac'] == 100 && $campos[$i]['desc_base_b_nac'] == 100) {
        $preco_bruto_fat_rs = $preco_maximo_custo_fat_rs * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
    }else if($campos[$i]['desc_base_a_nac'] == 100 && $campos[$i]['desc_base_b_nac'] != 100) {
        $preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
    }else if($campos[$i]['desc_base_a_nac'] != 100 && $campos[$i]['desc_base_b_nac'] == 100) {
        $preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
    }else {//Aqui já está sem problemas ...
        $preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / (1 - $campos[$i]['desc_base_a_nac'] / 100) / (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
    }
    $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '$preco_bruto_fat_rs' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    bancos::sql($sql);
}

if($linhas > 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'atualizar_lista_pas_esp.php?data_atual=<?=$data_atual;?>&data_futura=<?=date('Y-m-d H:i:s');?>&total_registro=<?=$_GET['total_registro'];?>&registro_atual=<?=$registro_atual+=$qtde_registros_processar;?>'
    </Script>
<?
}else {
    $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '0.00' WHERE `referencia` = 'ESP' AND `ativo` = '1' AND `status_custo` = '0' ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('O SISTEMA FINALIZOU A OPERAÇÃO !')
        window.close()
    </Script>
<?
}
?>