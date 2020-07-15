<?
require('../../lib/segurancas.php');
if(empty($indice)) $indice = 0;

//Busca todos os Clientes Ativos cadastrados no ERP ...
$sql = "SELECT COUNT(`id_cliente`) AS total_registro 
        FROM `clientes` 
        WHERE `ativo` = '1' 
        AND `cnpj_cpf` <> '' ";
$campos_total = bancos::sql($sql);
$total_registro = $campos_total[0]['total_registro'];

//P/ não ficar em loop infinito ...
if($total_registro == $indice) exit;

$sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente, `cnpj_cpf`, `email`, `ativo` 
        FROM `clientes` 
        WHERE `ativo` = '1' 
        AND `cnpj_cpf` <> '' ";
$campos 	= bancos::sql($sql, $indice, 1);
$cliente 	= str_replace("'", '', $campos[0]['cliente']);
$cnpj_cpf       = $campos[0]['cnpj_cpf'];

echo $cnpj_cpf;
echo '<br><br><br>';
$email			= $campos[0]['email'];
$ativo			= $campos[0]['ativo'];
/******************************************************************/
/***********************Conexão com o Portal***********************/
/******************************************************************/
//Site em que está hospedado as NFes e Danfes do(s) Cliente(s)
$host = mysql_connect('187.45.196.216', 'grupoalbafer1', 'd4rc10');
mysql_select_db('grupoalbafer1', $host);
//Verifico se esse Cliente já existe no Portal ...
$sql = "SELECT id_cliente 
        FROM `clientes` 
        WHERE `cnpj_cpf` = '$cnpj_cpf' LIMIT 1 ";
$campos_cliente = mysql_query($sql);
if(mysql_num_rows($campos_cliente) == 0) {//Significa que este USUÁRIO não existe no Portal e sendo assim vou add ele ...
    //Add o Cliente ...
    $sql = "INSERT INTO `clientes` (`id_cliente`, `cliente`, `cnpj_cpf`, `email`) VALUES (NULL, '$cliente', '$cnpj_cpf', '$email') ";
    echo $sql.'<br>';
    echo mysql_query($sql) or die(mysql_error());
    $id_cliente = mysql_insert_id();

    if($id_cliente == 0) exit('PROBLEMA NO CLIENTE '.$sql);

    //Add um usuário para o Portal ...
    $senha = rand(0, 999999);//Senha randômica
    for($i = 0; $i < 5; $i++) $senha = strrev(base64_encode($senha));//Criptografia da Senha ...
    $sql = "INSERT INTO `logins` (`id_cliente`, `login`, `senha`) VALUES ('$id_cliente', '$cnpj_cpf', '$senha') ";
    mysql_query($sql);
}else {//Significa que este CLIENTE já existe no Portal e sendo assim vou alterar ele ...
    $sql = "UPDATE `clientes` SET `cliente` = '$cliente', `email` = '$email', `ativo` = '$ativo' WHERE `id_cliente` = '".mysql_result($campos_cliente, 0, 'id_cliente')."' LIMIT 1 ";
}
/******************************************************************/
?>
<Script Language = 'JavaScript'>
//Aqui eu já passo o índice do próximo ...
    window.location = 'script_clientes_portal.php?indice=<?=++$indice;?>'
</Script>