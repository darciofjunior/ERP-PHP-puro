<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/ajax.php');

/*Verifico se j� existe alguma "Nota Fiscal Debitar" desse Fornecedor, N�mero e Data de Emiss�o 
passados por par�metro ...

Observa��o: Trago a mais recente ...*/
$sql = "SELECT `id_nfe`, `num_nota` 
        FROM `nfe` 
        WHERE `id_fornecedor` = '$_POST[id_fornecedor]' 
        AND `num_nota` = '$_POST[num_nota]' 
        AND SUBSTRING(`data_emissao`, 1, 10) = '$_POST[data_emissao]' ORDER BY `id_nfe` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
/*Se n�o existir eu posso nenhuma NF, ent�o significa que eu posso estar abrindo essa Tela como sendo 
um Iframe normalmente p/ a Inclus�o da Nota Fiscal*/
if(count($campos) == 0) {
    echo 'N';
}else {//J� existe a NF ...
    echo $campos[0]['id_nfe'].'|'.$campos[0]['num_nota'];
}
?>