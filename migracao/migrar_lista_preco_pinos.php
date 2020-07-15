<?
//Migração da Tabela Municípios ...
return migrar_lista_preco_pinos('lista_pinos_2010.txt', '|');

function migrar_lista_preco_pinos($arquivo, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$conteudo  = split(',', str_replace($separacao, ',', $conteudo));
			$referencia 			= $conteudo[0];
			$qtde_promocional 		= $conteudo[1];
			$preco_promocional 		= $conteudo[2];
			$qtde_promocional_b 	= $conteudo[3];
			$preco_promocional_b 	= $conteudo[4];
			
			$sql = "Update produtos_acabados set `qtde_promocional` = '$qtde_promocional', `preco_promocional` = '$preco_promocional', `qtde_promocional_b` = '$qtde_promocional_b', `preco_promocional_b` = '$preco_promocional_b' where referencia = '$referencia' limit 1 ";
			if(!mysql_query($sql)) {
				echo $conteudo . "<br>";
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