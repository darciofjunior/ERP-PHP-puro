<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');

/*Roberto imagina que n�o vai ser mais necess�rio separar esse Produto para mais ningu�m, ent�o essa 
rotina desmarca este PA como sendo "PA com Nova Entrada em Estoque" ...*/
$sql = "UPDATE `produtos_acabados` SET `status_material_novo` = '0' WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
bancos::sql($sql);
//Depois que atualiza o novo Campo que foi criado na Tabela de Produto Acabado, eu fecho o Pop-UP ...
?>
<Script Language = 'JavaScript'>
    window.close()
</Script>