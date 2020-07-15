<?
/*Migração da Tabela Transporte*/
return migrar_produtos_acabados('pas_ml.txt', '|');

function migrar_produtos_acabados($arquivo, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$vetor_conteudo = explode('|', $conteudo);
			$produto_h = strtok($vetor_conteudo[0], '-').'H'.strrchr($vetor_conteudo[0], '-');
			echo $sql = "Update `produtos_acabados` set `mmv` = '".round($vetor_conteudo[1] / 2, 2)."' where `referencia` LIKE '$produto_h' limit 1; ";
			echo '<br>';
			//echo '<br>'.mysql_query($sql);
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