<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

//Aki eu verifico quem é o Cliente deste Pedido, p/ ver se estão preenc. corretamente os dados de Endereço
$sql = "SELECT c.id_pais, pv.id_cliente, pv.liberado 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
        WHERE pv.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos     = bancos::sql($sql);

//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
$cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($campos[0]['id_cliente']);

//Se o Pedido está liberado, o usuário não pode mais manipular os itens de Pedido, não pode alterar, nem excluir ...
if($campos[0]['liberado'] == 1) {
    $controle_botao_alterar = "class='disabled' onclick='JavaScript:alert(".'"PEDIDO LIBERADO !"'.")'";
    /*Se o funcionário logado for Roberto 62, Dárcio 98 porque programa ou Wilson Nishimura 136, estes sempre poderão 
    estar excluindo itens do Pedido de Vendas mesmo que este esteja liberado ... Agora o porque desse controle: 
     
    Às vezes o Cliente possui Pendência que não dá valor, exemplo R$ 200,00 SGD e R$ 100,00 NF e sendo assim precisamos 
    mudar a empresa de um dos 2 pedidos p/ que se possa faturar ambos numa Nota Fiscal só, 
    isto também acontece muito em Vale ...*/
    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
        $controle_botao_excluir = "class='botao' ";
    }else {
        $controle_botao_excluir = "class='disabled' onclick='JavaScript:alert(".'"PEDIDO LIBERADO !"'.")'";
    }
    $controle_botao_alterar = "class='disabled' onclick='JavaScript:alert(".'"PEDIDO LIBERADO !"'.")'";
}else {
    $controle_botao_alterar = "class='botao' ";
    $controle_botao_excluir = "class='botao' ";
}

//Verifico se tem pelo menos um item de pedido, para poder habilitar os botões alterar e excluir
$sql = "SELECT id_pedido_venda_item 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
?>
<html>
<head>
<title>.:: Rodapé de Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function alterar_item() {
    var option  = 0
    if(typeof(parent.itens.document.form) == 'undefined') {
        return false
    }else {
        elemento = parent.itens.document.form
    }
    if(elemento.checked == true && elemento.type == 'radio') return true
    if(elemento.checked == false) {
        alert('SELECIONE UM ITEM !')
        return false
    }
    for(var i = 0; i < elemento.length; i++) {
        if(elemento[i].checked == true && elemento[i].type == 'radio') option ++
    }
    if(option == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        for(var i = 0; i < elemento.length; i++) {
            if (elemento[i].checked == true && elemento[i].type == 'radio') {
                var posicao = (i + 1)
                break;
            }
        }
        nova_janela('alterar.php?id_pedido_venda=<?=$id_pedido_venda;?>&posicao='+posicao, 'POP', '', '', '', '', 450, 850, 'c', 'c')
    }
}
    
function incluir_itens() {
    alert('A INCLUSÃO DE ITENS SÓ PODE SER FEITA DENTRO DO ORÇAMENTO !')
    return false
    var cadastro_cliente_incompleto = eval('<?=$cadastro_cliente_incompleto;?>')
    if(cadastro_cliente_incompleto == 1) {//Está incompleto
        alert('O CADASTRO DESTE CLIENTE ESTÁ INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
    }else {//Está tudo OK
        nova_janela('incluir.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function clique_automatico_cabecalho() {
    var clique_automatico_cabecalho = '<?=$clique_automatico_cabecalho;?>'
    if(clique_automatico_cabecalho == 1) {
        document.form.cmd_cabecalho.onclick()
    }
}

function imprimir() {
    var id_pais                 = eval('<?=$campos[0]['id_pais'];?>')

    if(id_pais == 31) {//Cliente do Brasil, exibo o relatório Nacional ...
        nova_janela('opcoes_de_impressao.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'OPCOES_IMPRESSAO', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Do contrário exibo o relatório de Exportação ...
        nova_janela('relatorio/relatorio_exportacao.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'CONSULTAR', 'F')
    }
}
</Script>
</head>
<?
/*Esse parâmetro -> $clique_automatico_cabecalho

Dispara um clique automático no botão de Alterar Cabeçalho, assim que acaba de ser
clonado um novo da Opção -> Outras Opções*/

/*Esse parâmetro -> $parametro_velho

É uma jogada para que não dê erro de paginação por causa da minha nova paginação*/

//Apenas na 1ª vez que esse parâmetro será vazio ...
$parametro_velho = (empty($parametro_velho)) ? $parametro : $parametro_velho;
?>
<body onload='clique_automatico_cabecalho()'>
<form name='form'>
<!--********************Controle de Tela********************-->
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho;?>'>
<!--********************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.parent.location = 'consultar.php<?=$parametro_velho;?>'" class='botao'>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho' title='Cabe&ccedil;alho' onclick="nova_janela('../alterar_cabecalho.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <input type='button' name='cmd_incluir' value='Incluir Itens' title='Incluir Itens' onclick='incluir_itens()' class='textdisabled'>
<?
    if($linhas_itens > 0) {
?>
        <input type='button' name='cmd_alterar' <?=$controle_botao_alterar;?> value='Alterar Item' title='Alterar Item' onclick='alterar_item()' class='botao'>
        <input type='button' name='cmd_excluir' <?=$controle_botao_excluir;?> value='Excluir Item(ns)' title='Excluir Item(ns)' onclick="nova_janela('excluir_itens.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <input type='button' name='cmd_outras' value='Outras Opções' title='Outras Opções' onclick="nova_janela('outras_opcoes.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' class='botao'>
        <!--Abro o Espelho do Cliente "Histórico de Vendas" por Família que é o modo q vem + completo ...-->
        <?
            //Aqui eu busco o id_representante através do Funcionário que está logado ...
            $sql = "SELECT id_representante 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
        ?>
        <input type='button' name='cmd_imprimir_espelho_produtos' value='Imprimir Espelho de Produtos' title='Imprimir Espelho de Produtos' onclick="nova_janela('../../relatorio/projetar_espelho_produtos/relatorio.php?cmb_tipo_relatorio=familia&cmb_representante=<?=$campos_representante[0]['id_representante'];?>&cmb_cliente=<?=$campos[0]['id_cliente']?>&pop_up=1', 'IMPRIMIR_ESPELHO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color: #D55C21' class='botao'>
        <input type='button' name='cmd_nfs_atreladas' value='NFs Atreladas' title='NFs Atreladas' onclick="nova_janela('relatorio/nfs_atreladas.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'POP', '', '', '', '', 320, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:brown' class='botao'>
        <input type='button' name='cmd_total_produtos_mlg_por_divisao' value='Total Produtos MLG por Divis&atilde;o' title='Total Produtos MLG por Divis&atilde;o' onclick="nova_janela('/erp/albafer/modulo/vendas/total_produtos_mlg_por_divisao.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:black' class='botao'>
<?
    }
?>
    </td>
</table>
</form>
</body>
</html>