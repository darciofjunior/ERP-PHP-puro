<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../');

/********************************************************************************************************/
/********************************************Links para Custo********************************************/
/********************************************************************************************************/
$texto.= "Custo: <a href='http://192.168.1.253/erp/albafer/modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=".$_GET['id_produto_acabado']."&tela=2&pop_up=1'>Interno</a> - ";

/**Busca do IP Externo que está cadastrado em alguma Empresa aqui do Sistema ...**/
$sql = "SELECT ip_externo 
        FROM `empresas` 
        WHERE `ip_externo` <> '' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);
/*Se encontrar um IP Externo cadastrado, o conteúdo do e-mail apontará p/ esse IP "que é a preferência", 
do contrário o IP será da onde o usuário está acessando o ERP $_SERVER['HTTP_HOST'] ...*/
$ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];

$texto.= "<a href='http://".$ip_externo."/erp/albafer/modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=".$_GET['id_produto_acabado']."&tela=2&pop_up=1'>Externo</a><br/><br/>";
/********************************************************************************************************/

if($_GET['observacao_depto_compras'] != null) $texto.= '<font color="red"><b>OBSERVAÇÃO DO DEPTO. TÉCNICO: </b><br/>'.$_GET['observacao_depto_compras'].'</font><br/><br/>';

$texto.= '<b>A/C DEPTO. COMPRAS</b><br/>';
$texto.= $_GET['compra_de_pis'];

/*Busco o e-mail do funcionário que está logado p/ saber no Remetente quem solicitou 
a requisição que chegará via e-mail p/ Compras e Depto. Técnico ...*/
$sql = "SELECT `email_externo` 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
$campos = bancos::sql($sql);
comunicacao::email($campos[0]['email_externo'], 'gcusto@grupoalbafer.com.br', '', 'Avaliar Necessidade de Compra PI / Produção PA Componente - '.$_GET['qtde_lote'].' * '.intermodular::pa_discriminacao($_GET['id_produto_acabado'], 0, 0, 0, 0, 1), $texto);
?>
<Script Language = 'JavaScript'>
    alert('E-MAIL ENVIADO COM SUCESSO !')
    window.close()
</Script>