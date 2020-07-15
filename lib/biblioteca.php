<?
class biblioteca {
	function menu($numero) {
		if (strlen($numero) == 1) {
			$montagem = '00'.$numero;
		}elseif (strlen($numero) == 2) {
 			$montagem = '0'.$numero;
		}else {
			$montagem = $numero;
		}
		return ($montagem);
	}

	function loop($opcao, $inicio, $quantidade) {
	echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($opcao == 1)  {
			for ($x = $inicio + $quantidade; $x >= $inicio; $x --) {
				echo '<option value="'.$x.'">'.$x.'</option>'."\n";
			}
		}else {
			for ($x = $inicio; $x <= $inicio + $quantidade; $x ++) {
				echo '<option value="'.$x.'">'.$x.'</option>'."\n";
			}
		}
	}

	function selecionar_loop($opcao, $inicio, $quantidade, $valor) {
	echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($opcao == 1)  {
			for ($x = $inicio + $quantidade; $x >= $inicio; $x --) {
				if ($x == $valor) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}else {
			for ($x = $inicio; $x <= $inicio + $quantidade; $x ++) {
				if ($x == $valor) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}
	}

	function controle_itens($itens1, $itens2, $acao=0) {
		if($acao == 0) {
			$itens= array_merge(explode(',',$itens1),explode(',',$itens2));
			$itens=array_unique($itens);
			$itens = implode(',',$itens);
			if(substr($itens, strlen($itens) - 1, 1) == ',') {
				$itens = substr($itens, 0, strlen($itens) - 1);
			}
    		if(substr($itens, 0,1)==',') {
	    		$itens = substr($itens, 1, strlen($itens));
	    	}
		}else {
			$itens1 = explode(',',$itens1);
			$itens2 = explode(',',$itens2);
			$qtde_itens1=count($itens1);
			$qtde_itens2=count($itens2);
			for($i=0;$i<$qtde_itens1;$i++) {
				for($x=0;$x<$qtde_itens2;$x++) {
					if($itens1[$i]==$itens2[$x]) {
						$valor=1;
					}
				}
				if($valor==0) {
					$itens_aux[]=$itens1[$i];
				} else {
					$valor=0;
				}
			}
			if(count($itens_aux) > 0) {
				$itens=implode(',',$itens_aux);
				if(substr($itens,0,1) == ',') {
					$itens=substr($itens,1,strlen($itens));
				}
			}
		}
		return $itens;
	}
}
?>