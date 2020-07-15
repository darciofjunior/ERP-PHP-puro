<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
/********************************Desatrela o PI do Fornecedor********************************/
//Aqui Desatrela o Fornecedor de um Produto Insumo específico selecionado pelo Usuário ...
    if(!empty($_GET['id_fornecedor_prod_insumo'])) {
//Aqui é para não furar o SQL
        if($_GET['id_fornecedor_prod_insumo'] == '') $_GET['id_fornecedor_prod_insumo'] = 0;
//Além de eu desatrelar o Fornecedor do PI, eu também já zero os preços deste Fornec na lista de Preço ...
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '0.00', `preco_faturado_export` = '0.00', `ativo` = '0' WHERE `id_fornecedor_prod_insumo` = '$_GET[id_fornecedor_prod_insumo]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('PRODUTO INSUMO EXCLUÍDO COM SUCESSO P/ ESTE FORNECEDOR !')
    </Script>
<?
    }
/********************************************************************************************/
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_fornecedor          = $_POST['id_fornecedor'];
        $chkt_nao_mostrar_esp   = $_POST['chkt_nao_mostrar_esp'];
        $txt_referencia_pi      = $_POST['txt_referencia_pi'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
        $txt_referencia_pa      = $_POST['txt_referencia_pa'];
    }else {
        $id_fornecedor          = $_GET['id_fornecedor'];
        $chkt_nao_mostrar_esp   = $_GET['chkt_nao_mostrar_esp'];
        $txt_referencia_pi      = $_GET['txt_referencia_pi'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
        $txt_referencia_pa      = $_GET['txt_referencia_pa'];
    }
//Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % como caracter ...
    $txt_discriminacao      = str_replace('|', '%', $txt_discriminacao);
/*Se estiver marcada a opção de Não Mostrar Esp, então não mostro os PA(s) que são do Tipo ESP do 
Fornecedor Corrente, então nesse SQL, eu trago somente os PA(s) que são ESP p/ ignorar + abaixo ...*/
    if(!empty($chkt_nao_mostrar_esp)) {
        $sql = "SELECT pa.id_produto_insumo 
                FROM `produtos_acabados` pa 
                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.`id_fornecedor` = '$id_fornecedor' 
                WHERE pa.`referencia` = 'ESP' ";
        $campos_esp = bancos::sql($sql);
        $linhas_esp = count($campos_esp);
        if($linhas_esp > 0) {
            for($i = 0; $i < $linhas_esp; $i++) $id_produtos_insumos_esp.= $campos_esp[$i]['id_produto_insumo'].', ';
            $id_produtos_insumos_esp = substr($id_produtos_insumos_esp, 0, strlen($id_produtos_insumos_esp) - 2);
            $condicao_esp = " AND pi.id_produto_insumo NOT IN ($id_produtos_insumos_esp) ";
        }
    }
//Se o usuário fez consulta por Referência do P.A. ...
    if(!empty($txt_referencia_pa)) {
        $sql = "SELECT id_produto_insumo 
                FROM `produtos_acabados` 
                WHERE `referencia` LIKE '%$txt_referencia_pa%' 
                AND `id_produto_insumo` > '0' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= $campos[$i]['id_produto_insumo'].', ';
            $id_produtos_insumos = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 2);
            $condicao_pa = " AND pi.id_produto_insumo IN ($id_produtos_insumos) ";
        }else {
            //Faço esse Macete de Propósito, p/ que não seja retornado nenhum PI mesmo na query abaixo ...
            $condicao_pa = " AND pi.id_produto_insumo = '0' ";
        }
    }
        
    $sql = "SELECT fpi.id_fornecedor_prod_insumo, pi.discriminacao, pi.id_produto_insumo, g.nome, g.referencia 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo AND pi.`ativo` = '1' AND pi.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_pa $condicao_esp 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.referencia LIKE '%$txt_referencia_pi%' 
            WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
            AND fpi.`ativo` = '1' ORDER BY pi.discriminacao ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        var nao_perguntar_novamente = eval('<?=$nao_perguntar_novamente;?>')
    /*Significa que já foi feita uma pergunta referente ao Filtro anteriormente e sendo assim
    só irá redirecionar p/ a Tela de Filtro novamente ...*/
        if(nao_perguntar_novamente == 1) {
            window.location = 'consultar_produtos.php?id_fornecedor=<?=$id_fornecedor;?>&valor=1'
        }else {
    /*Se não foi encontrado nenhum P.A. pelo filtro normal, então o Sistema pergunta p/ o usuário 
    se ele deseja visualizar os ESP(s) de acordo com o Filtro que ele fez ...*/
            var resposta = confirm('DESEJA CONSULTAR OS ESPECIAIS ?')
            if(resposta == true) {//Irá manter o Filtro do Usuário, acrescentando apenas a opção de Especiais ...
            <?
    /*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
    como caracter ...*/
                $txt_discriminacao = str_replace('%', '|', $txt_discriminacao);
            ?>
                window.location = 'consultar_produtos.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&txt_referencia_pi=<?=$txt_referencia_pi;?>&txt_discriminacao=<?=$txt_discriminacao?>&txt_referencia_pa=<?=$txt_referencia_pa;?>&chkt_nao_mostrar_esp=0&nao_perguntar_novamente=1'
            }else {
                window.location = 'consultar_produtos.php?id_fornecedor=<?=$id_fornecedor;?>&valor=1'
            }
        }
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Lista de Preço(s) - Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements, id_produtos_insumos = ''
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    //Aqui eu verifico se pelo menos um Produto Insumo está selecionado ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_produto_insumo[]' && elementos[i].checked) id_produtos_insumos+= elementos[i].value + ', '
        }
    }
    id_produtos_insumos = id_produtos_insumos.substr(0, id_produtos_insumos.length - 2)

    window.location = 'itens.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos='+id_produtos_insumos
}

function excluir_fornecedor(id_fornecedor_prod_insumo, pode_excluir_fornec) {
/*Significa que este Fornecedor Corrente é o Fornecedor Default, sendo assim, não posso estar excluindo
esse fornecedor desse PI*/
    if(pode_excluir_fornec == 1) {
        alert('ESSE FORNECEDOR NÃO PODE SER DESATRELADO !\nDEVIDO ESTE SER O FORNECEDOR DEFAULT DESTE PRODUTO INSUMO !!!')
//Não é o Fornecedor Default, então posso estar excluindo esse fornecedor normalmente desse PI
    }else {
        var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE FORNECEDOR DESSE PRODUTO INSUMO ?')
        if(resposta == true) window.location = 'consultar_produtos.php<?=$parametro;?>&id_fornecedor_prod_insumo='+id_fornecedor_prod_insumo
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Lista de Preço(s) - Produto(s) Insumo(s) do Fornecedor => 
            <font color='yellow'>
            <?
                //Busca da Razão Social do Fornecedor Corrente ...
                $sql = "SELECT razaosocial 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos_razao = bancos::sql($sql);
                echo $campos_razao[0]['razaosocial'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo
        </td>
        <td>
            Referência PI
        </td>
        <td>
            Referência PA
        </td>
        <td>
            Discriminação
        </td>
        <td>
            <input type='checkbox' name='chkt_todos' title='Selecionar Todos' onClick="selecionar('form', 'chkt_todos', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            /*Aqui eu verifico quem é o Fornecedor da Última Compra deste Produto Insumo ...
            Vou utilizar esse id + abaixo p/ fazer algumas comparações ...*/
            $sql = "SELECT id_fornecedor_default 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor_default` > '0' 
                    AND `ativo` = '1' ";
            $campos_default         = bancos::sql($sql);
            $id_fornecedor_default  = $campos_default[0]['id_fornecedor_default'];
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_todos', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
        <?
            $sql = "SELECT referencia 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pa = bancos::sql($sql);
            echo $campos_pa[0]['referencia'];
        ?>
        </td>
        <td align='left'>
        <?
//Significa que este Fornecedor do Loop é o Fornecedor Default, e sendo assim eu não posso estar excluindo este fornecedor ...
            $pode_excluir_fornec = ($id_fornecedor_default == $id_fornecedor) ? 1 : 0;
        ?>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Fornecedor' alt='Excluir Fornecedor' onclick="excluir_fornecedor('<?=$campos[$i]['id_fornecedor_prod_insumo'];?>', '<?=$pode_excluir_fornec;?>')">
        <?
//Significa que este Fornecedor do Loop é o Fornecedor Default, e sendo assim eu exibo todos os Detalhes da Última Compra ...
                if($id_fornecedor_default == $id_fornecedor) {
        ?>
                <a href="javascript:nova_janela('../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes da Última Compra" class="link">
                    <font color="red">
        <?
            }
            echo $campos[$i]['discriminacao'];
        ?>
        </td>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' value="<?=$campos[$i]['id_produto_insumo'];?>" onclick="checkbox('form', 'chkt_todos', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_produtos.php?id_fornecedor=<?=$id_fornecedor;?>'" class='botao'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='return validar()' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else {
    //Busco a Razão Social do Fornecedor passado por parâmetro ...
    $sql = "SELECT razaosocial 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Lista de Preço(s) - Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia_pi.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Lista de Preço(s) - Consultar Produto(s) Insumo(s) do Fornecedor => 
            <font color='yellow'>
                <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência do P.I.
        </td>
        <td>
            <input type='text' name="txt_referencia_pi" title="Digite a Referência do P.I." class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação do P.I.
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação do P.I." size="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência do P.A.
        </td>
        <td>
            <input type='text' name="txt_referencia_pa" title="Digite a Referência do P.A." size="15" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_nao_mostrar_esp' value='1' title="Não Mostrar ESP" id='label' class='checkbox' checked>
            <label for='label'>Não Mostrar ESP</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'lista_precos.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia_pi.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>