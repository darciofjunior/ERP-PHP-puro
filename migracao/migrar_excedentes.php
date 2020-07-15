<?
/*Migração da Tabela Paises*/
return migrar_excedentes('migrar_excedentes.txt', 'qtde, prateleira, bandeja, observacao', '|');

function migrar_excedentes($arquivo, $campos, $separacao) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x ++) {
			$conteudo  = trim($linhas[$x]);
			$conteudo  = split(',', str_replace($separacao, ',', $conteudo));
			$sql = "Select id_produto_acabado 
					from `produtos_acabados` 
					where `referencia` = '".$conteudo[0]."' limit 1 ";
			$campos_pa = mysql_query($sql);
//Se encontrou o PA então ...
			if(mysql_num_rows($campos_pa) == 1) {
				$sql = "Insert estoques_excedentes (`id_estoque_excedente`, `id_produto_acabado`, `qtde`, `prateleira`, `bandeja`, `observacao`) values (null, '".mysql_result($campos_pa, 0, 'id_produto_acabado')."', '".str_replace('.', '', $conteudo[1])."', '".$conteudo[2]."', '".$conteudo[3]."', '".$conteudo[4]."') ";
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