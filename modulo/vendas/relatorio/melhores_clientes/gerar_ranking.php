<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/melhores_clientes/melhores_clientes.php', '../../../../');

/*Zero todos os índices de Ranking por segurança, de modo que não fique nenhum resíduo de índices errados 
dos que foram gerados anteriormente ...*/
$sql = "UPDATE `clientes` SET `ranking` = '0' ";
bancos::sql($sql);

//Aqui gero um Ranking dos Melhores Clientes em cima do Faturamento "NF(s)" dos últimos 2 anos ...
$sql = "SELECT nfs.id_cliente 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
        WHERE nfs.data_emissao BETWEEN '".(date('Y') - 2).date('-m-d')."' AND '".date('Y-m-d')."' 
        GROUP BY nfs.id_cliente ORDER BY SUM(nfsi.qtde * nfsi.valor_unitario) DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for ($i = 0; $i < $linhas; $i++) {
    $sql = "UPDATE `clientes` SET `ranking` = '".($i + 1)."' WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
    bancos::sql($sql);
}
?>
<Script Language = 'JavaScript'>
    alert('RANKING DO(S) MELHOR(ES) CLIENTE(S) GERADO COM SUCESSO !!!\n\nOBS: ESTE RANKING FOI BASEADO EM CIMA DO FATURAMENTO DOS ÚLTIMOS 2 ANOS !')
    parent.html5Lightbox.finish()
</Script>