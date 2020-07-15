<?
if(!class_exists('sessao')) require 'sessao.php';//CASO EXISTA EU DESVIO A CLASSE ...

class bancos extends PDO {
    const dsn 		= 'mysql:dbname=erp_albafer';
    const user 		= 'root';
    const password      = '3rp4lb4f3r';

    public static function getDb() {
        static $db = null;
        if(is_null($db)) {
            $db = new PDO(self::dsn, self::user, self::password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $db;
    }

    public function sql($sql, $inicio = 0, $qtde_por_pagina = 'infinito', $paginar = 'nao', $pagina_atual = 0, $ajax = '', $name_div = '') {
        /**********************************************************************************************/
        /********************************PREVENÇÃO CONTRA SQL INJECTION********************************/
        /**********************************************************************************************/
        //$sql = preg_replace(sql_regcase("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/"), "", $sql);
        $sql = trim($sql);
        //$sql = strip_tags($sql);
        //$sql = addslashes($sql);
        /**********************************************************************************************/
        switch(strtoupper(substr($sql, 0, 1))) {
            case 'S'://S=> SELECT ...
            case '(':
                //SHOW TABLE STATUS WHERE COMMENT = 'tabela_por_ano' AND NAME = 'orcamentos_vendas'
                //if($executar_por_ano == 'S') {
                    /*$table          = strrchr($sql, 'FROM ');
                    $table          = explode(' ', $table);
                    $table_name     = $table[1];
                    //Verifico se a Tabela existe no Banco de Dados atual ...
                    $sql_table      = "SHOW TABLES LIKE '".str_replace('`', '', $table_name)."' ";
                    $campos         = bancos::getDb()->query($sql_table);
                    $result         = $campos->rowCount();
                    //Busco a mesma tabela em outros Bancos de Dados ...
                    $erp_albafer = '`erp_albafer'.date('Y').'`';//A princípio sugiro o 1º Banco com sendo o do ano atual ...
                    //O menor ano que o ERP irá vasculhar será o Ano de 2004, que foi onde inicamos o Sistema na Albafer ...
                    for($ano = date('Y'); $ano > 2004; $ano--) {
                        $sql = str_replace('FROM ', 'FROM '.$erp_albafer.'.', $sql);
                        $sql = str_replace('INNER JOIN ', 'INNER JOIN `erp_albafer`.', $sql);
                        echo $sql;
                        exit;
                    }*/
                    //Aqui eu verifico se a Tabela da Query é gerada anualmente ...
                    //$sql_table      = "SHOW TABLE STATUS WHERE NAME = '".str_replace('`', '', $table_name)."' ";
                    //$campos         = bancos::getDb()->query($sql_table);
                    //$result         = $campos->fetch(PDO::FETCH_ASSOC);


                    //if($result['Comment'] == 'tabela_por_ano') {
                        //echo $sql;
                    //}
                    //$campos_registros     = bancos::getDb()->query($sql);
                    //$linhas     = $campos_registros->rowCount();
                //}

                //echo $condicao = stristr($sql, 'FROM');

                /*
                //Aqui eu verifico se a tabela do SQL do usuário existe no BD atual conectado ...
                $sql_table = "SELECT COUNT(*) AS qtde_table_encontrada 
                                FROM information_schema.tables 
                                WHERE table_schema = '".self::dsn."' 
                                AND table_name = '$table_name' LIMIT 1 ";
                $campos = bancos::getDb()->query($sql_table);
                $result = $campos->fetch(PDO::FETCH_ASSOC);

                //Significa que essa tabela não se encontra no BD atual ...
                if($result['qtde_table_encontrada'] == 0) {
                    for($ano = 2008; $ano <= date('Y'); $ano++) {
                        $dsn = 'mysql:dbname=erp_albafer'.$ano;
                        bancos::getDb();
                    }
                }

                SHOW TABLE STATUS WHERE COMMENT = 'tabela_por_ano'
                 * 
                 * 
                 */
                /**********Aqui eu exibo no Mysql o Usuário que está logado e que está pesando o Banco**********/
                $sql = "/*IP=".substr($_SESSION['ip'], 10, 3)."->".$_SESSION['login']."*/".$sql;
                /***********************************************************************************************/
                if(empty($inicio) || $inicio < 0) $inicio = 0;
                $limite = " LIMIT $inicio, $qtde_por_pagina ";
                $campos = ($qtde_por_pagina == 'infinito') ? bancos::getDb()->query($sql) : bancos::getDb()->query($sql.$limite);
                if(!$campos) {
                    exit('ERRO DE QUERY !');
                    return 0;
                }else {
                    while ($row = $campos->fetch(PDO::FETCH_ASSOC)) $valores[] = $row;
                    if(strtoupper($paginar) == 'SIM') {//Se for com Paginação ...
                        require('paginacao.php');//Só pego a paginação se realmente precisar ...
                        if(empty($pagina_atual)) $pagina_atual = 1;
                        $condicao = stristr($sql, 'FROM');
                        if(!empty($GLOBALS['sql_extra'])) {//Aqui retorna vários registros ...
                            $campos_registros 	= bancos::getDb()->query($GLOBALS['sql_extra']);
                            $linhas                 = $campos_registros->rowCount();
                            //Se retornar 1 registro, pode ser que retornou um montante da query em uma única coluna e daí eu uso fetchColumn ...
                            if($linhas == 1) 	$linhas = $campos_registros->fetchColumn();							
                        }else {//Aqui retorna apenas 1 único registro com o Total Retornado do SQL ...
                            /***********************************************************************************/
                            /*****************Controle p/ sabermos a Qtde de Registros da Query*****************/
                            /***********************************************************************************/
                            //Não existe a palavra DISTINCT dentro da QUERY ...
                            $distinct   = (strpos($sql, 'DISTINCT') === false) ? '*' : strtok(strstr($sql, 'DISTINCT'), ')').')';
                            $qtde_union = substr_count($sql, 'UNION');
                            //Verifico se existe a palavra UNION dentro da QUERY ...
                            if($qtde_union > 0) {//Existe UNION ...
                                $primeira_parte_da_query    = strchr($sql, 'FROM');//Pega do FROM pra frente ...
                                $primeira_parte_da_query    = substr($primeira_parte_da_query, 0, strpos($primeira_parte_da_query, 'UNION'));//Pego do UNION para trás ...
                                $segunda_parte_da_query     = strchr($sql, 'UNION ALL ');//Pega do UNION ALL pra frente ...
                                if($qtde_union == 2) {
                                    $query_indefinida           = strchr($segunda_parte_da_query, 'FROM ');//Pega do outro FROM pra frente ...
                                    $terceira_parte_da_query    = substr($query_indefinida, 0, strpos($query_indefinida, 'UNION'));//Pego do UNION para trás ...
                                    $quarta_parte_da_query      = strchr($query_indefinida, 'UNION ALL ');//Pega do UNION ALL pra frente ...
                                    $quinta_parte_da_query      = strchr($quarta_parte_da_query, 'FROM ');//Pega do outro FROM pra frente ...
                                    $quinta_parte_da_query      = substr($quinta_parte_da_query, 0, strpos($quinta_parte_da_query, 'ORDER'));//Pego do ORDER BY para trás ...
                                    $sql_count                  = "(SELECT COUNT($distinct) AS total_registro ".$primeira_parte_da_query." UNION ALL (SELECT COUNT($distinct) AS total_registro ".$terceira_parte_da_query." UNION ALL (SELECT COUNT($distinct) AS total_registro ".$quinta_parte_da_query;
                                }else {
                                    $terceira_parte_da_query    = strchr($segunda_parte_da_query, 'FROM ');//Pega do outro FROM pra frente ...
                                    $terceira_parte_da_query    = substr($terceira_parte_da_query, 0, strpos($terceira_parte_da_query, 'ORDER'));//Pego do ORDER BY para trás ...
                                    $sql_count                  = "(SELECT COUNT($distinct) AS total_registro ".$primeira_parte_da_query." UNION ALL (SELECT COUNT($distinct) AS total_registro ".$terceira_parte_da_query;
                                }
                                $campos_registros 	= bancos::getDb()->query($sql_count);
                            }else {//Não existe UNION ...
                                $campos_registros 	= bancos::getDb()->query("SELECT COUNT($distinct) AS total_registro $condicao ");
                            }
                            /***********************************************************************************/
                            //Paginação ... verifico se existe GROUP BY na QUERY, p/ identificar que comando utilizar ...
                            $linhas = (strpos($sql, 'GROUP') === false) ? $campos_registros->fetchColumn() : $campos_registros->rowCount();
                        }
                        $total_registro = ($linhas == 0) ? 0 : $linhas;
                        if(strtolower($ajax) == 'ajax') {
                            paginacao::paginar_ajax($total_registro, $qtde_por_pagina, $pagina_atual, $name_div);
                        }else 
                            paginacao::paginar($total_registro, $qtde_por_pagina, $pagina_atual);
                    }
                    return $valores;
                }
            break;
            case 'I'://I=> INSERT ...
            case 'U'://U=> UPDATE ... 
            case 'D'://D=> DELETE ...
                $campos	= bancos::getDb()->query($sql);
                if(!$campos) {
                    exit('ERRO DE QUERY !');
                    return 0;
                }else {
                    if(strtoupper(substr($sql, 0, 1)) == 'I') {
                        //Passo essa variável como global para que essa seja enxergada na outra função "id_registro" abaixo ...
                        $GLOBALS['id_registro'] = bancos::getDb()->lastInsertId();
                    }
                    session_start('funcionarios');
                    //Como existe login e o assunto não tem nada relacionado a ouvidoria, posso então registrar a Ocorrência nos logs ...
                    if($_SESSION['id_login'] <> 0 && (strpos($sql, 'ouvidorias') === false)) {
                        /********************************************Logs********************************************/
                        if(!class_exists('logs')) require 'logs.php';//CASO EXISTA EU DESVIO A CLASSE
                        logs::gerenciar_logs();//Cria toda a Estrutura de Banco de Dados de Logs ...
                        $vetor_meses 	= array('', '1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
                        $database 		= 'logs_'.date('Y');
                        $mes_current 	= $vetor_meses[(int)date('m')];
                        $sql 			= "INSERT INTO $database.`logs_manipulacao_$mes_current` (`id_log`, `id_login`, `sql`, `comando`, `ip`, `data`) VALUES (NULL, '$_SESSION[id_login]', '".str_replace("'", '"', $sql)."', 3, '$_SESSION[ip]', '".date('Y-m-d H:i:s')."') ";
                        bancos::getDb()->query($sql);
                        /********************************************************************************************/
                    }
                    return 1;
                }
            break;
            default:
                exit('SQL N&Atilde;O IDENTIFICADO !');
                return 0;
            break;
        }
    }

    public function id_registro() {//Retorna o último inserido de uma tabela no Banco de Dados ...
        return $GLOBALS['id_registro'];
    }
}

class combos extends bancos {
    function combo_array($array, $id_banco = 0) {
        $retorno        = "<option value='' style='color:red'>SELECIONE</option>";
        
        $total_array 	= count($array);
        $valor_banco 	= $array[$id_banco][0];
        $ativo          = 0;
        if(empty($valor_banco)) {//Certifico que ele não foi deletado, Luis ..
            $valor_banco = $array[$id_banco][1];
            $ativo = 1;
        }
        for($i = 1; $i <= $total_array; $i++) {
            $selected = ($valor_banco == $array[$i][(int)$ativo]) ? 'selected' : '';
            //Se não foi deletado, então eu apresento essa linha ...
            if(!empty($array[$i][(int)$ativo])) $retorno.= "<option value='".$i."'".$selected.">".$array[$i][(int)$ativo]."</option>";
        }
        return $retorno;
    }

    public function combo($sql, $id = '', $trazer_emails_corporativos = 'N') {
        $campos_registros   = bancos::getDb()->query($sql);
        $combo  = '<option value="" style="color:red">SELECIONE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>'."\n";
        
        if($trazer_emails_corporativos == 'S') {
            $vetor_emails_corporativos = array_sistema::emails_corporativos();
            foreach($vetor_emails_corporativos as $email_corporativo) {
                $selected = ($email_corporativo == $id) ? 'selected' : '';
                $combo.= '<option value="'.$email_corporativo.'"'.$selected.'>'.$email_corporativo.'</option>'."\n";
            }
        }
        
        if(is_array($id)) {//Significa que trabalhei com uma Combo "Múltipla" e nesse caso posso ter mais de 1 id selecionado ...
            foreach($campos_registros as $row) {
                $selected = (in_array($row[0], $id)) ? 'selected' : '';
                $combo.='<option value="'.$row[0].'"'.$selected.'>'.$row[1].'</option>'."\n";
            }
        }else {//Combo "Simples" ...
            foreach($campos_registros as $row) {
                $selected = ($row[0] == $id) ? 'selected' : '';
                $combo.='<option value="'.$row[0].'"'.$selected.'>'.$row[1].'</option>'."\n";
            }
        }
        return $combo;
    }

    function listar_tabelas() {
        $campos_registros = bancos::getDb()->query('SHOW TABLES FROM `erp_albafer` ');
        echo '<option value="" style="color:red">SELECIONE</option>'."\n";
        foreach($campos_registros as $row) {
            echo '<option value="'.$row[0].'">'.$row[0].'</option>'."\n";
        }
    }

    function selecionar_tabelas($tabela) {
        $campos_registros = bancos::getDb()->query('SHOW TABLES FROM `erp_albafer` ');
        echo '<option value="" style="color:red">SELECIONE</option>'."\n";
        foreach($campos_registros as $row) {
            $selected = ($row[0] == $tabela) ? 'selected' : '';
            $combo.='<option value="'.$row[0].'"'.$selected.'>'.$row[0].'</option>'."\n";
        }
    }
}
?>