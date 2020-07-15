<?
/*
Nome: Dárcio Fernandes de Souza Júnior
Última atualizacao: 20-08-2007
*/
class data {
	function is_date($data) { //passar data formato americano
		$ano   = substr($data, 0, 4);
		$mes   = substr($data, 5, 2);
		$dia   = substr($data, 8, 2);
		if(!checkdate($mes,$dia,$ano)) {
			return false;
		}
	}

	function datetodata($data, $caracter) {
		$ano   = substr($data, 0, 4);
		$mes   = substr($data, 5, 2);
		$dia   = substr($data, 8, 2);
		return($dia.$caracter.$mes.$caracter.$ano);
	}

	function datatodate($data, $caracter) {
		$ano   = substr($data, 6, 4);
		$mes   = substr($data, 3, 2);
		$dia   = substr($data, 0, 2);
		return($ano.$caracter.$mes.$caracter.$dia);
	}
	
	function mes($numero) {
		$meses  = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		return $meses[$numero];
	}

	function dia($dia) {
		$dias_semana = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado');
		return $dias_semana[$dia];
	}
	
	function dia_semana($data) {
		$ano   = substr($data, 6, 4);
		$mes   = substr($data, 3, 2);
		$dia   = substr($data, 0, 2);

		$dia_semana = date('w', mktime(0, 0, 0, $mes, $dia, $ano));
		/*Legenda ... -> 0 - Domingo, 1 - Segunda, 2 - Terça, 3 - Quarta, 4 - Quinta, 5 - Sexta, 6 - Sábado ...*/
		return $dia_semana;
	}

	function listar_dia($tipo) {
		echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($tipo == 1) {
			for ($x = 1; $x <= date('t'); $x ++) {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
			}
		}else {
			$dias_semana = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado');
			for ($x = 0; $x <= 6; $x ++) {
				echo '<option value="'.$dias_semana[$x].'">'.$dias_semana[$x].'</option>'."\n";
			}
		}
	}
	
	function listar_mes($tipo) {
		echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";		
		if ($tipo == 1) {
			for ($x = 1; $x <= 12; $x ++) {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
			}
		}else {
			$meses  = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
			for ($x = 1; $x <= 12; $x ++) {
				echo '<option value="'.$meses[$x].'">'.$meses[$x].'</option>'."\n";
			}
		}
	}
	
	function selecionar_dia($tipo) {
		echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($tipo == 1) {
			for ($x = 1; $x <= date('t'); $x ++) {
				if ($x ==  date('d')) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}else {
			$dias_semana = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado');
			for ($x = 0; $x <= 6; $x ++) {
					if ($x ==  date('w')) {
						echo '<option value="'.$dias_semana[$x].'" selected>'.$dias_semana[$x].'</option>'."\n";
					}else {
						echo '<option value="'.$dias_semana[$x].'">'.$dias_semana[$x].'</option>'."\n";
					}
			}
		}
	}
	
	function selecionar_mes($tipo) {
		echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($tipo == 1) {
			for ($x = 1; $x <= 12; $x ++) {
				if ($x == date('m')) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}else {
			$meses  = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
			for ($x = 1; $x <= 12; $x ++) {
				if ($x == date('m')) {
					echo '<option value="'.$meses[$x].'" selected>'.$meses[$x].'</option>'."\n";
				}else {
					echo '<option value="'.$meses[$x].'">'.$meses[$x].'</option>'."\n";
				}
			}
		}
	}
	
	function listar_ano($opcao, $inicio, $quantidade) {
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
	
	function selecionar_ano($opcao, $inicio, $quantidade) {
		echo '<option value="" class="destaquecombo">SELECIONE</option>'."\n";
		if ($opcao == 1)  {
			for ($x = $inicio + $quantidade; $x >= $inicio; $x --) {
				if ($x == date('Y')) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}else {
			for ($x = $inicio; $x <= $inicio + $quantidade; $x ++) {
				if ($x == date('Y')) {
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
			}
		}
	}

	function selecionar_hora() {
		for ($x = 00; $x <= 23; $x ++) {
			if ($x == date('H')) {
					if (strlen($x) == 1) {
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					if (strlen($x) == 1) {
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
		}
	}
	
	function selecionar_minutos() {
		for ($x = 0; $x <= 59; $x ++) {
			if ($x == date('i')) {
					if (strlen($x) == 1) { 
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					if (strlen($x) == 1) { 
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
		}
	}
	
	function selecionar_segundos() {
		for ($x = 0; $x <= 59; $x ++) {
			if ($x == date('s')) {
					if (strlen($x) == 1) { 
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'" selected>'.$x.'</option>'."\n";
				}else {
					if (strlen($x) == 1) {
						$x = '0'.$x;
					}
					echo '<option value="'.$x.'">'.$x.'</option>'."\n";
				}
		}
	}
        
        function calcular_horas($hora_inicial, $hora_final, $operacao) {
            $vetor_inicial  = explode(':', $hora_inicial);
            $hora_inicial   = ($vetor_inicial[0] * 60) + $vetor_inicial[1];
            $vetor_final    = explode(':', $hora_final);
            $hora_final     = ($vetor_final[0] * 60) + $vetor_final[1];

            if($operacao == '-') {
                $valor = $hora_final - $hora_inicial;
            }elseif($operacao == '+') {
                $valor = $hora_inicial + $hora_final;
            }else {
                return false;
            }

            //Comentamos pq para nós é interessante o acumalativo de várias horas excedendo mais que um dia -> 24 horas ...
            //if($valor >= 1440)  $valor -= 1440;
            //if($valor < 0)      $valor += 1440;

            $minutos    = $valor % 60;
            $horas      = (int)(($valor - $minutos) / 60);
            return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
        }
	
	function diferenca_data($data1, $data2) {
            $segundos  = strtotime($data2)-strtotime($data1);
            $dias      = (integer)round(round(($segundos/86400),2),0);
            $horas     = intval($segundos/3600);
            $segundos -= $horas * 3600;
            $minutos   = intval($segundos/60);
            $segundos -= $minutos * 60;
            return array($dias, $horas, $minutos, $segundos);
	}

/*Retorna uma nova data, entre 'a primeira data e o intervalo de dias' no formato anti-heróis*/
	function adicionar_data_hora($data, $qtddias) {//ou retirar
		/*
                    Windows:  Timestamp Negativos não são suportados dentro de uma versão conhecida de Windows. 
                    Portanto a faixa de anos válidos inclui apenas 1970 a 2038.
                    fora desta data até o momento só o unix trata isto é até 2069 ...
		*/
		$ano   = intval(substr($data, 6, 4));
		$mes   = intval(substr($data, 3, 2));
		$dia   = intval(substr($data, 0, 2));
		if((integer) $ano > 2037) {
                    $ano = intval(substr($ano, 2, 2));
		}
		$dia   = $dia + intval($qtddias);
		return date("d/m/Y", mktime (0,0,0, $mes, $dia, $ano));
	}
	
	function adicionar_hora($horario, $qtdeminutos) {
		$horas   	= substr($horario, 0, 2);
		$minutos  	= substr($horario, 3, 2);
		$minutos+= intval($qtdeminutos);
		return date("H:i:s", mktime ($horas, $minutos, 0, 0, 0, 0));
	}

	function intervalo_semana($data) {
		$dia = (integer)substr($data,0,2);
		$mes = (integer)substr($data,3,2);
		$ano = (integer)substr($data,6,4);

		while(date("w", mktime(0,0,0, $mes, $dia, $ano))!=0) {
			date("w", mktime(0,0,0, $mes, $dia, $ano))."-";
			$dia--;
			if($dia<=0) { //caso for 01/01 tem q haver esta parte para nao ir para 0 e sim para 31
				$mes--;
				if($mes<=0) { //caso for 01/01/2005 tem q haver esta parte para nao ir para 0 e sim para 31/12/2004
					$mes=12;
					$ano--;
				}
				$dia=date("t", mktime(0,0,0, $mes, 1, $ano));
			}
		}
		if(strlen($dia)==1) { $dia='0'.$dia; }
		if(strlen($mes)==1) { $mes='0'.$mes; }
		$datas[0]=$dia.'/'.$mes.'/'.$ano;
		$datas[1]=data::adicionar_data_hora($datas[0], 6);
		return $datas;
	}

	function numero_semana($dia, $mes, $ano) {
            $dia = (integer)$dia;
            $mes = (integer)$mes;
            $ano = (integer)$ano;
            if(date("w", mktime(0,0,0, $mes, $dia, $ano)) == 0) { // retorna a representação numérica do dia da semana 0 (para Domingo) a 6 (para Sábado)
                    return date("W", mktime(0,0,0,$mes,++$dia,$ano));
            }else {
                    return date("W", mktime(0,0,0,$mes,$dia,$ano));
            }
	}

	function detalhe_data($data) {
		$meses     = array('', 'Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		$dias      = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'S&aacute;bado');
		$dia       = substr($data, 0, 2);
		$mes       = substr($data, 3, 2);
		$ano       = substr($data, 6, 4);
		$tempo     = mktime(0, 0, 0, $mes, $dia, $ano);
		$qtde_dias = date('t', mktime(0, 0, 0, $mes, $dia, $ano));
		$data      = getdate($tempo);
		$nome_mes  = $meses[$data['mon']];
		$nome_dia  = $dias[$data['wday']];
		return array($nome_mes, $nome_dia, $data['wday'], $qtde_dias);
	}

	function calendario($ano = '', $mes = '') {
		$mes = !$mes ? date('m') : $mes;
	        $ano = !$ano ? date('Y') : $ano;
		list($nome_mes, $nome_dia) = $this->detalhe_data(date('d-m-Y'));
		$resultado.='<table width="350" border="0" cellspacing="1" cellpadding="1" align="center">
				<tr class="atencao" align="center">
					<td colspan="7">'.$nome_dia." ".date('d')." de ".$nome_mes." de ".date('Y').'</td>
						<tr class="linhacabecalho" align="center">
							<td>Dom:</td>
						<td>Seg:</td>
						<td>Ter:</td>
						<td>Qua:</td>
							<td>Qui:</td>
						<td>Sex:</td>
							<td>Sab:</td>
					</tr>';
		$dia      = 1;
		$contador = 0;
		while ($dia <= cal_days_in_month(1, $mes, $ano)) {
			if ($contador % 2 == 0) {
				$css = 'linhanormalforte';
			}else {
				$css = 'linhanormal';
			}
			$resultado.= '<tr class="'.$css.'" onmouseover="sobre_celula(this)" onmouseout="fora_celula(this)">';
			for ($x = 0; $x <= 6; $x ++) {
				if ($dia <= cal_days_in_month(1, $mes, $ano)) {
					if (date('w', mktime(0, 0, 0, $mes, $dia, $ano)) == $x) {
						$dia = strlen($dia) <= 1 ? 0 . $dia : $dia;
						$mes = strlen($mes) <= 1 ? 0 . $mes : $mes;
						$resultado.='<td align="center" width="50">'.$dia ++.'</td>';
					}else {
						$resultado.= '<td></td>';
					}
				}
			}
			$resultado.= '</tr>';
			$contador ++;
		}
		$resultado.= "</table>";
		echo $resultado;
	}
        
        function qtde_dias_do_mes_ano($mes, $ano) {
            $qtde_dias = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            return $qtde_dias;
        }
}