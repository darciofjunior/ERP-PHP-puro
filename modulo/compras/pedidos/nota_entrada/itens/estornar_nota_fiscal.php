<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

//Antes de Estornar a Nota Fiscal, eu verifico se a mesma j� foi Importada pelo Depto. Financeiro "Contas � Pagar" ...
$sql = "SELECT id_conta_apagar 
        FROM `contas_apagares` 
        WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos_contas_apagar = bancos::sql($sql);
if(count($campos_contas_apagar) == 0) {//Nota Fiscal n�o est� Importada no Financeiro, sendo assim posso Estorn�-la ...
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
}else {//Nota Fiscal j� Importada no Financeiro, n�o pode ser Estornada ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NOTA FISCAL N�O PODE SER ESTORNADA !!!\n\nNOTA FISCAL FOI IMPORTADA PELO DEPTO. FINANCEIRO !')
        window.close()
    </Script>
<?  
}
?>