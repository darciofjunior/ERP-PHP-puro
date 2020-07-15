<?
require('../../../../lib/segurancas.php');
require('../../../../lib/ajax.php');

//Listo o endereço do Cliente que foi selecionado em outra combo ...
$sql = "SELECT DISTINCT(c.id_cliente), CONCAT(c.endereco, ', ', c.num_complemento, ' - ', c.bairro, ' - ', c.cidade, ' (', ufs.sigla, ')', ' - Contato: ', cc.nome, ' (', c.ddd_com, ') ', c.telcom) AS rotulo 
        FROM `clientes` c 
        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente` = c.`id_cliente` AND cc.`ativo` = '1' 
        LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
        WHERE c.`id_cliente` = '$_POST[id_cliente]' 
        AND c.ativo = '1' ORDER BY cc.id_cliente_contato DESC LIMIT 1 ";
$campos = bancos::sql($sql);
$combo 	= ajax::combo($campos, 'id_cliente', 'rotulo');
?>