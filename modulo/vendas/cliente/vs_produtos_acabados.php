<?
require('../../../lib/segurancas.php');

if(empty($_GET['id_orcamento_venda'])) require('../../../lib/menu/menu.php');//Se essa tela foi acessada pela Permissão "Clientes vs Produtos Acabados", então eu exibo o menu ...

segurancas::geral($PHP_SELF, '../../../');
require('../../classes/cliente/vs_produtos_acabados.php');
?>
