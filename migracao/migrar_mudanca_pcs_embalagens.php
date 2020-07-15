<?
/*Migração da Tabela Cliente*/
return migrar_mudanca_pcs_embalagem('pcas_embalagens_pinos.txt', '|');

function migrar_mudanca_pcs_embalagem($arquivo, $separacao) {
	//$conectar = mysql_connect('localhost','','');
	$conectar = mysql_connect('localhost', 'root', 'albafer');
	mysql_select_db('erp_albafer', $conectar);
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = explode('|', $linhas[$x]);
			$sql = "SELECT id_produto_acabado 
					FROM `produtos_acabados` 
					WHERE `referencia` = '$conteudo[0]' LIMIT 1 ";
			$campos = mysql_query($sql);	
			echo $sql = "UPDATE `pas_vs_pis_embs` SET pecas_por_emb = '$conteudo[1]' WHERE `id_produto_acabado` = '".mysql_result($campos, 0, 'id_produto_acabado')."' AND `embalagem_default` = '1' LIMIT 1 ";
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