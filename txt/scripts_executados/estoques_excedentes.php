<?
require('../../lib/segurancas.php');
session_start('funcionarios');

$sql = "SELECT * 
        FROM `estoques_excedentes` 
        WHERE observacao <> '' 
        LIMIT 35000 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $vetor_observacao = explode('<br>', $campos[$i]['observacao']);
    
    if(count($vetor_observacao) == 3) {//Significa que temos no mínimo 2 saídas ... 1 Entrada / 2 Saídas = '3' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        if($login_data1 == $login_data2) {
            
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            
            echo '<br/>';
        }
    }else if(count($vetor_observacao) == 4) {//Significa que temos no mínimo 3 saídas ... 1 Entrada / 3 Saídas = '4' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 5) {//Significa que temos no mínimo 4 saídas ... 1 Entrada / 4 Saídas = '5' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 6) {//Significa que temos no mínimo 5 saídas ... 1 Entrada / 5 Saídas = '6' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 7) {//Significa que temos no mínimo 6 saídas ... 1 Entrada / 6 Saídas = '7' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        $login_data6 = strchr($vetor_observacao[6], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data6 = substr($login_data6, 0, strlen($login_data6) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5 || $login_data5 == $login_data6) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/>';
            echo $vetor_observacao[6].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 8) {//Significa que temos no mínimo 7 saídas ... 1 Entrada / 7 Saídas = '8' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        $login_data6 = strchr($vetor_observacao[6], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data6 = substr($login_data6, 0, strlen($login_data6) - 3);
        
        $login_data7 = strchr($vetor_observacao[7], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data7 = substr($login_data7, 0, strlen($login_data7) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5 || $login_data5 == $login_data6 || $login_data6 == $login_data7) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/>';
            echo $vetor_observacao[6].'<br/>';
            echo $vetor_observacao[7].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 9) {//Significa que temos no mínimo 8 saídas ... 1 Entrada / 8 Saídas = '9' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        $login_data6 = strchr($vetor_observacao[6], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data6 = substr($login_data6, 0, strlen($login_data6) - 3);
        
        $login_data7 = strchr($vetor_observacao[7], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data7 = substr($login_data7, 0, strlen($login_data7) - 3);
        
        $login_data8 = strchr($vetor_observacao[8], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data8 = substr($login_data8, 0, strlen($login_data8) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5 || $login_data5 == $login_data6 || $login_data6 == $login_data7 || $login_data7 == $login_data8) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/>';
            echo $vetor_observacao[6].'<br/>';
            echo $vetor_observacao[7].'<br/>';
            echo $vetor_observacao[8].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 10) {//Significa que temos no mínimo 9 saídas ... 1 Entrada / 9 Saídas = '10' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        $login_data6 = strchr($vetor_observacao[6], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data6 = substr($login_data6, 0, strlen($login_data6) - 3);
        
        $login_data7 = strchr($vetor_observacao[7], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data7 = substr($login_data7, 0, strlen($login_data7) - 3);
        
        $login_data8 = strchr($vetor_observacao[8], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data8 = substr($login_data8, 0, strlen($login_data8) - 3);
        
        $login_data9 = strchr($vetor_observacao[9], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data9 = substr($login_data9, 0, strlen($login_data9) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5 || $login_data5 == $login_data6 || $login_data6 == $login_data7 || $login_data7 == $login_data8 || $login_data8 == $login_data9) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/>';
            echo $vetor_observacao[6].'<br/>';
            echo $vetor_observacao[7].'<br/>';
            echo $vetor_observacao[8].'<br/>';
            echo $vetor_observacao[9].'<br/><br/>';
        }
    }else if(count($vetor_observacao) == 11) {//Significa que temos no mínimo 9 saídas ... 1 Entrada / 10 Saídas = '11' ...
        $login_data1 = strchr($vetor_observacao[1], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data1 = substr($login_data1, 0, strlen($login_data1) - 3);
        
        $login_data2 = strchr($vetor_observacao[2], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data2 = substr($login_data2, 0, strlen($login_data2) - 3);
        
        $login_data3 = strchr($vetor_observacao[3], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data3 = substr($login_data3, 0, strlen($login_data3) - 3);
        
        $login_data4 = strchr($vetor_observacao[4], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data4 = substr($login_data4, 0, strlen($login_data4) - 3);
        
        $login_data5 = strchr($vetor_observacao[5], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data5 = substr($login_data5, 0, strlen($login_data5) - 3);
        
        $login_data6 = strchr($vetor_observacao[6], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data6 = substr($login_data6, 0, strlen($login_data6) - 3);
        
        $login_data7 = strchr($vetor_observacao[7], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data7 = substr($login_data7, 0, strlen($login_data7) - 3);
        
        $login_data8 = strchr($vetor_observacao[8], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data8 = substr($login_data8, 0, strlen($login_data8) - 3);
        
        $login_data9 = strchr($vetor_observacao[9], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data9 = substr($login_data9, 0, strlen($login_data9) - 3);
        
        $login_data10 = strchr($vetor_observacao[10], 'Login:');//Pegar a String à partir da palavra Login ...
        $login_data10 = substr($login_data10, 0, strlen($login_data10) - 3);
        
        if($login_data1 == $login_data2 || $login_data2 == $login_data3 || $login_data3 == $login_data4 || $login_data4 == $login_data5 || $login_data5 == $login_data6 || $login_data6 == $login_data7 || $login_data7 == $login_data8 || $login_data8 == $login_data9 || $login_data9 == $login_data10) {
            echo $campos[$i]['id_estoque_excedente'].'|Total Vetor='.count($vetor_observacao).'|'.$vetor_observacao[0].'<br/>';
            echo $vetor_observacao[1].'<br/>';
            echo $vetor_observacao[2].'<br/>';
            echo $vetor_observacao[3].'<br/>';
            echo $vetor_observacao[4].'<br/>';
            echo $vetor_observacao[5].'<br/>';
            echo $vetor_observacao[6].'<br/>';
            echo $vetor_observacao[7].'<br/>';
            echo $vetor_observacao[8].'<br/>';
            echo $vetor_observacao[9].'<br/>';
            echo $vetor_observacao[10].'<br/><br/>';
        }
    }
    
    //echo $campos[$i]['id_estoque_excedente'].'-'.count($vetor_observacao).'-'.$campos[$i]['observacao'].'<br/>';
    
    
    //$pos === false
    
    
    //echo strpos($campos[$i]['observacao'], '(Entrada');
    unset($login1);
}

/*mesmo login
mesma data
mesma hora minuto
acao no mínimo 2*/
?>