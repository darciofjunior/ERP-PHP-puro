<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

//Antes de Estornar a Nota Fiscal, eu verifico se a mesma já foi Importada pelo Depto. Financeiro "Contas à Pagar" ...
$sql = "SELECT id_conta_apagar 
        FROM `contas_apagares` 
        WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos_contas_apagar = bancos::sql($sql);
if(count($campos_contas_apagar) == 0) {//Nota Fiscal não está Importada no Financeiro, sendo assim posso Estorná-la ...
    $sql = "UPDATE `nfe` SET `situacao` = '0' WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('NOTA FISCAL ESTORNADA COM SUCESSO !')
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}else {//Nota Fiscal já Importada no Financeiro, não pode ser Estornada ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NOTA FISCAL NÃO PODE SER ESTORNADA !!!\n\nNOTA FISCAL FOI IMPORTADA PELO DEPTO. FINANCEIRO !')
        window.close()
    </Script>
<?  
}
?>