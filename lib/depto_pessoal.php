<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class depto_pessoal {
    //Lembrando que a Data de Holerith, está sempre 1 mês a frente do Período da Folha ...
    function periodo_folha($data_holerith) {
        $dia_holerith = substr($data_holerith, 0, 2);
        $mes_holerith = substr($data_holerith, 3, 2);
        $ano_holerith = substr($data_holerith, 6, 4);
        
        if($mes_holerith == '01') {//Se o mês da Data do Holerith = Janeiro "Início de Ano" ...
            $mes_inicial_folha  = 11;//Novembro ...
            $mes_final_folha    = 12;//Dezembro ...
            
            $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.($ano_holerith - 1);//Iremos trazer o Período do ano Passado
            $data_final_folha   = '25/'.$mes_final_folha.'/'.($ano_holerith - 1);//Iremos trazer o Período do ano Passado
        }else {//Fevereiro em Diante ...
            $mes_inicial_folha  = ($mes_holerith - 2);
            $mes_final_folha    = ($mes_holerith - 1);
            
            /*Esse é um Tratamento especial somente p/ o mês de Fevereiro "02" porque senão a variável $mes_inicial_folha 
            ficaria como sendo = "00" o que não existe e sendo assim volto para Dezembro ...*/
            if($mes_inicial_folha == '00') $mes_inicial_folha  = 12;
            
            //Preparo esses campos em Formato de mês p/ retornar p/ o usuário ...
            if($mes_inicial_folha < 10) $mes_inicial_folha  = '0'.$mes_inicial_folha;
            if($mes_final_folha < 10)   $mes_final_folha    = '0'.$mes_final_folha;
            //Se o mês inicial da Folha = Dezembro "Fim de Ano" ...
            if($mes_inicial_folha == 12) {
                $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.($ano_holerith - 1);//Iremos trazer o Período do ano Passado
            }else {
                $data_inicial_folha = '26/'.$mes_inicial_folha.'/'.$ano_holerith;
            }
            $data_final_folha   = '25/'.$mes_final_folha.'/'.$ano_holerith;
        }
        return array('data_inicial_folha' => $data_inicial_folha, 'data_final_folha' => $data_final_folha);
    }
    
    //Aqui eu contabilizo quantos que o funcionário realmente trabalhou, caso este tenha entrado em férias ...
    function dias_trabalhados($id_funcionario, $data_holerith) {
        /*A princípio busco algumas datas como Admissão e Férias p/ saber o período das últimas férias 
        do Funcionário ...*/
        $sql = "SELECT `id_empresa`, `data_admissao`, `ultimas_ferias_data_inicial`, `ultimas_ferias_data_final` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        
        //Busca o Período da Folha ...
        $datas_folha        = self::periodo_folha($data_holerith);
        $data_inicial_folha = data::datatodate($datas_folha['data_inicial_folha'], '-');
        $data_final_folha   = data::datatodate($datas_folha['data_final_folha'], '-');
        
        //Se o usuário entrou em férias dentro do período da folha ...
        if($campos[0]['ultimas_ferias_data_inicial'] > $data_inicial_folha && $campos[0]['ultimas_ferias_data_inicial'] < $data_final_folha) {
            $diferenca_data     = data::diferenca_data($data_inicial_folha, $campos[0]['ultimas_ferias_data_inicial']);
            $dias_trabalhados   = $diferenca_data[0];
        }else if($campos[0]['ultimas_ferias_data_final'] > $data_inicial_folha && $campos[0]['ultimas_ferias_data_final'] < $data_final_folha) {
            $diferenca_data     = data::diferenca_data($campos[0]['ultimas_ferias_data_final'], $data_final_folha);
            $dias_trabalhados   = $diferenca_data[0];
        }else {//Significa que o funcionário não estava em período de férias ...
            if($campos[0]['id_empresa'] == 4) {//Empresa Grupo s/ Registro ...
                $diferenca_data     = data::diferenca_data($campos[0]['data_admissao'], $data_final_folha);
                $dias_trabalhados   = $diferenca_data[0];
                if($dias_trabalhados >= 30) {//Funcionário "velho de casa" ...
                    $dias_trabalhados   = 30;
                    /*Nesse caso eu sempre somo mais porque o sistema ignora a Data Inicial que pra esse caso deve 
                    ser contado como dia trabalho - Exemplo: de 16/09 à 25/09 - temos 10 dias trabalhados, ou seja
                    o dia 16 tem de ser contado também ...*/
                }else {
                    $dias_trabalhados+= 1;
                }
            }else {//Se o Funcionário está registrado, mais do que nunca que este é velho de casa ...
                $dias_trabalhados   = 30;
            }
        }
        return $dias_trabalhados;
    }
    
    function valores_hora_extra($data_pagamento, $salario_mensal) {
        if($data_pagamento >= '2018-07-05') {//Nova idéia de Hora Extra ...
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
        }else {//Lógica normal ...
            $adicional_hora_extra = 30;
            $valor_hora_extra_min = 0;
        }
        return array('adicional_hora_extra' => $adicional_hora_extra, 'valor_hora_extra_min' => $valor_hora_extra_min);
    }
    
    //Essa função retorna um array com Tipos de Vale que será visualizado em toda parte de Vale / Salário, etc ...
    function tipos_vale() {
        return array(
            '1' => 'Dia 20', 
            '2' => 'Avulso', 
            '3' => 'Combustível', 
            '4' => 'Consórcio', 
            '5' => 'Convênio Médico', 
            '6' =>  'Convênio Odontológico', 
            '7' =>  'Transporte', 
            '8' =>  'Empréstimo', 
            '9' =>  'Celular', 
            '10' =>  'Mensalidade Sindical', 
            '11' =>  'Contribuição Confederativa', 
            '12' =>  'Imposto Sindical', 
            '13' =>  'Contribuição Assistencial', 
            '14' =>  'Crédito Consignado', 
            '15' =>  'Mensalidade MetalCred'
        );
    }
}
?>