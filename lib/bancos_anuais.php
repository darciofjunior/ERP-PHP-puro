<?
//SHOW TABLE STATUS FROM `erp_albafer2011` WHERE COMMENT = 'tabela_por_ano' - Comando interessante para estudar ...
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class bancos_anuais extends bancos {
	function __construct() {
                $year_current = date('Y');
		$new_database = 'erp_albafer'.$year_current;
		//Aqui eu verifico se esse Novo Banco de Dados já existe ...
		$sql = "SELECT SCHEMA_NAME 
				FROM INFORMATION_SCHEMA.SCHEMATA 
				WHERE SCHEMA_NAME = '$new_database' ";
		$schema = bancos::getDb()->query($sql);
		$linhas = $schema->rowCount();
		if($linhas == 0) {//Significa que esse BD não existe ...			
			$sql 	= "CREATE DATABASE IF NOT EXISTS `$new_database` ";//Cria a Nova Base de Dados ...
			bancos::getDb()->query($sql);
			$sql 	= "SHOW TABLE STATUS FROM `erp_albafer` ";//Lista todas as Tabelas da BD passada por parâmetro ...
			$tables	= bancos::getDb()->query($sql);
			while($table_current = $tables->fetch(PDO::FETCH_OBJ)) {
				//Se existir comentário ...
				if($table_current->Comment == 'tabela_por_ano') {
					$COMMENT = " COMMENT='tabela_por_ano'";
					//Limpo essas variáveis p/ não herdar valor do Loop Anterior ...
					$campos_estrutura = ''; $campos_registros = ''; $key = '';
					$sql_fields 	= "SHOW FIELDS FROM ".$table_current->Name;//Lista todos os Campos e Atributos desses da respectiva Tabela do Loop ...
					$fields 		= bancos::getDb()->query($sql_fields);
					while($field_current = $fields->fetch(PDO::FETCH_OBJ)) {
	        			if($field_current->Key == 'PRI') {
	        				$key.= ' PRIMARY KEY (`'.$field_current->Field.'`), ';      				
	        			}else if($field_current->Key == 'MUL') {
	        				$key.= 'KEY `'.$field_current->Field.'` (`'.$field_current->Field.'`), ';
	        			}else {
	        				$key.= '';
	        			}
						$default 			= ($field_current->Default != '') ? "DEFAULT '".$field_current->Default."'" : '';
						$null 				= ($field_current->Null == 'NO') ? 'NOT NULL' : 'NULL';
	        			$extra 				= ($field_current->Extra == 'auto_increment') ? 'AUTO_INCREMENT' : '';
	        			$campos_estrutura.= " `".$field_current->Field."` ".$field_current->Type." $null $default $extra, ";
	        			$campos_registros.= " `".$field_current->Field."`, ";
	    			}
	    			$key = substr($key, 0, strlen($key) - 2);
    				//Aqui eu crio um vetor para armazenar as futuras querys das tabelas que terão de ser criadas ...
					$sql_tables[] = "CREATE TABLE IF NOT EXISTS `".$table_current->Name."` (
					  				$campos_estrutura 
					  				$key )  ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 $COMMENT AUTO_INCREMENT=1; ";
					/*
				 	 * Aqui eu comentei porque era uma outra analogia de copiar os registros na Nova Base de Dados que foi criada ...
					 * 
					 * if(!empty($COMMENT)) {//Se o Comentário = 'tabela_por_ano' ...
						//Lista todos os Registros da Respectiva Tabela ...
						$sql_registers 	= "SELECT * FROM ".$table_current->Name." ";
						$registers 		= mysql_query($sql_registers);
						$numero_colunas	= mysql_num_fields($registers);
						
						$insert_registers = '';//Limpo a variável para não herdar valores do Loop Anterior ...
						for($i = 0; $i < mysql_num_rows($registers); $i++) {
							$insert_registers.= '(';
							for($j = 0; $j < $numero_colunas; $j++) $insert_registers.= mysql_result($registers, $i, $j).', ';
							$insert_registers = substr($insert_registers, 0, strlen($insert_registers) - 2);
							$insert_registers.= '), ';
						}
						$insert_registers = substr($insert_registers, 0, strlen($insert_registers) - 2);
						//Aqui eu Crio um Script para Inserir nas Tabelas os registros criados anteriormente ...
						$campos_registros = substr($campos_registros, 0, strlen($campos_registros) - 2);
						$sql_registros[] = "INSERT INTO ($campos_registros) VALUES ($insert_registers) ";
					}*/
				 }
			}
			//Acabo de se conectar no BD que foi acabado de criar ...
			$dsn 		= 'mysql:dbname='.$new_database.';host=localhost';
			$user 		= 'root';
			$password 	= 'w1l50n';
			
			$db = new PDO($dsn, $user, $password);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			for($i = 0; $i < count($sql_tables); $i++) {
				//Aqui eu crio a(s) Nova(s) Tabela(s) dentro da Base de Dados que acabou de ser criada ...
				if(!$db->query($sql_tables[$i])) exit($sql_tables[$i]);
			}
			/*
			 * Aqui eu comentei porque era uma outra analogia de copiar os registros na Nova Base de Dados que foi criada ...

			 * 
			for($i = 0; $i < count($sql_registros); $i++) {
				//Aqui eu insiro o(s) Registro(s) na(s) Tabela(s) dentro da Base de Dados que acabou de ser criada ...
				if(!mysql_query($sql_registros[$i])) exit($sql_registros[$i].' - '.die(mysql_error()));
			}*/
		}
	}
}
?>