<?
/*Migração da Tabela Paises*/
return migrar_ie_mg('ie_mg.txt', 'cod_cliente, insc_estadual', '|');

function migrar_ie_mg($arquivo, $campos, $separacao) {
	$conectar = mysql_connect('localhost', 'root', 'albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$conteudo  = split(',', str_replace($separacao, ',', $conteudo));
			$cod_cliente = $conteudo[0];
			$insc_estadual = $conteudo[2];
			echo $sql = "Update clientes set `insc_estadual` = '$insc_estadual' where `cod_cliente` = '$cod_cliente' limit 1 ";
			echo '<br>';
			mysql_query($sql);
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