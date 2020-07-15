<?
/*Migração da Tabela Transporte*/
return migrar_produtos_acabados('promocao_vendas_2015-4.txt', '|');

function migrar_produtos_acabados($arquivo, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$vetor_conteudo = explode('|', $conteudo);
			
			/*
			$sql = "SELECT id_produto_acabado 
					FROM `produtos_acabados` 
					WHERE `referencia` = '$vetor_conteudo[0]' ";
			$campos = mysql_query($sql);
			
			//Busca a Qtde de Peças do PA em que a Embalagem é Default ...
			$sql = "SELECT pecas_por_emb 
					FROM `pas_vs_pis_embs` 
					WHERE `id_produto_acabado` = '".mysql_result($campos, 0, 'id_produto_acabado')."' 
					AND `embalagem_default` = '1' LIMIT 1 ";
			$campos_qtde_pecas 	= mysql_query($sql);
			$qtde_pecas 		= (mysql_num_rows($campos_qtde_pecas) == 1) ? round(mysql_result($campos_qtde_pecas, 0, 'pecas_por_emb'), 2) : 1;*/
			
			echo $sql = "Update `produtos_acabados` set `preco_promocional` = '$vetor_conteudo[2]', `qtde_promocional` = '$vetor_conteudo[1]', `preco_promocional_b` = '$vetor_conteudo[3]', `qtde_promocional_b` = '$vetor_conteudo[1]'  where `referencia` = '$vetor_conteudo[0]' limit 1; ";
			echo '<br>'.mysql_query($sql);
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