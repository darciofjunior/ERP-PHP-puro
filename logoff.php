<?
require('lib/segurancas.php');
session_start('funcionarios');
if($_SESSION['id_login'] <> 0) {//Se ainda n�o expirou a Sess�o e o usu�rio clicou no bot�o Sair do ERP por exemplo ...
	/********************************************Logs********************************************/
	if(!class_exists('logs')) require 'lib/logs.php';//CASO EXISTA EU DESVIO A CLASSE
	logs::gerenciar_logs();//Cria toda a Estrutura de Banco de Dados de Logs ...
	$vetor_meses 	= array('', '1_janeiro', '2_fevereiro', '3_mar�o', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
	$database 		= 'logs_'.date('Y');
	$mes_current 	= $vetor_meses[(int)date('m')];	
	$sql			= "INSERT INTO $database.`logs_logins_logout_$mes_current` (`id_log_login`, `id_login`, `id_modulo`, `ip`, `status`, `data`) VALUES (NULL, '$_SESSION[id_login]', '$_SESSION[id_modulo]', '$_SESSION[ip]', '0', '".date('Y-m-d H:i:s')."') ";
	bancos::sql($sql);
	/********************************************************************************************/
	session_unset('funcionarios');//Exclui todas as vari�veis armazenadas da Sess�o ...
	session_destroy();//Destr�i a Sess�o j� vazia ...
}
?>
<Script Language = 'JavaScript'>
	window.location = 'default.php'
</Script>