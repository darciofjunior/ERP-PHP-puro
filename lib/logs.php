<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class logs extends bancos {
	function gerenciar_logs() {
		$database 		= 'logs_'.date('Y');
		//Crio o Banco de Dados de Logs caso esse ainda não exista ...
		$sql 				= "CREATE DATABASE IF NOT EXISTS `$database` ";
		$campos_registros 	= bancos::getDb()->query($sql);	
		if($campos_registros->rowCount() == 1) {//Se foi possível criar o BD pq realmente não existia, então crio as tabelas abaixo ...
			$vetor_meses	= array('1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
			$linhas 		= count($vetor_meses);
			//Crio as Tabelas referente ao Ano Corrente ...
			for($i = 0; $i < $linhas; $i++) {//Cria a tabela de logs ...
				$tabela = 'logs_manipulacao_'.$vetor_meses[$i];
				$sql = "CREATE TABLE IF NOT EXISTS $database.`$tabela` (
                                            `id_log` bigint(20) NOT NULL AUTO_INCREMENT, 
                                            `id_login` bigint(20) NOT NULL DEFAULT '0', 
                                            `sql` text, 
                                            `comando` tinyint(3) NOT NULL DEFAULT '0', 
                                            `ip` varchar(20) NOT NULL DEFAULT '', 
                                            `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`id_log`)) 
                                            ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
				bancos::getDb()->query($sql);
			}
			for($i = 0; $i < $linhas; $i++) {//Cria a tabela de logs login ...
				$tabela = 'logs_logins_logout_'.$vetor_meses[$i];
				$sql = "CREATE TABLE IF NOT EXISTS $database.`$tabela` (
					  `id_log_login` bigint(20) NOT NULL AUTO_INCREMENT, 
					  `id_login` bigint(20) NOT NULL DEFAULT '0', 
					  `id_modulo` bigint(11) NOT NULL DEFAULT '0', 
					  `ip` varchar(15) DEFAULT NULL, 
					  `status` int(1) NOT NULL DEFAULT '0', 
					  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
					  PRIMARY KEY (`id_log_login`), 
					  KEY `id_login` (`id_login`), 
					  KEY `id_modulo` (`id_modulo`)) 
					  ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
				bancos::getDb()->query($sql);
			}
			for($i = 0; $i < $linhas; $i++) {//Cria a tabela de logs acesso ...
				$tabela = 'logs_acessos_telas_'.$vetor_meses[$i];
				$sql = "CREATE TABLE IF NOT EXISTS $database.`$tabela` (
						`id_log_acesso` bigint(20) NOT NULL AUTO_INCREMENT, 
						`id_funcionario` bigint(20) NOT NULL, 
						`identificacao` bigint(20) NOT NULL, 
						`origem` tinyint(4) NOT NULL, 
						`url` varchar(255) NOT NULL, 
						`ip` char(20) NOT NULL, 
						`data_ocorrencia` datetime NOT NULL, 
						PRIMARY KEY (`id_log_acesso`)) 
						ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
				bancos::getDb()->query($sql);
			}
		}
	}
}
?>