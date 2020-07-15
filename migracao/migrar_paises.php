<?
/*Migração da Tabela Paises*/
return migrar_paises('paises.txt', 'codigo_pais, pais', '|');

function migrar_paises($arquivo, $campos, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$conteudo  = split(',', str_replace($separacao, ',', $conteudo));
			$codigo_pais = substr($conteudo[0], 1, 4); $pais = $conteudo[1];
			$sql = "Select id_pais 
					from paises 
					where pais = '$pais' limit 1 ";
			$campos_pais = mysql_query($sql);
//Se encontrou o País então ...
			if(mysql_num_rows($campos_pais) == 1) {
				$sql = "Update paises set `codigo_pais` = '$codigo_pais' where `id_pais` = '".mysql_result($campos_pais, 0, 'id_pais')."' limit 1 ";
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