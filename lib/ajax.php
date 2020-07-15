<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class ajax {
//Retorna uma consulta no formato JSON p/ um objeto div de um Página ...
    function auto_complete($campos, $campo_trazer) {
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrar pelo menos 1 Registro, então se cria o Formato JSon ...
            $json = "{'array_palavras':[";
            foreach($campos as $row) {
                $campo_exibir = str_replace(',', '', $row[$campo_trazer]);
                $campo_exibir = str_replace("'", '', $row[$campo_trazer]);
                $json.= "'".utf8_encode($campo_exibir)."',";
            }
            $json = substr($json, 0, strlen($json) - 1);
            $json.= "]}";
            echo $json;
        }else {
            echo -1;//Macete p/ não dar pau, -1 em homenagem a um fato q ocorreu no passado (rs) ...
        }
    }
//Retorna uma consulta no formato XML p/ um objeto combo de um Página  ...
    function combo($campos, $campo_trazer1, $campo_trazer2) {
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrar pelo menos 1 Registro, então se cria o arquivo XML ...
            $xml  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            $xml .= "<xml_principal>\n";
            for($i = 0; $i < $linhas; $i++) {
                $xml .= "<xml>\n";
                $xml .= "<id_xml>".$campos[$i][$campo_trazer1]."</id_xml>\n";
                $xml .= "<rotulo_xml>".$campos[$i][$campo_trazer2]."</rotulo_xml>\n";
                $xml .= "</xml>\n";
            }
            $xml.= "</xml_principal>\n";
            Header('Content-type: application/xml; charset=iso-8859-1');//Cabeçalho XML ...
            echo $xml;
        }
    }
}
?>