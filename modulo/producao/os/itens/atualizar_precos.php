<?
require('../../../../lib/segurancas.php');
require('../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

/****************************************Controle**********************************************/
//Verifico se essa OS já está importada p/ Pedido ...
$sql = "SELECT `id_pedido` 
        FROM `oss` 
        WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if($campos_os[0]['id_pedido'] != 0) {//Essa O.S. já foi importada para Pedido, sendo assim não possu atualizar nenhum Item
    $mensagem = 'NENHUM ITEM DE O.S. PODE SER ATUALIZADO !\nESTÁ O.S. JÁ FOI IMPORTADA P/ PEDIDO !';
}else {//Essa O.S. ainda está em aberto, então posso atualizar os Itens dela normalmente ...
/**********************************************************************************************/
/******Aqui é todo o Controle para Atualização dos Preços de Itens da OS e Cabeçalho da OS******/
//Nessa função eu busco quais os PI(s) de OS que estão com os Preços em relação a Lista de Preço
    $retorno = producao::conferir_precos_os($_GET['id_os']);
    $id_produtos_insumos = count($retorno['id_produtos_insumos']);
//Não retornou nenhum item da conferência de Preços, então já retorno essa logo mensagem ...
    if($id_produtos_insumos == 0) {//Se não encontrar nenhum PI, então há nada a ser feito 
        $mensagem = 'NÃO HÁ ITEM(NS) DE O.S. P/ SER(EM) ATUALIZADO(S) !';
//Se retornar algum item da conferência de Preços, então chamo a função para atualização dos Preços ...
    }else {//Pego os PI(s) e passo por Parâmetro estes que precisam ser atualizados
        $id_produtos_insumos = $retorno['id_produtos_insumos'];
        $retorno = producao::atualizar_precos_os($_GET['id_os'], $id_produtos_insumos);
        if($retorno == 1) {//Significa que atualizou algum Item
            $mensagem = 'TODO(S) O(S) ITEM(NS) DE O.S. QUE ESTAVA(M) COM O(S) PREÇO(S) INCOMPATÍVEL(IS) COM O DA LISTA DE PREÇO, FORAM ATUALIZADO(S) COM SUCESSO !';
        }else {//Não atualizou nenhum Item, porque a O.S. já foi importada
            $mensagem = 'NENHUM ITEM DE O.S. PODE SER ATUALIZADO !\nESTÁ O.S. JÁ FOI IMPORTADA P/ PEDIDO !';
        }
    }
/***********************************************************************************************/
}
?>
<Script Language = 'JavaScript'>
    alert('<?=$mensagem;?>')
    window.opener.parent.itens.document.form.submit()
    window.opener.parent.rodape.document.form.submit()
    window.close()
</Script>