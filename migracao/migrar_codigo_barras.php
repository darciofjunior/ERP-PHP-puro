<?
/*Migração da Tabela Transporte*/
return migrar_codigo_barras('codigo_barras_excel2.txt', 'produtos_acabados', 'codigo_barra', '|');

function migrar_codigo_barras($arquivo, $tabela, $campos, $separacao) {
	$conectar = mysql_connect('localhost','root','w1l50n');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  			= trim($linhas[$x]);
			$vetor_conteudo 	= explode('|', $conteudo);
			$sql 				= "SELECT id_produto_acabado 
									FROM $tabela 
									WHERE referencia = '$vetor_conteudo[0]' LIMIT 1 ";
			$campos_consulta	= mysql_query($sql);
			if(mysql_num_rows($campos_consulta) == 0) {//Não encontrou o Produto ...
				echo $vetor_conteudo[0].'<br>';
			}else {//Encontrou normalmente, então eu atualizo o Código de Barra ...
				$sql = "Update $tabela set `$campos` = '$vetor_conteudo[1]' where referencia = '$vetor_conteudo[0]' limit 1 ";
				mysql_query($sql);
			}
		}
		flush();
		$tamanho = filesize($arquivo);
		if ($tamanho >= '1073741824') {
			$tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
		}elseif ($tamanho >= '1048576') {
			$tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
		}elseif ($tamanho >= '1024') {
			$tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
		}else {
			$tamanho = $tamanho . ' B';
		}
		echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
	}else {
		echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
	}
}
?>