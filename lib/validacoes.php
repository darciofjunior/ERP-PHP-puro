<?
/*
esta funcao retornarar todas as utiliza��es basica
*/
class validacoes {
	function validar($palavra, $opcao) {
		switch(strtoupper($opcao)) {
			case "EMAIL":
//Se maior q 5 caracteres ent�o ele vasculha no loop
				if(strlen($palavra) >= 5) {
//Verifica se o e-mail possui arroba
					$achou_arroba = strpos($palavra, '@');
					$achou_ponto = strpos($palavra, '.');
//Verifica se o arroba ou o ponto est�o exatamente na primeira posi��o do string
					if($achou_arroba == 0 || $achou_ponto == 0) {
						return 0;
					}else {
						if($achou_ponto < $achou_arroba) {
							for($i = $achou_arroba; $i < strlen($palavra); $i++) {
								if(substr($palavra, $i, 1) == '.') {
									$posicao_ponto_loop = $i;
									$i = strlen($palavra);
								}
							}
						}else {
							$posicao_ponto_loop = $achou_ponto;
						}
//Verifica se o ponto n�o est� logo ap�s o arroba ex: --> luis_gomes@. <-- inv�lido
						if(($posicao_ponto_loop - $achou_arroba) == 1) {
							return 0;
						}else {
							return 1;
						}
					}
				}else {
					return 0;
				}
				break;
				default:
				break;
			}
		}
	}
?>

