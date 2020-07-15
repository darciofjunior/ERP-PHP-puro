<?
require('../../../../../lib/segurancas.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/migrar_lista/migrar_lista.php', '../../../../../');

//Atualizo o Preço da Lista p/ a Lista Nova p/ todos os Produtos ...
$sql = "UPDATE `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
	SET pa.`preco_unitario_simulativa` = pa.`preco_unitario`, ged.`desc_a_lista_nova` = ged.`desc_base_a_nac`, ged.`desc_b_lista_nova` = ged.`desc_base_b_nac`, ged.`acrescimo_lista_nova` = ged.`acrescimo_base_nac` 
	WHERE pa.`ativo` = '1' ";
bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    alert('LISTA DE PREÇO ATUAL MIGRADA COM SUCESSO P/ A NOVA LISTA !')
    window.location = 'migrar_lista.php'
</Script>