<?
require('../../../lib/segurancas.php');

if(empty($_GET['id_orcamento_venda'])) require('../../../lib/menu/menu.php');//Se essa tela foi acessada pela Permiss�o "Clientes vs Produtos Acabados", ent�o eu exibo o menu ...

segurancas::geral($PHP_SELF, '../../../');
require('../../classes/cliente/vs_produtos_acabados.php');
?>
