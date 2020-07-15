<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class genericas {
    /*Função que rastreia todos os PIs atrelado ao custo, não puxo a 4ª Etapa por ser uma Etapa de Máquinas e não puxo a 7ª Etapa 
    por ser uma Etapa de PAs ...*/
    function pi_dentro_custo_pa($etapas = 0) {
        switch($etapas) {
            case 1://Etapa 1
                $sql = "SELECT DISTINCT(id_produto_insumo) 
                        FROM `pas_vs_pis_embs` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 1
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;
            case 2://Etapa 2
                $sql = "SELECT DISTINCT(pac.`id_produto_insumo`) 
                        FROM `produtos_acabados_custos` pac 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` 
                        AND pac.`id_produto_insumo` IS NOT NULL ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 2
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;
            case 3://Etapa 3
                $sql = "SELECT DISTINCT(pp.id_produto_insumo) 
                        FROM `pacs_vs_pis` pp 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 3
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;	
            case 5://Etapa 5
                $sql = "SELECT DISTINCT(ppt.id_produto_insumo) 
                        FROM `pacs_vs_pis_trat` ppt 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = ppt.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 5
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;
            case 6://Etapa 6
                $sql = "SELECT DISTINCT(ppu.id_produto_insumo) 
                        FROM `pacs_vs_pis_usis` ppu 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = ppu.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 6
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;
            default://Etapas 1, 2, 3, 5 e 6 ...
                $sql = "SELECT DISTINCT(id_produto_insumo) 
                        FROM `pas_vs_pis_embs` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 1
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
                
                $sql = "SELECT DISTINCT(pac.`id_produto_insumo`) 
                        FROM `produtos_acabados_custos` pac 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` 
                        AND pac.`id_produto_insumo` IS NOT NULL ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 2
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
                
                $sql = "SELECT DISTINCT(pp.id_produto_insumo) 
                        FROM `pacs_vs_pis` pp 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 3
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
                
                $sql = "SELECT DISTINCT(ppt.id_produto_insumo) 
                        FROM `pacs_vs_pis_trat` ppt 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = ppt.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 5
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
                
                $sql = "SELECT DISTINCT(ppu.id_produto_insumo) 
                        FROM `pacs_vs_pis_usis` ppu 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = ppu.id_produto_acabado_custo 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
                        WHERE pa.`operacao_custo` = pac.`operacao_custo` ";
                $campos = bancos::sql($sql);//busco o PI atrelado ao custo etapa 6
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= ($campos[$i]['id_produto_insumo'].', ');
            break;
        }
        $id_produtos_insumos    = substr($id_produtos_insumos, 0, (strlen($id_produtos_insumos) - 2));
        $vetor_produto_insumos  = explode(',', $id_produtos_insumos);
        $id_produtos_insumos    = implode(',', array_unique($vetor_produto_insumos));
        $operador               = (count($vetor_produto_insumos) > 1) ? ' NOT IN ' : ' <> ';
        return " AND pi.`id_produto_insumo` $operador ($id_produtos_insumos) ";
    }

//Essa função serve para me retornar a diferença de Porcentagem para > ou < em relação ao segundo valor ...
    function diff_porcentagem($valor1, $valor2, $porcentagem) {
        if($valor1 >= ($valor2 * (1 + $porcentagem / 100)) || $valor1 < ($valor2 * (1 - $porcentagem / 100))) {
            return 1;//Variação Exagerada, o Valor da Moeda Estrangeira ultrapassou a variação Admissível ...
        }else {
            return 0;
        }
    }

    function order_by($campo, $rotulo='', $title, $order_by, $path) { //retorna o nome da empresa clicada ou logada
        if($campo == $order_by) {
            $url = $GLOBALS['PHP_SELF'].$GLOBALS['parametro']."&order_by=$campo DESC";
            $figura="<img width=22 height=22 src='".$path."/imagem/order_asc.png'>";
        }else {
            $url = $GLOBALS['PHP_SELF'].$GLOBALS['parametro']."&order_by=$campo";
            if(($campo." desc") == $order_by) {
                $figura = "<img width=22 height=22 src='".$path."/imagem/order_desc.png'>";
            }else {
                $figura = '';
            }
        }
        return "<label onclick=".'"'."JavaScript:location=("."'".$url."')".'" style="cursor:pointer" title="'.$title.'"><font color=darkblue><b>'.$rotulo."</b></font>&nbsp;".$figura." </label>";
    }
	
    function replace($de, $para, $mystring) { //retorna o nome da empresa clicada ou logada
        do {
            $mystring	= strtoupper($mystring);
            $findme     = strtoupper($de);
            $pos_aux	= strpos($mystring, $findme);
            if(is_numeric($pos_aux)) {
                    $mystring   = substr($mystring,0,$pos_aux).$para.substr($mystring,($pos_aux+strlen($para)+(strlen($de)-strlen($para))),strlen($mystring));
            }
        }while(!empty($pos_aux));
        return $mystring;
    }

    function nome_empresa($id_empresa) {
        //Busca o nome da empresa clicada ou logada através do id passado por parâmetro ...
        $sql = "SELECT nomefantasia 
                FROM `empresas` 
                WHERE `id_empresa` = '$id_empresa' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            return $campos[0]['nomefantasia'];
        }else {
            return 'EMPRESA NÃO IDENTIFICADA !!!';
        }
    }
    
    function nota_sgd($id_empresa) {
        if($id_empresa == 4) {//Somente a Empresa Grupo trabalhará com SGD que é sem Nota Fiscal ...
            $nota_sgd   = 'S';
            $tipo_nota  = ' (SGD)';
        }else {
            $nota_sgd   = 'N';
            $tipo_nota  = ' (NF)';
        }
        return array('nota_sgd' => $nota_sgd, 'tipo_nota' => $tipo_nota);
    }

    function buscar_referencia($id_produto_insumo, $referencia, $antigo = '') {
        //Se a referência do PI for do Tipo PRAC, então ele busca a Referência do PA na tabela PIPA relacional
        if($referencia == 'PRAC') {
            $sql = "SELECT referencia 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {
                //$referencia = $referencia.' - '.$campos[0]['referencia'];
                return $campos[0]['referencia'];
            }
        }else {
            return $referencia;
        }
    }

//Função q pega a moeda dia q traz todas em todos os modos U$ e Euro
    function moeda_dia($tipo = 'todas') {
        switch($tipo) {
            case 'dolar'://Retorna o dólar do dia
                $sql = "SELECT valor_dolar_dia 
                        FROM `cambios` ORDER BY id_cambio DESC LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return $campos[0]['valor_dolar_dia'];
                }
            break;
            case 'euro'://Retorna o euro do dia
                $sql = "SELECT valor_euro_dia 
                        FROM `cambios` ORDER BY id_cambio DESC LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return $campos[0]['valor_euro_dia'];
                }
            break;
            default: //retorna em array as duas
                $sql = "SELECT valor_dolar_dia, valor_euro_dia 
                        FROM `cambios` ORDER BY id_cambio DESC LIMIT 2 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return array ('dolar'=>$campos[0]['valor_dolar_dia'], 'euro'=>$campos[0]['valor_euro_dia']);
                }
            break;
        }
    }

    function variavel($id_variavel) {//Retorna o valor da variável passada por parâmetro ...
        $sql = "SELECT valor 
                FROM `variaveis` 
                WHERE `id_variavel` = '$id_variavel' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            return 1;
        }else {
            return $campos[0]['valor'];
        }
    }

    function variaveis($valor='todas') {//pegar em variaves os valores das moedas custos
        switch($valor) {
            case 'dolar_custo'://Retorna o dólar custo
                $sql = "SELECT valor 
                        FROM `variaveis` 
                        WHERE `id_variavel` = '7' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return $campos[0]['valor'];
                }
            break;
            case 'euro_custo'://Retorna o euro custo
                $sql = "SELECT valor 
                        FROM `variaveis` 
                        WHERE `id_variavel` = '8' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return $campos[0]['valor'];
                }
            break;
            case 'moeda_custo'://Retorna o dólar custo e o euro custo
                $sql = "SELECT valor 
                        FROM `variaveis` 
                        WHERE `id_variavel` IN (7, 8) LIMIT 2 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {
                    return 1;
                }else {
                    return array ('dolar_custo'=> $campos[0]['valor'], 'euro_custo'=> $campos[1]['valor']);
                }
            break;
            case 'taxa_financeira_vendas'://Retorna a taxa financeira de vendas
                $sql = "SELECT valor 
                        FROM `variaveis` 
                        WHERE `id_variavel` = '16' LIMIT 1 ";
                $campos                 = bancos::sql($sql);
                return $campos[0]['valor'];
            break;
            case 'fator_importacao'://Retorna o fator importação
                if(!class_exists('genericas')) require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
                $fator_custo_importacao = genericas::variavel(1);
                return $fator_custo_importacao;
            break;
            default: //retorna em array as duas
                echo '0';
            break;
        }
    }

//Busca o Cep da Base de Ceps, retornando o endereço, bairro, cidade, uf
    function buscar_cep($cep) {
        $cep = str_replace('-', '', $cep);
        if(strlen($cep) != 8) {//Significa que o CEP é inválido
            echo 'CEP INVÁLIDO !';
            exit;
        }
        $cep = substr($cep, 0, 5).'-'.substr($cep, 5, 3);
        //Seleciona os dados da tabela de cep conforme o q foi digitado pelo usuário
        $sql = "SELECT * 
                FROM `ceps` 
                WHERE `cep` = '$cep' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 1) {//Encontrou o Cep
            $logradouro = addslashes($campos[0]['logradouro']);
            $bairro 	= addslashes($campos[0]['bairro']);
            $cidade 	= addslashes($campos[0]['cidade']);
            $uf         = $campos[0]['uf'];
            $ddd        = $campos[0]['ddd'];
            
            //Busca o id_uf do Estado ...
            $sql = "SELECT id_uf 
                    FROM `ufs` 
                    WHERE `sigla` = '$uf' LIMIT 1 ";
            $campos_ufs = bancos::sql($sql);
            $id_uf      = $campos_ufs[0]['id_uf'];

            return array ('logradouro' => $logradouro, 'bairro' => $bairro, 'cidade' => $cidade, 'uf' => $uf, 'ddd' => $ddd, 'id_uf' => $id_uf);
        }else {//Não encontrou o Cep
            return 0;
        }
    }
        
    /*Essa função tem o objetivo de retornar Datas que equivalem ao período de Pagamento de Comissão dos Representantes, 
    Fechamento da Folha - normalmente retorna o último período mensal, mais eu posso retroagir ou acrescentar mais meses ...*/
    function retornar_data_relatorio($mes_descontar = 0) {
        $dia_current = date('d');
        $mes_current = (int)date('m');
        $ano_current = date('Y');
        $mes_current-=(int)$mes_descontar;
        if($dia_current > 25) {//Se o dia é > 25
            if($mes_current < 10) {
                if($mes_current <= 0) {//Quando vira o ano e no parametro subtraímos 1 dava erro no calculo
                    //Desse 12 que eu fixei, eu subtrai a diferença $mes_current que foi calculada na 4ª linha dessa função ...
                    $mes_current = 12 + $mes_current;
                    $ano_current--;
                }else {
                    if($mes_current < 10) $mes_current = '0'.$mes_current;
                }
            }
            $txt_data_inicial = '26/'.$mes_current.'/'.$ano_current;
            if($mes_current == 12) {//Se o Mês é Dezembro
                $ano_current+= 1;//Aumento Hum do ano, pq significa que eu estou virando de ano ...
            }
            $mes_current+= 1;//Incremento sempre 1 a + p/ o prox. mês ...
            if($mes_current == 13) $mes_current = 1;
            if($mes_current < 10) $mes_current = '0'.$mes_current;
            $txt_data_final = '25/'.$mes_current.'/'.$ano_current;
        }else {//Se o dia é < 25
            if($mes_current <= 0) {//Quando vira o ano e no parametro subtraímos 1 dava erro no calculo
                //Desse 12 que eu fixei, eu subtrai a diferença $mes_current que foi calculada na 4ª linha dessa função ...
                $mes_current = 12 + $mes_current;
                $ano_current--;
            }else {
                if($mes_current < 10) $mes_current = '0'.$mes_current;
            }
        }
        $txt_data_final = '25/'.$mes_current.'/'.$ano_current;
        $mes_current-= 1;//Decremento sempre 1 a - p/ o mês anterior ...
        if($mes_current == 00) $mes_current = '12';
        if($mes_current == 12) {//Se o Mês é Dezembro
            $ano_current-= 1;//Desconto Hum do ano, pq significa que eu estou retrocedendo o ano ...
        }
        if($mes_current < 10)   $mes_current = '0'.$mes_current;
        $txt_data_inicial = '26/'.$mes_current.'/'.$ano_current;
        return array ('data_inicial'=>$txt_data_inicial,'data_final'=>$txt_data_final);
    }
	
    function retornar_periodo_folha($data_holerith) {
        //O Parâmetro de Data pode ser passado de qualquer jeito ...
        if(substr($data_holerith, 2, 1) == '/') {//Significa que a Data foi passada no formato Normal ...
            $dia_current = substr($data_holerith, 0, 2);
            $mes_current = (int)substr($data_holerith, 3, 2);
            $ano_current = substr($data_holerith, 6, 4);
        }else {//Significa que a Data foi passada no formato americano ...
            $dia_current = substr($data_holerith, 8, 2);
            $mes_current = (int)substr($data_holerith, 5, 2);
            $ano_current = substr($data_holerith, 0, 4);
        }
        $mes_current-=(int)1;
        if($dia_current > 25) {//Se o dia é > 25
            if($mes_current < 10) {
                if($mes_current==0) {// quando vira o ano e no parametro subtrai um dava erro no calculo
                    $mes_current = '12';
                    $ano_current--;
                }else {
                    $mes_current = '0'.$mes_current;
                }
            }
            $txt_data_inicial = '26/'.$mes_current.'/'.$ano_current;
            //Se o Mês é Dezembro
            if($mes_current == 12)  $ano_current+= 1;//Aumento Hum do ano, pq significa que eu estou virando de ano ...
            $mes_current+= 1;//Incremento sempre 1 a + p/ o prox. mês ...
            if($mes_current == 13)  $mes_current = 1;
            if($mes_current < 10)   $mes_current = '0'.$mes_current;
            $txt_data_final = '25/'.$mes_current.'/'.$ano_current;
        }else {//Se o dia é < 25
            if($mes_current < 10) {
                if($mes_current==0) {// quando vira o ano e no parametro subtrai um dava erro no calculo
                    $mes_current = '12';
                    $ano_current--;
                }else {
                    $mes_current = '0'.$mes_current;
                }
            }
            $txt_data_final = '25/'.$mes_current.'/'.$ano_current;
            $mes_current-= 1;//Decremento sempre 1 a - p/ o mês anterior ...
            if($mes_current == 00) $mes_current = '12';
            //Se o Mês é Dezembro
            if($mes_current == 12) $ano_current-= 1;//Desconto Hum do ano, pq significa que eu estou retrocedendo o ano ...
            if($mes_current < 10) $mes_current = '0'.$mes_current;
            $txt_data_inicial = '26/'.$mes_current.'/'.$ano_current;
        }
        return array ('data_inicial'=>$txt_data_inicial,'data_final'=>$txt_data_final);
    }
    
    /*Essa função é muito utilizada quando o usuário vai registrar um Follow-Up do Cliente, porque hoje em 
    dia é guardado o "id_representante" nessa tabela "follow_ups" tentando agilizar aí o PDT de Vendas ...*/
    function buscar_id_representante($id_cliente_contato) {
        //Aqui eu guardo o id_representante no Registro de Follow-UP p/ agilizar o processamento da tela de PDT ...
        $sql = "SELECT cr.`id_representante` 
                FROM `clientes_contatos` cc 
                INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` 
                WHERE cc.`id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        return $campos_representante[0]['id_representante'];
    }
    /*******************************************************************************************/
    /******************************Funções do Site / Área do Cliente****************************/
    /*******************************************************************************************/
    function conexao_com_o_site_area_cliente() {
        //Site em que está hospedado as NFes e Danfes do(s) Cliente(s)
        $host = mysql_connect('mysharedhost0027.locaweb.com.br', 'grupoalbafer1', 's1t34r34cl13nt');
        mysql_select_db('grupoalbafer1', $host);
    }
    
    function atualizar_clientes_no_site_area_cliente($id_cliente) {
        //Busca de dados do Cliente atual ...
        $sql = "SELECT `id_pais`, `id_uf`, `razaosocial`, `cnpj_cpf`, `ddi_com`, `ddd_com`, `telcom`, `email`, `ativo` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        /*Essas atualizações no site / área do cliente só serão feitas p/ os Clientes que são do Brasil, porque se atualizarmos 
        dados p/ os Clientes de Exportação como e-mail por exemplo, o sistema irá enviar de forma automática 
        XML(s) das NF(es) o que não é interessante, devido já enviarmos documentações paralelas nesses casos ...*/
        if($campos[0]['id_pais'] == 31) {
            $id_pais        = $campos[0]['id_pais'];
            $id_uf          = $campos[0]['id_uf'];
            $razaosocial    = $campos[0]['razaosocial'];
            $cnpj_cpf       = $campos[0]['cnpj_cpf'];
            $ddi_com        = $campos[0]['ddi_com'];
            $ddd_com        = $campos[0]['ddd_com'];
            $telcom         = $campos[0]['telcom'];
            $email          = $campos[0]['email'];
            $ativo          = $campos[0]['ativo'];
            
            self::conexao_com_o_site_area_cliente();//Conexão com o Site / Área do Cliente ...
            
            //Verifico se esse Cliente já existe no Site / Área do Cliente ...
            $sql = "SELECT `id_cliente` 
                    FROM `clientes` 
                    WHERE `cnpj_cpf` = '$cnpj_cpf' LIMIT 1 ";
            $campos = mysql_query($sql);
            if(mysql_num_rows($campos) == 0) {//Significa que este CLIENTE não existe no Site / Área do Cliente e sendo assim vou add ele ...
                //Add o Cliente ...
                $sql = "INSERT INTO `clientes` (`id_cliente_cadastro`, `id_cliente`, `razaosocial`, `cnpj_cpf`, `id_pais`, `id_uf`, `ddi_com`, `ddd_com`, `telcom`, `email`) VALUES (NULL, '$id_cliente', '$razaosocial', '$cnpj_cpf', '$id_pais', '$id_uf', '$ddi_com', '$ddd_com', '$telcom', '$email') ";
                mysql_query($sql);
                $id_cliente = mysql_insert_id();

                //Add um usuário para o Site / Área do Cliente ...
                $senha = rand(0, 999999);//Senha randômica
                for($i = 0; $i < 5; $i++) $senha = strrev(base64_encode($senha));//Criptografia da Senha ...
                
                $sql = "UPDATE `clientes` SET `senha` = '$senha' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                mysql_query($sql);
            }else {//Significa que este CLIENTE já existe no Site / Área do Cliente e sendo assim vou alterar ele ...
                $sql = "UPDATE `clientes` SET `id_cliente` = '$id_cliente', `razaosocial` = '$razaosocial', `id_pais` = '$id_pais', `id_uf` = '$id_uf', `ddi_com` = '$ddi_com', `ddd_com` = '$ddd_com', `telcom` = '$telcom', `email` = '$email', `ativo` = '$ativo' WHERE `cnpj_cpf` = '$cnpj_cpf' LIMIT 1 ";
                mysql_query($sql);
            }
            /******************************************************************/
        }
    }
    
    function atualizar_pas_no_site_area_cliente($id_produto_acabado) {
        //Busca de dados do Produto Acabado atual ...
        $sql = "SELECT cf.`classific_fiscal`, ed.`razaosocial`, gpa.`nome`, pa.`operacao`, pa.`origem_mercadoria`, 
                pa.`referencia`, pa.`discriminacao`, pa.`peso_unitario`, pa.`altura`, pa.`largura`, pa.`comprimento`, 
                pa.`codigo_barra`, pa.`ativo`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $classific_fiscal       = $campos[0]['classific_fiscal'];
        $divisao                = $campos[0]['razaosocial'];
        $grupo                  = $campos[0]['nome'];
        $operacao_faturamento   = $campos[0]['operacao'];
        $origem_mercadoria      = $campos[0]['origem_mercadoria'];
        $referencia             = $campos[0]['referencia'];
        $discriminacao          = $campos[0]['discriminacao'];
        $peso_unitario          = $campos[0]['peso_unitario'];
        $altura                 = $campos[0]['altura'];
        $largura                = $campos[0]['largura'];
        $comprimento            = $campos[0]['comprimento'];
        $codigo_barra           = $campos[0]['codigo_barra'];
        $ativo                  = $campos[0]['ativo'];
        $unidade                = $campos[0]['sigla'];

        //Busca da Qtde de Peças por Embalagem do PA Corrente ...
        $sql = "SELECT `pecas_por_emb` 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `embalagem_default` = '1' LIMIT 1 ";
        $campos_pecas_emb   = bancos::sql($sql);
        $pecas_por_emb      = $campos_pecas_emb[0]['pecas_por_emb'];
        
        self::conexao_com_o_site_area_cliente();//Conexão com o Site / Area do Cliente ...
        
        //Verifico se esse Produto Acabado já existe no Site / Área do Cliente ...
        $sql = "SELECT `id_produto_acabado` 
                FROM `produtos_acabados` 
                WHERE ((`discriminacao` = '$discriminacao') OR (`referencia` = '$referencia' AND `referencia` <> 'ESP')) 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = mysql_query($sql);
        if(mysql_num_rows($campos) == 0) {//Significa que este PRODUTO ACABADO não existe no Site / Área do Cliente e sendo assim vou add ele ...
            //Add um Produto Acabado para o Site / Área do Cliente ...
            $sql = "INSERT INTO `produtos_acabados` (`id_produto_acabado`, `referencia`, `discriminacao`, `codigo_barra`, `classific_fiscal`, `unidade`, `operacao_faturamento`, `origem_mercadoria`, `peso_unitario`, `altura`, `largura`, `comprimento`, `pecas_por_emb`, `divisao`, `grupo`, `ativo`) VALUES ('$id_produto_acabado', '$referencia', '$discriminacao', '$codigo_barra', '$classific_fiscal', '$unidade', '$operacao_faturamento', '$origem_mercadoria', '$peso_unitario', '$altura', '$largura', '$comprimento', '$pecas_por_emb', '$divisao', '$grupo', '$ativo') ";
            mysql_query($sql);
        }else {//Significa que este PRODUTO ACABADO já existe no Site / Área do Cliente e sendo assim vou alterar ele ...
            $sql = "UPDATE `produtos_acabados` SET `referencia` = '$referencia', `discriminacao` = '$discriminacao', `classific_fiscal` = '$classific_fiscal', `unidade` = '$unidade', `operacao_faturamento` = '$operacao_faturamento', `origem_mercadoria` = '$origem_mercadoria', `peso_unitario` = '$peso_unitario', `altura` = '$altura', `largura` = '$largura', `comprimento` = '$comprimento', `pecas_por_emb` = '$pecas_por_emb', `divisao` = '$divisao', `grupo` = '$grupo', `ativo` = '$ativo' WHERE `id_produto_acabado` = '".mysql_result($campos, 0, 'id_produto_acabado')."' LIMIT 1 ";
            mysql_query($sql);
        }
        /******************************************************************/
    }
    
    function atualizar_representantes_no_site_area_cliente($id_representante) {
        //Busca de dados do Representante atual ...
        $sql = "SELECT `nome_fantasia`, `cidade`, `uf`, `zona_atuacao`, `ativo` 
                FROM `representantes` 
                WHERE `id_representante` = '$id_representante' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $representante  = $campos[0]['nome_fantasia'];
        $cidade         = $campos[0]['cidade'];
        $uf             = $campos[0]['uf'];
        $zona_atuacao   = $campos[0]['zona_atuacao'];
        $ativo          = $campos[0]['ativo'];
        self::conexao_com_o_site_area_cliente();//Conexão com o Site / Área do Cliente ...
        
        //Verifico se esse Representante já existe no Site / Área do Cliente ...
        $sql = "SELECT `id_representante` 
                FROM `representantes` 
                WHERE `representante` = '$representante' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = mysql_query($sql);
        if(mysql_num_rows($campos) == 0) {//Significa que este REPRESENTANTE não existe no Site / Área do Cliente e sendo assim vou add ele ...
            //Add um Representante para o Site / Área do Cliente ...
            $sql = "INSERT INTO `representantes` (`id_representante`, `representante`, `cidade`, `uf`, `zona_atuacao`, `ativo`) VALUES ('$id_representante', '$representante', '$cidade', '$uf', '$zona_atuacao', '$ativo') ";
            mysql_query($sql);
        }else {//Significa que este REPRESENTANTE já existe no Site / Área do Cliente e sendo assim vou alterar ele ...
            $sql = "UPDATE `representantes` SET `representante` = '$representante', `cidade` = '$cidade', `uf` = '$uf', `zona_atuacao`= '$zona_atuacao', `ativo` = '$ativo' WHERE `id_representante` = '".mysql_result($campos, 0, 'id_representante')."' LIMIT 1 ";
            mysql_query($sql);
        }
        /******************************************************************/
    }
}
?>