<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../');
require('../../classes/nf_carta_correcao/incluir.php');
?>