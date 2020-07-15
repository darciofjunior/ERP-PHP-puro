<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...

/*
* Helper functions for building a DataTables server-side processing SQL query
*
* The static functions in this class are just helper functions to help build
* the SQL used in the DataTables demo server-side processing scripts. These
* functions obviously do not represent all that can be done with server-side
* processing, they are intentionally simple to show how it works. More complex
* server-side processing operations will likely require a custom script.
*
* See http://datatables.net/usage/server-side for full details on the server-
* side processing requirements of DataTables.
*
* @license MIT - http://datatables.net/license_mit
*
* Customized By emranulhadi@gmail.com | http://emranulhadi.wordpress.com/
*/

class SSP {
    /**
    * Create the data output array for the DataTables rows
    *
    * @param array $columns Column information array
    * @param array $data Data from the SQL get
    *
    * @return array Formatted data in a row based format
    */
    static function data_output($columns, $data) {
        $out = array();
        for ($i = 0, $ien = count($data); $i < $ien; $i++) {
            $row = array();
            for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
                $column = $columns[$j];
// Is there a formatter?
                if (isset($column['formatter'])) {
                    if(isset($column['field'])) {
                        $row[$column['dt']] = $column['formatter']($data[$i][$column['field']], $data[$i]);
                    }else {
                        $row[$column['dt']] = $column['formatter']($data[$i][$column['db']], $data[$i]);
                    }
                }else {
                    if(isset($columns[$j]['field'])) {
                        $row[$column['dt']] = $data[$i][$columns[$j]['field']];
                    }else {
                        $row[$column['dt']] = $data[$i][$columns[$j]['db']];
                    }
                }
            }
            $out[] = $row;
        }
        return $out;
    }
    
    /**
    * Paging
    *
    * Construct the LIMIT clause for server-side processing SQL query
    *
    * @param array $request Data sent to server by DataTables
    * @param array $columns Column information array
    * @return string SQL limit clause
    */
    static function limit($request) {
        $limit = '';
        if(isset($request['start']) && $request['length'] != -1) {
            $limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
        }
        return $limit;
    }

    /**
    * Ordering
    *
    * Construct the ORDER BY clause for server-side processing SQL query
    *
    * @param array $request Data sent to server by DataTables
    * @param array $columns Column information array
    *
    * @return string SQL order by clause
    */
    static function order($request, $columns) {
        $order = '';
        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array();
            $dtColumns = self::pluck( $columns, 'dt' );

            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
// Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                            'ASC' :
                            'DESC';
                    $orderBy[] = '`' . $column['db'] . '` ' . $dir;
                }
            }
            $order = 'ORDER BY ' . implode(', ', $orderBy);
        }
        return $order;
    }

    /**
    * Searching / Filtering
    *
    * Construct the WHERE clause for server-side processing SQL query.
    *
    * NOTE this does not match the built-in DataTables filtering which does it
    * word by word on any field. It's possible to do here performance on large
    * databases would be very poor
    *
    * @param array $request Data sent to server by DataTables
    * @param array $columns Column information array
    * @param array $bindings Array of values for PDO bindings, used in the sql_exec() function
    *
    * @return string SQL where clause
    */
    static function filter($request, $columns, $existe_where, &$bindings) {
        $globalSearch   = array();
        $columnSearch   = array();
        $dtColumns      = self::pluck( $columns, 'dt');

        if(isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];

            for($i = 0, $ien = count($request['columns']) ; $i < $ien ; $i++) {
                $requestColumn  = $request['columns'][$i];
                $columnIdx      = array_search( $requestColumn['data'], $dtColumns );
                $column         = $columns[ $columnIdx ];

                if($requestColumn['searchable'] == 'true') {
                    $binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR);
                    $globalSearch[] = $column['db']." LIKE ".$binding;
                }
            }
        }

        // Individual column filtering
        for($i = 0, $ien = count($request['columns']); $i < $ien ; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search( $requestColumn['data'], $dtColumns );
            $column = $columns[ $columnIdx ];

            $str = $requestColumn['search']['value'];

            if($requestColumn['searchable'] == 'true' && $str != '') {
                $binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR);
                $columnSearch[] = $column['db']." LIKE ".$binding;
            }
        }

        // Combine the filters into a single string
        $where = '';

        if(count($globalSearch)) $where = '('.implode(' OR ', $globalSearch).')';

        if(count($columnSearch)) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
        }
        
        if($existe_where == 'S') {//Como existe WHERE no SQL que foi passado por parâmetro, não posso ter 2 WHERE(s) ...
            if($where !== '') $where = 'AND '.$where;
        }else {//Não existe WHERE no SQL que foi passado por parâmetro ...
            if($where !== '') $where = 'WHERE '.$where;
        }
        return $where;
    }

    /**
    * Perform the SQL queries needed for an server-side processing requested,
    * utilising the helper functions of this class, limit(), order() and
    * filter() among others. The returned array is ready to be encoded as JSON
    * in response to an SSP request, or can be modified if needed before
    * sending back to the client.
    *
    * @param array $request Data sent to server by DataTables
    * @param array $sql_details SQL connection details - see sql_connect()
    * @param string $table SQL table to query
    * @param string $primaryKey Primary key of the table
    * @param array $columns Column information array
    * @param string $extraWhere Where query String
    *
    * @return array Server-side processing response array
    *
    */
    static function simple($request, $sql) {
        //Variáveis que serão utilizadas mais abaixo ...
        $bindings               = array();
        $columns                = self::columns($sql);
        
        //Retiro todas as Quebras de Linha "\n" e retornos de Carro "\r" ...
        $sql        = preg_replace('~[\r\n]+~', '', $sql);
        $sql        = str_replace('`', '', $sql);
        
        $vetor_sql  = explode('FROM', $sql);
        $campos     = str_replace('SELECT ', '', $vetor_sql[0]);
        $tables     = $vetor_sql[1];
        
        //Verifico se existe a String WHERE na String SQL que foi passada por parâmetro ...
        if(strpos($sql, 'WHERE') !== false) {//Encontrou WHERE na String ...
            $existe_where = 'S';
        }else {
            $existe_where = 'N';
        }

        $limit                  = self::limit($request);//Equivale a Qtde de Registros por Página que o usuário seleciona ...
        $order                  = self::order($request, $columns);//Equivale a Ordenação das Colunas 
        $where                  = self::filter($request, $columns, $existe_where, $bindings);//Equivale ao Filtro digitado pelo Usuário na Caixa Localizar ...
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS $campos 
                FROM $tables 
                $where 
                $order 
                $limit ";//Nova Query com Filtro, Ordenaçao e Paginação ...
        //Main query to actually get the data
        $data = self::sql_exec($bindings, $sql);

        //Data set length after filtering
        $resFilterLength = self::sql_exec('SELECT FOUND_ROWS()');
        $recordsFiltered = $resFilterLength[0][0];
       
        // Total data set length
        $resTotalLength = self::sql_exec('SELECT COUNT(*) FROM '.$tables);
        $recordsTotal = $resTotalLength[0][0];
        
        /*
        * Output
        */
        return array(
            'draw'            => intval($request['draw']),
            'recordsTotal'    => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data'            => self::data_output($columns, $data)
        );
    }
    
    /**
    * Execute an SQL query on the database
    *
    * @param resource $db Database handler
    * @param array $bindings Array of PDO binding values from bind() to be
    * used for safely escaping strings. Note that this can be given as the
    * SQL query string if no bindings are required.
    * @param string $sql SQL query to execute.
    * @return array Result from the query (all rows)
    */
    static function sql_exec($bindings, $sql = null) {
        $db = bancos::getDb();//Connect to the database ...
        //Argument shifting
        if($sql === null) $sql = $bindings;
        $stmt = $db->prepare($sql);
        //Bind parameters
        if(is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }
        //Execute
        try {
            $stmt->execute();
        }catch (PDOException $e) {
            self::fatal("An SQL error occurred: " . $e->getMessage());
        }
        //Return all
        return $stmt->fetchAll();
    }
    
    /**
    * Create a PDO binding key which can be used for escaping variables safely
    * when executing a query with sql_exec()
    *
    * @param  array &$a    Array of bindings
    * @param  *      $val  Value to bind
    * @param  int    $type PDO field type
    * @return string       Bound key to be used in the SQL where this parameter
    *   would be used.
    */
    static function bind(&$a, $val, $type) {
        $key = ':binding_'.count($a);

        $a[] = array(
            'key' => $key,
            'val' => $val,
            'type' => $type
        );
        return $key;
    }

    /**
    * Pull a particular property from each assoc. array in a numeric array, 
    * returning and array of the property values from each item.
    *
    *  @param  array  $a    Array to get data from
    *  @param  string $prop Property to read
    *  @return array        Array of property values
    */
    static function pluck($a, $prop) {
        $out = array();
        for($i = 0, $len = count($a); $i < $len; $i++) $out[] = $a[$i][$prop];
        return $out;
    }
    
    //Método que prepara as colunas da Query que foi passada por parâmetro no Padrão desta Classe SSP com db, dt ...
    static function columns($sql) {
        //Variáveis que serão utilizadas mais abaixo ...
        $out        = array();
        $columns    = array();
        $indice     = 0;
        
        //Retiro todas as Quebras de Linha "\n" e retornos de Carro "\r" ...
        $sql        = preg_replace('~[\r\n]+~', '', $sql);
        $sql        = str_replace('`', '', $sql);
        
        $vetor_sql  = explode('FROM', $sql);
        
        //Os Campos que o usuário deseja retornar da Consulta, estão sempre nessa 1ª parte do Vetorzinho ...
        $campos     = str_replace('SELECT ', '', $vetor_sql[0]);//Retiro a palavra "SELECT" que está junto aos campos ...
        $campos     = trim($campos);//Retiro os espaços que estão antes e depois dos campos ...
        $campos     = explode(',', $campos);
        
        foreach($campos as $coluna) {
            //Verifico se existe a String AS no Campo que desejo retornar ...
            if(strpos($coluna, 'AS') !== false) {//Encontrou AS na String ...
                $coluna_tratada = explode('AS ', $coluna);
                array_push($columns, array('db' => $coluna_tratada[1], 'dt' => $indice));
                $indice++;
            }else {
                //Verifico se existe "." no Campo que desejo retornar ...
                if(strpos($coluna, '.') !== false) {//Encontrou "." na String ...
                    $coluna_tratada = strchr($coluna, '.');
                    $coluna_tratada = str_replace('.', '', $coluna_tratada);
                    array_push($columns, array('db' => $coluna_tratada, 'dt' => $indice));
                    $indice++;
                }
            }
        }
        return $columns;
    }
}
?>