<?
require('../../../../lib/segurancas.php');
require('../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

/****************************************Controle**********************************************/
//Verifico se essa OS j� est� importada p/ Pedido ...
$sql = "SELECT `id_pedido` 
        FROM `oss` 
        WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if($campos_os[0]['id_pedido'] != 0) {//Essa O.S. j� foi importada para Pedido, sendo assim n�o possu atualizar nenhum Item
    $mensagem = 'NENHUM ITEM DE O.S. PODE SER ATUALIZADO !\nEST� O.S. J� FOI IMPORTADA P/ PEDIDO !';
}else {//Essa O.S. ainda est� em aberto, ent�o posso atualizar os Itens dela normalmente ...
/**********************************************************************************************/
/******Aqui � todo o Controle para Atualiza��o dos Pre�os de Itens da OS e Cabe�alho da OS******/
//Nessa fun��o eu busco quais os PI(s) de OS que est�o com os Pre�os em rela��o a Lista de Pre�o
    $retorno = producao::conferir_precos_os($_GET['id_os']);
    $id_produtos_insumos = count($retorno['id_produtos_insumos']);
//N�o retornou nenhum item da confer�ncia de Pre�os, ent�o j� retorno essa logo mensagem ...
    if($id_produtos_insumos == 0) {//Se n�o encontrar nenhum PI, ent�o h� nada a ser feito 
        $mensagem = 'N�O H� ITEM(NS) DE O.S. P/ SER(EM) ATUALIZADO(S) !';
//Se retornar algum item da confer�ncia de Pre�os, ent�o chamo a fun��o para atualiza��o dos Pre�os ...
    }else {//Pego os PI(s) e passo por Par�metro estes que precisam ser atualizados
        $id_produtos_insumos = $retorno['id_produtos_insumos'];
        $retorno = producao::atualizar_precos_os($_GET['id_os'], $id_produtos_insumos);
        if($retorno == 1) {//Significa que atualizou algum Item
            $mensagem = 'TODO(S) O(S) ITEM(NS) DE O.S. QUE ESTAVA(M) COM O(S) PRE�O(S) INCOMPAT�VEL(IS) COM O DA LISTA DE PRE�O, FORAM ATUALIZADO(S) COM SUCESSO !';
        }else {//N�o atualizou nenhum Item, porque a O.S. j� foi importada
            $mensagem = 'NENHUM ITEM DE O.S. PODE SER ATUALIZADO !\nEST� O.S. J� FOI IMPORTADA P/ PEDIDO !';
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