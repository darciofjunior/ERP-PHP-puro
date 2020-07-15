<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>CAIXA SECUNDÁRIA ADICIONADA COM SUCESSO.</font>";

/******************************************************************************/
//Se a Caixa Secundária já foi preenchida então ...
if(!empty($_POST['id_produto_acabado'])) {
    //Aqui eu busco o N.º da última caixa secundária que foi inserida no Packing List ...
    $sql = "SELECT `caixa_secundario_numero` 
            FROM `packings_lists_itens` 
            WHERE `id_packing_list` = '$_POST[hdd_packing_list]' ORDER BY id_packing_list_item DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Se ainda não foi inserido nenhuma Caixa, o Sistema já sabe que será a 1ª caixa, do contrário só irá continuar a contagem ...
    $caixa_secundario_numero = (count($campos) == 0) ? 1 : ($campos[0]['caixa_secundario_numero'] + 1);
    
    foreach($_POST['id_produto_acabado'] as $i => $id_produto_acabado) {
        if(!empty($_POST['txt_qtde_packing_list'][$i])) {
            /*Pode ser que sempre insiro o mesmo "id_pedido_venda_item" mais de uma vez até que na mesma
            caixa Secundária, mas até é um problema do Usuário ...*/
            $sql = "INSERT INTO `packings_lists_itens` (`id_packing_list_item`, `id_packing_list`, `id_pedido_venda_item`, `id_produto_acabado`, `id_produto_insumo_secundario`, `caixa_secundario_numero`, `qtde`, `data_sys`) VALUES (NULL, '$_POST[hdd_packing_list]', '".$_POST['id_pedido_venda_item'][$i]."', '$id_produto_acabado', '$_POST[cmb_caixa_secundario]', '$caixa_secundario_numero', '".$_POST['txt_qtde_packing_list'][$i]."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}
/******************************************************************************/

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente             = $_POST['id_cliente'];
    $id_pedido_venda_item   = $_POST['id_pedido_venda_item'];
}else {
    $id_cliente             = $_GET['id_cliente'];
    $id_pedido_venda_item   = $_GET['id_pedido_venda_item'];
}

//Aqui eu verifico se esse Cliente possui algum Packing List em aberto ...
$sql = "SELECT id_packing_list 
        FROM `packings_lists` 
        WHERE `id_cliente` = '$id_cliente' 
        AND `status` = '0' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Não existe nenhum Packing List em aberto ... 
    //Verifico se existe algum Packing List na Base de Dados que ficou sem Cliente ...
    $sql = "SELECT id_packing_list 
            FROM `packings_lists` 
            WHERE `id_cliente` = '0' 
            AND `status` = '0' LIMIT 1 ";
    $campos_packing_sem_cliente = bancos::sql($sql);
    if(count($campos_packing_sem_cliente) == 1) {//Existe Packing List que está sem Cliente, sendo assim reaproveito essa Numeração ...
        $sql = "UPDATE `packings_lists` SET `id_cliente` = '$_GET[id_cliente]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_packing_list` = '".$campos_packing_sem_cliente[0]['id_packing_list']."' LIMIT 1 ";
        bancos::sql($sql);
        $id_packing_list = $campos_packing_sem_cliente[0]['id_packing_list'];
    }else {//Não existe, sendo assim Insiro um Novo Packing List para o Cliente ...
        $sql = "INSERT INTO `packings_lists` (`id_packing_list`, `id_cliente`, `data_sys`) VALUES (NULL, '$_GET[id_cliente]', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_packing_list = bancos::id_registro();
    }
}else {//Já existe Packing List ...
    $id_packing_list = $campos[0]['id_packing_list'];
}

/*Antes de exibir a Tela c/ os itens p/ o usuário manipular, verifico se desses itens selecioandos, ainda existe algum que 
dá p/ incluso no $id_packing_list definido na linha acima ...*/
$exibir_tela_packing_list   = 0;

//Controle p/ não perder a parâmetrização nas demais vezes quando esta tela já foi submetida pelo menos 1 vez ...
if(is_array($id_pedido_venda_item)) {
    $vetor_pedido_venda_item    = $id_pedido_venda_item;
}else {//Controle na 1ª vez quando carregamos a Tela ...
    $vetor_pedido_venda_item    = explode(',', $id_pedido_venda_item);
}

foreach($vetor_pedido_venda_item as $i => $id_pedido_venda_item) {
    //Aqui eu busco dados dos itens que foram selecionados na tela de Itens PA(s) abaixo ...
    $sql = "SELECT `qtde` 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
    $campos_pedidos = bancos::sql($sql);
    
    //Aqui eu busco o quanto que já foi feito "Empacotado" do PA em todas as caixas Secundária do mesmo Packing List ...
    $sql = "SELECT SUM(`qtde`) AS total_qtde_packing_list_pa 
            FROM `packings_lists_itens` 
            WHERE `id_packing_list` = '$id_packing_list' 
            AND `id_produto_acabado` = '$id_pedido_venda_item' ";
    $campos_packing_list_pa = bancos::sql($sql);
    $qtde_restante          = $campos_pedidos[0]['qtde'] - $campos_packing_list_pa[0]['total_qtde_packing_list_pa'];
    if($qtde_restante > 0) {//Só exibo o Item se realmente existe alguma Qtde para ser atrelada ao Packing List ...
        $exibir_tela_packing_list++;
        break;
    }
}
?>
<html>
<head>
<title>.:: Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Secundária ...
    if(!combo('form', 'cmb_caixa_secundario', '', 'SELECIONE UMA CAIXA SECUNDÁRIA !')) {
        return false
    }
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_packing_list[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_packing_list[]'].length)
    }
    var itens_preenchidos = 0
//Aqui eu verifico se foi colocado pelo menos 1 Item na Caixa de Papelão ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('txt_qtde_packing_list'+i).value != '') {//Se estiver preenchido ...
            itens_preenchidos++
            break
        }
    }
    if(itens_preenchidos == 0) {
        alert('DIGITE A QTDE DE PACKING LIST DE PELO MENOS UM ITEM P/ INSERIR NA CAIXA DE PAPELÃO !')
        document.getElementById('txt_qtde_packing_list0').focus()
        return false
    }
//Aqui eu verifico se a Quantidade que foi colocada na caixa é maior do que a Qtde Restante ...
    for(i = 0; i < linhas; i++) {
        var qtde_restante           = eval(document.getElementById('hdd_qtde_restante'+i).value)
        if(document.getElementById('txt_qtde_packing_list'+i).value != '') {//Se estiver preenchido ...
            var qtde_packing_list   = eval(document.getElementById('txt_qtde_packing_list'+i).value)
            if(qtde_packing_list > qtde_restante) {
                alert('QUANTIDADE DE PACKING LIST INVÁLIDA !!!\nQUANTIDADE DE PACKING LIST MAIOR DO QUE A QUANTIDADE RESTANTE !')
                document.getElementById('txt_qtde_packing_list'+i).focus()
                document.getElementById('txt_qtde_packing_list'+i).select()
                return false
            }
        }
    }
}

function copiar_qtde_restante(indice, qtde_restante) {
    document.getElementById('txt_qtde_packing_list'+indice).value = qtde_restante
}

function calcular_peso_total(indice, peso_unitario) {
//Calculo o Peso Total do Item somente da Linha em que o Usuário digitou a Qtde ...
    if(document.getElementById('txt_qtde_packing_list'+indice).value != '') {
        var qtde_packing_list   = document.getElementById('txt_qtde_packing_list'+indice).value
        document.getElementById('hdd_peso_total_item'+indice).value = qtde_packing_list * peso_unitario
        document.getElementById('hdd_peso_total_item'+indice).value = arred(document.getElementById('hdd_peso_total_item'+indice).value, 8, 1)
    }else {
        document.getElementById('hdd_peso_total_item'+indice).value = ''
    }
    
//Calculo do Peso Total de todos os Itens ...
    var peso_total_todos_itens = 0

    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde_packing_list[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_packing_list[]'].length)
    }
    for(i = 0; i < linhas; i++) {
        //Somente das Linhas que estão preenchidas ...
        if(document.getElementById('hdd_peso_total_item'+indice).value != '') {
            peso_total_todos_itens+= eval(strtofloat(document.getElementById('hdd_peso_total_item'+indice).value))
        }
    }
    document.getElementById('hdd_peso_total_todos_itens').value = peso_total_todos_itens
    document.getElementById('hdd_peso_total_todos_itens').value = arred(document.getElementById('hdd_peso_total_todos_itens').value, 8, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='hdd_packing_list' value='<?=$id_packing_list;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_pedido_venda_item' value='<?=$id_pedido_venda_item;?>'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
<?
    if($exibir_tela_packing_list == 0) {//Dos itens selecionados, não existe mais nenhum p/ ser incluído nesse packing list ...
?>
    <tr align='center' class='erro'>
        <td>
            TODO(S) O(S) ITEM(NS) SELECIONADO(S), JÁ FOI(RAM) ADICIONADO(S) NESSE PACKING LIST !
        </td>
    </tr>
<?
    }else {//Se existir pelo menos 1 item ainda que dá p/ ser Incluso no Packing List então exibo a tela abaixo ...
?>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Packing List N.º 
            <font color='yellow'>
                <?=$id_packing_list;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            <!--Lembrando que as Caixas de Madeira também são os PI´s cadastrados no Sistema 
            <select name="cmb_caixa_madeira" title="Selecione uma Caixa de Madeira" class="combo">
            <?
                $sql = "SELECT `id_produto_insumo`, `discriminacao` 
                        FROM `produtos_insumos` 
                        WHERE `discriminacao` LIKE 'CAIXA%MADEIRA%' 
                        AND `ativo` = '1' ORDER BY `discriminacao` ";
                echo combos::combo($sql);
            ?>
            </select>-->
            Caixa(s) Secundária(s): 
            <!--Lembrando que as Caixas Secundária também são os PI´s cadastrados no Sistema -->
            <select name='cmb_caixa_secundario' title='Selecione uma Caixa Secundária' class='combo'>
            <?
                $sql = "SELECT `id_produto_insumo`, `discriminacao` 
                        FROM `produtos_insumos` 
                        WHERE `discriminacao` LIKE 'CAIXA%PAPEL%PAP%' 
                        AND `ativo` = '1' ORDER BY `discriminacao` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Qtde Packing List
        </td>
        <td>
            Qtde Separada
        </td>
        <td>
            Qtde Restante
        </td>
        <td>
            Qtde Pedida
        </td>
        <td>
            Produto
        </td>
        <td>
            Peso Unitário
        </td>
        <td>
            Peso Total
        </td>
        <td>
            N.º Pedido
        </td>
    </tr>
<?
        $indice = 0;
        foreach($vetor_pedido_venda_item as $i => $id_pedido_venda_item) {
            //Aqui eu busco dados dos itens que foram selecionados na tela de Itens PA(s) abaixo ...
            $sql = "SELECT pa.`peso_unitario`, pvi.`id_pedido_venda`, pvi.`id_produto_acabado`, 
                    ovi.`id_produto_acabado_discriminacao`, pvi.`qtde`, 
                    (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale`) AS separada, pvi.`qtde_faturada` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN  `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            $campos_pedidos = bancos::sql($sql);

            //Aqui eu busco o quanto que já foi feito "Empacotado" do PA em todas as caixas de Papelão do mesmo Packing List ...
            $sql = "SELECT SUM(`qtde`) AS total_qtde_packing_list_pa 
                    FROM `packings_lists_itens` 
                    WHERE `id_packing_list` = '$id_packing_list' 
                    AND `id_pedido_venda_item` = '$id_pedido_venda_item' ";
            $campos_packing_list_pa = bancos::sql($sql);
            $qtde_restante          = $campos_pedidos[0]['qtde'] - $campos_packing_list_pa[0]['total_qtde_packing_list_pa'];
            if($qtde_restante > 0) {//Só exibo o Item se realmente existe alguma Qtde para ser atrelada ao Packing List ...
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='text' name='txt_qtde_packing_list[]' id='txt_qtde_packing_list<?=$indice;?>' title='Digite a Quantidade do Packing List' maxlength='6' size='9' onkeyup="verifica(this, 'aceita', 'numeros', '', event); if(this.value == 0) {this.value = ''};calcular_peso_total('<?=$indice;?>', '<?=$campos_pedidos[0]['peso_unitario'];?>')" class='caixadetexto'>
        </td>
        <td>
        <?
            if($campos_pedidos[0]['separada'] - $campos_pedidos[0]['qtde_faturada'] > 0) echo intval($campos_pedidos[0]['separada'] - $campos_pedidos[0]['qtde_faturada']);
        ?>
        </td>
        <td>
            <a href="javascript:copiar_qtde_restante('<?=$indice;?>', '<?=$qtde_restante;?>')" title='Copia Restante p/ Qtde Packing List' style='cursor:help' class='link'>
                <?=$qtde_restante;?>
            </a>
        </td>
        <td>
            <?=intval($campos_pedidos[0]['qtde']);?>
            <input type='hidden' name='hdd_qtde_restante[]' id='hdd_qtde_restante<?=$indice;?>' value='<?=$qtde_restante;?>'>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos_pedidos[0]['id_produto_acabado'], 0, '', '', $campos_pedidos[0]['id_produto_acabado_discriminacao']);?>
        </td>
        <td>
            <!--Esses parâmetros tela1 serve para o pop-up fazer a atualização na tela de baixo-->
            <a href="javascript:nova_janela('../../../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$campos_pedidos[0]['id_produto_acabado'];?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Atualizar Peso do Produto' class='link'>
                <?=number_format($campos_pedidos[0]['peso_unitario'], 8, ',', '.');?>
            </a>
            <!--**********************Controle de Tela**********************-->
            <input type='hidden' name='hdd_peso_unitario[]' id='hdd_peso_unitario<?=$indice;?>' value='<?=$campos_pedidos[0]['peso_unitario'];?>'>
        </td>
        <td>
            <input type='text' name='hdd_peso_total_item[]' id='hdd_peso_total_item<?=$indice;?>' maxlength='6' size='9' class='textdisabled' disabled>
        </td>
        <td>
            <?=$campos_pedidos[0]['id_pedido_venda'];?>
            <input type='hidden' name='id_produto_acabado[]' id='id_produto_acabado<?=$indice;?>' value='<?=$campos_pedidos[0]['id_produto_acabado'];?>'>
            <input type='hidden' name='id_pedido_venda_item[]' id='id_pedido_venda_item<?=$indice;?>' value='<?=$id_pedido_venda_item;?>'>
        </td>
    </tr>
<?
                $indice++;
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(parent)' class='botao'>
        </td>
        <td>
            <input type='text' name='hdd_peso_total_todos_itens[]' id='hdd_peso_total_todos_itens' value='0,00000000' maxlength='6' size='9' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
<?
    /**************************************************************************/
    //Verifico se existe pelo menos 1 Item já incluído nesse Packing List ...
    $sql = "SELECT `id_packing_list_item` 
            FROM `packings_lists_itens` 
            WHERE `id_packing_list` = '$id_packing_list' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
?>
<center>
    <p><iframe name='' src='relatorio.php?id_packing_list=<?=$id_packing_list;?>' width='95%' height='70%' class='caixadetexto'>
</center>
<?
    }
    /**************************************************************************/
?>
</form>
</body>
</html>