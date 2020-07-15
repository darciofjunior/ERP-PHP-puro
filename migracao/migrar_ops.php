<?
/*Migração da Tabela Transporte*/
return migrar_ops('migrar_ops.txt', 'ops', 'id_op, id_produto_acabado, qtde_produzir, data_emissao, prazo_entrega, observacao', '|');

function migrar_ops($arquivo, $tabela, $campos, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$conteudo  = str_replace($separacao, "', '", $conteudo);
			$sql = "insert into $tabela ($campos) values ('$conteudo')";
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