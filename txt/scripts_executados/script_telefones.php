<?
require('../../lib/segurancas.php');
session_start('funcionarios');

$sql = "SELECT id_fornecedor, ddd_fone1, ddd_fone2, ddd_fax, fone1, fone2, fax 
        FROM fornecedores 
        WHERE uf = 'SP' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $digitos_lidos  = 0;
    $novo_telefone  = '';
    $novo_ddd       = '';
    $fone1          = ereg_replace('[^0-9]', '', $campos[$i]['fone1']);
    
    for($j = (strlen($fone1) - 1); $j >= 0; $j--) {
        $digito_atual = substr($fone1, $j, 1);
        //Vou verificando digito por dígito ...
        if($digitos_lidos <= 7) {
            $novo_telefone.= $digito_atual;
            if($digitos_lidos == 3) $novo_telefone.= '-';
        }else {
            $novo_ddd.= $digito_atual;
        }
        $digitos_lidos++;
    }
   
    $novo_ddd       = strrev($novo_ddd);
    $novo_ddd       = substr($novo_ddd, 1, strlen($novo_ddd));
    $novo_telefone  = strrev($novo_telefone);
    
    if(substr($novo_telefone, 0, 2) <> 70 && substr($novo_telefone, 0, 2) <> 77 && substr($novo_telefone, 0, 2) <> 78 && substr($novo_telefone, 0, 2) <> 79) {
        if(substr($novo_telefone, 0, 1) == 6 || substr($novo_telefone, 0, 1) == 7 || substr($novo_telefone, 0, 1) == 8 || substr($novo_telefone, 0, 1) == 9) {
            $novo_telefone = '9'.$novo_telefone;
        }    
    }
    if($campos[$i]['ddd_fone1'] == '' && $novo_ddd != '') {
        echo $sql = "UPDATE fornecedores SET ddd_fone1 = '$novo_ddd' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMIT 1 ";
        bancos::sql($sql);
        echo '<br>';
    } 
        echo $sql = "UPDATE fornecedores SET fone1 = '$novo_telefone' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMTI 1 ";
        bancos::sql($sql);
        echo '<br><br>';
    
    /*________________________________________________________________________________________________________________________*/
    $digitos_lidos  = 0;
    $novo_telefone  = '';
    $novo_ddd       = '';
    $fone2          = ereg_replace('[^0-9]', '', $campos[$i]['fone2']);
        
    for($j = (strlen($fone2) - 1); $j >= 0; $j--) {
        $digito_atual = substr($fone2, $j, 1);
        //Vou verificando digito por dígito ...
        if($digitos_lidos <= 7) {
            $novo_telefone.= $digito_atual;
            if($digitos_lidos == 3) $novo_telefone.= '-';
        }else {
            $novo_ddd.= $digito_atual;
        }
        $digitos_lidos++;
    }
   
    $novo_ddd       = strrev($novo_ddd);
    $novo_ddd       = substr($novo_ddd, 1, strlen($novo_ddd));
    $novo_telefone  = strrev($novo_telefone);
    
    if(substr($novo_telefone, 0, 2) <> 70 && substr($novo_telefone, 0, 2) <> 77 && substr($novo_telefone, 0, 2) <> 78 && substr($novo_telefone, 0, 2) <> 79) {
        if(substr($novo_telefone, 0, 1) == 6 || substr($novo_telefone, 0, 1) == 7 || substr($novo_telefone, 0, 1) == 8 || substr($novo_telefone, 0, 1) == 9) {
            $novo_telefone = '9'.$novo_telefone;
        }    
    }
    if($campos[$i]['ddd_fone2'] == '' && $novo_ddd != '') {
        echo $sql = "UPDATE fornecedores SET ddd_fone2 = '$novo_ddd' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMIT 1 ";
        bancos::sql($sql);
        echo '<br>';
    } 
        echo $sql = "UPDATE fornecedores SET fone2 = '$novo_telefone' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMTI 1 ";
        bancos::sql($sql);
        echo '<br><br>';
    
    /*________________________________________________________________________________________________________________________*/
    $digitos_lidos  = 0;
    $novo_telefone  = '';
    $novo_ddd       = '';
    $fax            = ereg_replace('[^0-9]', '', $campos[$i]['fax']);
            
    for($j = (strlen($fax) - 1); $j >= 0; $j--) {
        $digito_atual = substr($fax, $j, 1);
        //Vou verificando digito por dígito ...
        if($digitos_lidos <= 7) {
            $novo_telefone.= $digito_atual;
            if($digitos_lidos == 3) $novo_telefone.= '-';
        }else {
            $novo_ddd.= $digito_atual;
        }
        $digitos_lidos++;
    }
   
    $novo_ddd       = strrev($novo_ddd);
    $novo_ddd       = substr($novo_ddd, 1, strlen($novo_ddd));
    $novo_telefone  = strrev($novo_telefone);
    
    if(substr($novo_telefone, 0, 2) <> 70 && substr($novo_telefone, 0, 2) <> 77 && substr($novo_telefone, 0, 2) <> 78 && substr($novo_telefone, 0, 2) <> 79) {
        if(substr($novo_telefone, 0, 1) == 6 || substr($novo_telefone, 0, 1) == 7 || substr($novo_telefone, 0, 1) == 8 || substr($novo_telefone, 0, 1) == 9) {
            $novo_telefone = '9'.$novo_telefone;
        }    
    }
    if($campos[$i]['fax'] == '' && $novo_ddd != '') {
        echo $sql = "UPDATE fornecedores SET ddd_fax = '$novo_ddd' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMIT 1 ";
        bancos::sql($sql);
        echo '<br>';
    } 
        echo $sql = "UPDATE fornecedores SET fax = '$novo_telefone' WHERE id_fornecedor = '".$campos[$i]['id_fornecedor']."' LIMTI 1 ";
        bancos::sql($sql);
        echo '<br><br>';
    
    $digitos_lidos = 0;
}

$sql = "SELECT id_cliente, ddd_com, ddd_fax, telcom, telfax
        FROM clientes
        WHERE id_uf = 1";
$campos = bancos::sql($sql);
$linhas = count($campos);

for($i = 0; $i < $linhas; $i++) {
    $digitos_lidos  = 0;
    $novo_telefone  = '';
    $novo_ddd       = '';
    $telcom          = ereg_replace('[^0-9]', '', $campos[$i]['telcom']);

for($j = (strlen($telcom) - 1); $j >= 0; $j--) {
    $digito_atual = substr($telcom, $j, 1);
    //Vou verificando digito por dígito ...
    if($digitos_lidos <= 7) {
        $novo_telefone.= $digito_atual;
        if($digitos_lidos == 3) $novo_telefone.= '-';
    }else {
        $novo_ddd.= $digito_atual;
    }
    $digitos_lidos++;
}

$novo_ddd       = strrev($novo_ddd);
$novo_ddd       = substr($novo_ddd, 1, strlen($novo_ddd));
$novo_telefone  = strrev($novo_telefone);

if(substr($novo_telefone, 0, 2) <> 70 && substr($novo_telefone, 0, 2) <> 77 && substr($novo_telefone, 0, 2) <> 78 && substr($novo_telefone, 0, 2) <> 79) {
    if(substr($novo_telefone, 0, 1) == 6 || substr($novo_telefone, 0, 1) == 7 || substr($novo_telefone, 0, 1) == 8 || substr($novo_telefone, 0, 1) == 9) {
        $novo_telefone = '9'.$novo_telefone;
    }    
}
if($campos[$i]['ddd_com'] == '' && $novo_ddd != '') {
    echo $sql = "UPDATE clientes SET ddd_com = '$novo_ddd' WHERE id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
    echo '<br>';
} 
    echo $sql = "UPDATE clientes SET telcom = '$novo_telefone' WHERE id_cliente = '".$campos[$i]['id_cliente']."' LIMTI 1 ";
    bancos::sql($sql);
    echo '<br><br>';
/*________________________________________________________________________________________________________________________*/
    $digitos_lidos  = 0;
    $novo_telefone  = '';
    $novo_ddd       = '';
    $telfax          = ereg_replace('[^0-9]', '', $campos[$i]['telfax']);    
    
for($j = (strlen($telfax) - 1); $j >= 0; $j--) {
    $digito_atual = substr($telfax, $j, 1);
    //Vou verificando digito por dígito ...
    if($digitos_lidos <= 7) {
        $novo_telefone.= $digito_atual;
        if($digitos_lidos == 3) $novo_telefone.= '-';
    }else {
        $novo_ddd.= $digito_atual;
    }
    $digitos_lidos++;
}

$novo_ddd       = strrev($novo_ddd);
$novo_ddd       = substr($novo_ddd, 1, strlen($novo_ddd));
$novo_telefone  = strrev($novo_telefone);

if(substr($novo_telefone, 0, 2) <> 70 && substr($novo_telefone, 0, 2) <> 77 && substr($novo_telefone, 0, 2) <> 78 && substr($novo_telefone, 0, 2) <> 79) {
    if(substr($novo_telefone, 0, 1) == 6 || substr($novo_telefone, 0, 1) == 7 || substr($novo_telefone, 0, 1) == 8 || substr($novo_telefone, 0, 1) == 9) {
        $novo_telefone = '9'.$novo_telefone;
    }    
}
if($campos[$i]['ddd_fax'] == '' && $novo_ddd != '') {
    echo $sql = "UPDATE clientes SET ddd_fax = '$novo_ddd' WHERE id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
    echo '<br>';
} 
    echo $sql = "UPDATE clientes SET telfax = '$novo_telefone' WHERE id_cliente = '".$campos[$i]['id_cliente']."' LIMTI 1 ";
    bancos::sql($sql);
    echo '<br><br>';
    
}

 