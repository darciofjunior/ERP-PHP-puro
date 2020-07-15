<?
class diretorio {
	function diretorio($caminho, $raiz, $local, $opcao) {
		$diretorio = opendir($caminho);
		if ($diretorio) {
			echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
			if ($opcao == 1 || $opcao == 3) {
				if (file_exists($local)) {
					$nome = dirname($local);
					$base = basename($local);
					if ($caminho != '../../') {
						echo '<option value="'.$nome.'/" class="destaquecombo">DIRETÓRIO PERTENCENTE DO '.$base.'</option>'."\n";
					}
				}
				if ($caminho != '../../') {
					echo '<option value="'.$raiz.'">RAIZ</option>'."\n";
					echo '<option value="'.$local.'" selected>'.$base.'</option>'."\n";
				}
			}
			while ($arquivo = readdir($diretorio)) {
				switch ($opcao) {
					case 1:	
							if (is_dir($caminho.$arquivo) && $arquivo != '..' && $arquivo != '.') {
								echo '<option value="'.$caminho.$arquivo.'/">'.$arquivo.'</option>'."\n";
							}
						break;
					case 2:
							if (is_file($caminho.$arquivo) && $arquivo != '..' && $arquivo != '.') {
								echo '<option value="'.$caminho.$arquivo.'">'.$arquivo.'</option>'."\n";
							}
						break;
					default:
							if (is_dir($caminho.$arquivo) && $arquivo != '..' && $arquivo != '.' || is_file($caminho.$arquivo)) {
								echo '<option value="'.$caminho.$arquivo.'/">'.$arquivo.'</option>'."\n";
							}
				}
			}}else {
					echo '<option value="" class="destaquecombo">ERROR AO TENTAR ABRIR O DIRET&Oacute;RIO '.$local.'</option>';
			}
			clearstatcache();
			closedir($diretorio);
	}
}

class copiar  {
	function copiar_arquivo($diretorio, $nome, $arquivo, $tamanho, $tipo, $opcao_tipo) {
	global $retorno;
		switch($opcao_tipo) {
			case 1:
					if (file_exists($diretorio.$arquivo) || !is_writeable($diretorio)) {
						$retorno = 'Arquivo existente ou diretório sem permissão de gravação '.$arquivo.' '.date('d/m/Y H:m:s')."\r\n";
					}else {
						if (copy($nome, $diretorio.$arquivo)) {
							$retorno = 'Arquivo copiado com sucesso '.$arquivo.' no diretório '.$diretorio.' '.date('d/m/Y H:m:s')."\r\n";
						}else {
							$retorno = 'Error ao tentar copiar o arquivo '.$arquivo.' no diretório '.$diretorio.' '.date('d/m/Y H:m:s')."\r\n";	
						}
					}
				break;
			case 2:
					$y = 0;
					for ($x = strlen($arquivo) - 1; $x >= 0; $x --) {
							if (substr($arquivo, $x, 1) == '.') {
								break;
							}
						$y ++;
					}
					$extensao  = substr($arquivo, -$y);
					$aleatorio = md5(uniqid(microtime(), 1)).getmypid().date('dmYHms');
					$aleatorio = $aleatorio.'.'.$extensao;
					if (file_exists($diretorio.$aleatorio)) {
							$retorno = 'Arquivo existente ou diretório sem permissão de gravação '.$arquivo.' '.date('d/m/Y H:m:s')."\r\n";
					}else {
						if (copy($nome, $diretorio.$aleatorio)) {
							$retorno = 'Arquivo copiado com sucesso '.$arquivo.' no diretório '.$diretorio.' '.date('d/m/Y H:m:s')."\r\n";
						}else {
							$retorno = 'Error ao tentar copiar o arquivo '.$arquivo.' no diretório '.$diretorio.' '.date('d/m/Y H:m:s')."\r\n";
						}
					}
					return ($aleatorio);
				break;
			default:
				$abrir       = fopen($nome, 'rb');
				$temporario  = fread($abrir, filesize($nome));
				fclose($abrir);
				$temporario = addslashes($temporario);
		}
	}
}
?>