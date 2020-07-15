<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, p/ que esse seja executado depende de o usuário ter Permissão 
no menu do Pedido de Vendas, mais para o pessoal do Estoque que tem acesso pelo Gerenciar não é necessário se ter Permissão em Vendas ...*/
if($nao_verificar_sessao != 1) {
    segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');
}

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NÃO POSSO ALTERAR ESTE VALOR !\n POIS O ITEM NÃO POSSUI PENDÊNCIA SUFICIENTE PARA SE ADAPTAR COM O VALOR CONTIDO NA LÓGICA CORRENTE .</font>";

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pedido_venda        = $_POST['id_pedido_venda'];
    $nao_verificar_sessao   = $_POST['nao_verificar_sessao'];
    $posicao                = $_POST['posicao'];
}else {
    $id_pedido_venda        = $_GET['id_pedido_venda'];
    $nao_verificar_sessao   = $_GET['nao_verificar_sessao'];
    $posicao                = $_GET['posicao'];
}

if(empty($posicao)) $posicao = 1;//Macete por causa da paginacao do Pop-UP ...

if($passo == 1) {
    $sql = "SELECT `id_orcamento_venda_item`, `qtde`, `qtde_devolvida`, `qtde_pendente` 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda_item` = '$_POST[id_pedido_venda_item]' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_orcamento_venda_item    = $campos[0]['id_orcamento_venda_item'];
    $qtde_pedida_antiga         = $campos[0]['qtde'];
    $qtde_devolvida             = $campos[0]['qtde_devolvida'];
    $qtde_pendente              = $campos[0]['qtde_pendente'];
    $diferenca                  = $_POST['txt_qtde'] - $qtde_pedida_antiga;//Para saber a <> entre a qtde_inicial para qtde_nova ...

    if($diferenca > 0) {
        //Atualizo o Pedido com a Nova Quantidade digitada pelo Usuário ...
        $sql = "UPDATE `pedidos_vendas_itens` SET `qtde` = '$_POST[txt_qtde]', `qtde_pendente` = `qtde_pendente` + '$diferenca' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        bancos::sql($sql);

        //Atualizo o campo Qtde no Orçamento ...
        $sql = "UPDATE `orcamentos_vendas_itens` SET `qtde` = `qtde` + $diferenca WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
        
        faturamentos::pedidos_vendas_status($_POST['id_pedido_venda_item']);
        estoque_acabado::controle_pedidos_vendas_itens($_POST['id_pedido_venda_item'], 4);//É só para Controle de Importação dos Itens do Orçamento ...
        
        $valor = 1;
    }else {//Se for menor significa q eu irei retirar então eu verifico se pode mesmo ...
        if($qtde_pendente >= abs($diferenca)) {//se for eu posso alterar para menos as qtdes
            //Atualizo o Pedido com a Nova Quantidade digitada pelo Usuário ...
            $sql = "UPDATE `pedidos_vendas_itens` SET `qtde` = '$_POST[txt_qtde]', `qtde_pendente` = `qtde_pendente` + '$diferenca' WHERE `id_pedido_venda_item` = '$_POST[id_pedido_venda_item]' LIMIT 1 ";
            bancos::sql($sql);
            
            //Atualizo o campo Qtde no Orçamento ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `qtde` = `qtde` + '$diferenca' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            bancos::sql($sql);
            
            faturamentos::pedidos_vendas_status($_POST['id_pedido_venda_item']);
            estoque_acabado::controle_pedidos_vendas_itens($_POST['id_pedido_venda_item'], 4);//É só para Controle de Importação dos Itens do Orçamento ...
            
            $valor = 1;
        }else {
            $valor = 2;
        }
    }
}

//Seleciona a qtde de itens que existe no orçamento
$sql = "SELECT COUNT(`id_pedido_venda_item`) AS qtde_itens 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Significa q está sendo acessado do Mód. de Compras, então só mostra P.A. do Tipo Componentes*/
$sql = "SELECT pvi.`id_pedido_venda_item`, pvi.`id_produto_acabado`, pvi.`qtde`, u.`sigla` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
        ORDER BY pvi.`id_orcamento_venda_item` ";
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
?>
<html>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar(posicao) {
//Quantidade ...
    if(document.form.txt_qtde.value == '') {
        alert('DIGITE A QUANTIDADE !')
        documen.form.txt_qtde.focus()
        return false
    }
//Quantidade não pode ser Zero ...
    if(document.form.txt_qtde.value == 0) {
        alert('VOCÊ NÃO PODE ZERAR ESSE ITEM !\nPEÇA PARA O GERENTE EXCLUIR O ITEM !')
        document.form.txt_qtde.focus()
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP ...
    document.form.nao_atualizar.value = 1
//Recupera a posição corrente no hidden, para não dar erro de paginação ...
    document.form.posicao.value = posicao
    limpeza_moeda('form', 'txt_qtde, ')
//Submetendo o Formulário ...
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(window.opener.parent.itens) == 'object') {
            window.opener.parent.itens.document.form.submit()
        }else {
            window.opener.location = window.opener.location.href
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>')">
<!--*****************Controles de Tela*****************-->
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='nao_verificar_sessao' value='<?=$nao_verificar_sessao;?>'>
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='id_pedido_venda_item' value='<?=$campos[0]['id_pedido_venda_item'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--***************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Quantidade do Pedido
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow'>
                <b>Produto: </b>
            </font>
            <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado'], 0, 1, 1);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <?
                /************************************************************/
                /****Tratamento com as Casas Decimais do campo Quantidade****/
                /************************************************************/
                if($sigla == 'KG') {//Essa é a única sigla que permite trabalharmos com Qtde Decimais ...
                    $onkeyup            = "verifica(this, 'moeda_especial', 1, '', event) ";
                    $qtde_apresentar    = number_format($campos[0]['qtde'], 1, ',', '.');
                }else {
                    $onkeyup            = "verifica(this, 'aceita', 'numeros', '', event) ";
                    $qtde_apresentar    = (integer)$campos[0]['qtde'];
                }
                /************************************************************/
            ?>
            <input type='text' name='txt_qtde' value='<?=$qtde_apresentar;?>' title='Digite o Preço Líquido Faturado' onkeyup="<?=$onkeyup;?>" size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='javascript:validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='javascript:validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>