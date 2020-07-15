<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class depto_pessoal {
    //Lembrando que a Data de Holerith, est� sempre 1 m�s a frente do Per�odo da Folha ...
    function periodo_folha($data_holerith) {
        $dia_holerith = substr($data_holerith, 0, 2);
        $mes_holerith = substr($data_holerith, 3, 2);
        $ano_holerith = substr($data_holerith, 6, 4);
        
        if($mes_holerith == '01') {//Se o m�s da Data do Holerith = Janeiro "In�cio de Ano" ...
            $mes_inicial_folha  = 11;//Novembro ...
            $mes_final_folha    = 12;//Dezembro ...
            
            $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.($ano_holerith - 1);//Iremos trazer o Per�odo do ano Passado
            $data_final_folha   = '25/'.$mes_final_folha.'/'.($ano_holerith - 1);//Iremos trazer o Per�odo do ano Passado
        }else {//Fevereiro em Diante ...
            $mes_inicial_folha  = ($mes_holerith - 2);
            $mes_final_folha    = ($mes_holerith - 1);
            
            /*Esse � um Tratamento especial somente p/ o m�s de Fevereiro "02" porque sen�o a vari�vel $mes_inicial_folha 
            ficaria como sendo = "00" o que n�o existe e sendo assim volto para Dezembro ...*/
            if($mes_inicial_folha == '00') $mes_inicial_folha  = 12;
            
            //Preparo esses campos em Formato de m�s p/ retornar p/ o usu�rio ...
            if($mes_inicial_folha < 10) $mes_inicial_folha  = '0'.$mes_inicial_folha;
            if($mes_final_folha < 10)   $mes_final_folha    = '0'.$mes_final_folha;
            //Se o m�s inicial da Folha = Dezembro "Fim de Ano" ...
            if($mes_inicial_folha == 12) {
                $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.($ano_holerith - 1);//Iremos trazer o Per�odo do ano Passado
            }else {
                $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.$ano_holerith;
            }
            $data_final_folha   = '25/'.$mes_final_folha.'/'.$ano_holerith;
        }
        return array('data_inicial_folha' => $data_inicial_folha, 'data_final_folha' => $data_final_folha);
    }
    
    //Aqui eu contabilizo quantos que o funcion�rio realmente trabalhou, caso este tenha entrado em f�rias ...
    function dias_trabalhados($id_funcionario, $data_holerith) {
        /*A princ�pio busco algumas datas como Admiss�o e F�rias p/ saber o per�odo das �ltimas f�rias 
        do Funcion�rio ...*/
        $sql = "SELECT `id_empresa`, `data_admissao`, `ultimas_ferias_data_inicial`, `ultimas_ferias_data_final` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        
        //Busca o Per�odo da Folha ...
        $datas_folha        = self::periodo_folha($data_holerith);
        $data_inicial_folha = data::datatodate($datas_folha['data_inicial_folha'], '-');
        $data_final_folha   = data::datatodate($datas_folha['data_final_folha'], '-');
        
        //Se o usu�rio entrou em f�rias dentro do per�odo da folha ...
        if($campos[0]['ultimas_ferias_data_inicial'] > $data_inicial_folha && $campos[0]['ultimas_ferias_data_inicial'] < $data_final_folha) {
            $diferenca_data     = data::diferenca_data($data_inicial_folha, $campos[0]['ultimas_ferias_data_inicial']);
            $dias_trabalhados   = $diferenca_data[0];
        }else if($campos[0]['ultimas_ferias_data_final'] > $data_inicial_folha && $campos[0]['ultimas_ferias_data_final'] < $data_final_folha) {
            $diferenca_data     = data::diferenca_data($campos[0]['ultimas_ferias_data_final'], $data_final_folha);
            $dias_trabalhados   = $diferenca_data[0];
        }else {//Significa que o funcion�rio n�o estava em per�odo de f�rias ...
            if($campos[0]['id_empresa'] == 4) {//Empresa Grupo s/ Registro ...
                $diferenca_data     = data::diferenca_data($campos[0]['data_admissao'], $data_final_folha);
                $dias_trabalhados   = $diferenca_data[0];
                if($dias_trabalhados >= 30) {//Funcion�rio "velho de casa" ...
                    $dias_trabalhados   = 30;
                    /*Nesse caso eu sempre somo mais porque o sistema ignora a Data Inicial que pra esse caso deve 
                    ser contado como dia trabalho - Exemplo: de 16/09 � 25/09 - temos 10 dias trabalhados, ou seja
                    o dia 16 tem de ser contado tamb�m ...*/
                }else {
                    $dias_trabalhados+= 1;
                }
            }else {//Se o Funcion�rio est� registrado, mais do que nunca que este � velho de casa ...
                $dias_trabalhados   = 30;
            }
        }
        return $dias_trabalhados;
    }
    
    function valores_hora_extra($data_pagamento, $salario_mensal) {
        if($data_pagamento >= '2018-07-05') {//Nova id�ia de Hora Extra ...
            if($salario_mensal < 1500) {
                $adicional_hora_extra = 45;
                $valor_hora_extra_min = 0;
            }else if($salario_mensal < 1750) {
                $adicional_hora_extra = 42.5;
                $valor_hora_extra_min = 9.72;
            }else if($salario_mensal < 2000) {
                $adicional_hora_extra = 40;
                $valor_hora_extra_min = 11.14;
            }else if($salario_mensal < 2400) {
                $adicional_hora_extra = 37.5;
                $valor_hora_extra_min = 12.50;
            }else if($salario_mensal < 2800) {
                $adicional_hora_extra = 35;
                $valor_hora_extra_min = 14.73;
            }else if($salario_mensal < 3200) {
                $adicional_hora_extra = 32.5;
                $valor_hora_extra_min = 16.86;
            }else {
                $adicional_hora_extra = 30;
                $valor_hora_extra_min = 18.91;
            }
        }else {//L�gica normal ...
            $adicional_hora_extra = 30;
            $valor_hora_extra_min = 0;
        }
        return array('adicional_hora_extra' => $adicional_hora_extra, 'valor_hora_extra_min' => $valor_hora_extra_min);
    }
    
    //Essa fun��o retorna um array com Tipos de Vale que ser� visualizado em toda parte de Vale / Sal�rio, etc ...
    function tipos_vale() {
        return array(
            '1' => 'Dia 20', 
            '2' => 'Avulso', 
            '3' => 'Combust�vel', 
            '4' => 'Cons�rcio', 
            '5' => 'Conv�nio M�dico', 
            '6' =>  'Conv�nio Odontol�gico', 
            '7' =>  'Transporte', 
            '8' =>  'Empr�stimo', 
            '9' =>  'Celular', 
            '10' =>  'Mensalidade Sindical', 
            '11' =>  'Contribui��o Confederativa', 
            '12' =>  'Imposto Sindical', 
            '13' =>  'Contribui��o Assistencial', 
            '14' =>  'Cr�dito Consignado', 
            '15' =>  'Mensalidade MetalCred'
        );
    }
}
?>