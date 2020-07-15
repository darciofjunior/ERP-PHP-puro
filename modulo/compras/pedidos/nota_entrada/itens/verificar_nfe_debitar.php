<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/ajax.php');

/*Verifico se jб existe alguma "Nota Fiscal Debitar" desse Fornecedor, Nъmero e Data de Emissгo 
passados por parвmetro ...

Observaзгo: Trago a mais recente ...*/
$sql = "SELECT `id_nfe`, `num_nota` 
        FROM `nfe` 
        WHERE `id_fornecedor` = '$_POST[id_fornecedor]' 
        AND `num_nota` = '$_POST[num_nota]' 
        AND SUBSTRING(`data_emissao`, 1, 10) = '$_POST[data_emissao]' ORDER BY `id_nfe` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
/*Se nгo existir eu posso nenhuma NF, entгo significa que eu posso estar abrindo essa Tela como sendo 
um Iframe normalmente p/ a Inclusгo da Nota Fiscal*/
if(count($campos) == 0) {
    echo 'N';
}else {//Jб existe a NF ...
    echo $campos[0]['id_nfe'].'|'.$campos[0]['num_nota'];
}
?>