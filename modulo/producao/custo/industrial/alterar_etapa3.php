<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if(!empty($_POST['id_pac_pi'])) {
    $sql = "UPDATE `pacs_vs_pis` SET `qtde` = '$_POST[txt_qtde]' where id_pac_pi = '$_POST[id_pac_pi]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
//Atualização do Funcionário que alterou os dados no custo ...
    $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    
    if($_POST['hdd_adicionar_novo'] == 'S') {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_produto_insumo.php?id_produto_acabado_custo=<?=$_POST[id_produto_acabado_custo];?>'
    </Script>
<?
    }
}

$id_produto_acabado_custo   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];
$fator_custo3               = genericas::variavel(12);

//Seleciona a qtde de itens que existe do produto acabado na etapa 3
$sql = "SELECT COUNT(pp.id_pac_pi) AS qtde_itens 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `pacs_vs_pis` pp ON pp.`id_produto_insumo` = pi.`id_produto_insumo` 
        WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "SELECT pp.id_pac_pi, g.referencia, pi.id_produto_insumo, pi.discriminacao, pp.qtde, u.sigla 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        INNER JOIN `pacs_vs_pis` pp ON pp.`id_produto_insumo` = pi.`id_produto_insumo` 
        WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pi ";
if(empty($posicao)) 	$posicao = $qtde_itens;
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
?>
<html>
<head>
<title>.:: Alterar Produto Insumo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa3() {
    var fator_custo         = eval('<?=$fator_custo3;?>')
    var qtde                = eval(strtofloat(document.form.txt_qtde.value))
    var preco_unitario_rs   = eval(strtofloat(document.form.txt_preco_unitario_rs3.value))
    document.form.txt_total3.value = qtde * preco_unitario_rs * fator_custo

    if(isNaN(document.form.txt_total3.value)) {
        document.form.txt_total3.value = ''
    }else {
        document.form.txt_total3.value = arred(document.form.txt_total3.value, 2, 1)
    }
}

function validar(posicao) {
    var quantidade          = eval(strtofloat(document.form.txt_qtde.value))
    if(quantidade == 0 || typeof(quantidade) == 'undefined') {
        alert('QUANTIDADE INVÁLIDA ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
        document.form.txt_qtde.focus()
        document.form.txt_qtde.select()
        return false
    }
    limpeza_moeda('form', 'txt_qtde, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_qtde.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>')">
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='id_produto_acabado_custo' value='<?=$id_produto_acabado_custo;?>'>
<input type='hidden' name='id_pac_pi' value="<?=$campos[0]['id_pac_pi'];?>">
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_adicionar_novo'>
<!--********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            3&ordf; Etapa: Alterar Produto Insumo
        </td>
    </tr>
    <tr class='linhadestaque'> 
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                <font color="#FFFF00">Ref.:</font> 
                <?=$campos[0]['referencia'];?>
                - <font color="#FFFF00">Unid.:</font> 
                <?=$campos[0]['sigla'];?>
                - <font color="#FFFF00">Discrim.:</font> 
                <?=$campos[0]['discriminacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estoque:
        </td>
        <td> 
            <?
                $sql = "SELECT qtde AS qtde_estoque 
                        FROM `estoques_insumos` 
                        WHERE `id_produto_insumo` = ".$campos[0]['id_produto_insumo']." LIMIT 1 ";
                $campos_estoque_pi  = bancos::sql($sql);
                $qtde_estoque       = (count($campos_estoque_pi) == 1) ? number_format($campos_estoque_pi[0]['qtde_estoque'], 2, ',', '.') : '0,00';
            ?>
            <input type='text' name='txt_estoque3' value='<?=$qtde_estoque;?>' size='12' class='disabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            Quantidade:
        </td>
        <td>
            <input type='text' name='txt_qtde' value='<?=number_format($campos[0]['qtde'], 2, ',', '.');?>' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculo_etapa3()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            Pre&ccedil;o Unitário R$:
        </td>
        <td> 
            <?
                $preco_custo = custos::preco_custo_pi($campos[0]['id_produto_insumo']);
            ?>
            <input type='text' name='txt_preco_unitario_rs3' value="<?=number_format($preco_custo, 2, ',', '.');?>" size='12' class='disabled' disabled>
            &nbsp;
            <?=$campos[0]['sigla'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total R$:
        </td>
        <td>
        <?
            $total = $campos[0]['qtde'] * $preco_custo * $fator_custo3;
        ?>
            <input type='text' name='txt_total3' value='<?=number_format($total, 2, ',', '.');?>' size='12' class='disabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_adicionar_novo' value='Adicionar Novo' title='Adicionar Novo' onclick="document.form.hdd_adicionar_novo.value = 'S';validar('<?=$posicao;?>')" class='botao'>
            <input type='button' name='cmd_redefinir' value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa3()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_alterar_fornecedores' value='Alterar Fornecedores' title='Alterar Fornecedores' onClick="showHide('alterar_fornecedores'); return false" style='color:black' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'> 
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
<!--Agora sempre irá mostrar esse Iframe-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td align='right'>
            &nbsp;
            <span id='statusalterar_fornecedores'></span>
            <span id='statusalterar_fornecedores'></span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe src = '../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$campos[0]['id_produto_insumo'];?>' name="alterar_fornecedores" id="alterar_fornecedores" marginwidth="0" marginheight="0" style='display: none' frameborder='0' height='260' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<!--Controle para saber se vai estar mostrando este Iframe para o Usuário-->
<?
//Verifico se esse PI corrente está em algum Pedido de Compras ...
    $sql = "SELECT id_item_pedido 
            FROM `itens_pedidos` 
            WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
    $campos_pedido = bancos::sql($sql);
    if(count($campos_pedido) == 0) {//Como não está, exibo essa Tela com Todos os Fornecedores desse PI ...
?>
<Script Language = 'JavaScript'>
/*Idéia de Onload

Na primeira vez em que carregar essa Tela, caso venha existir algum Pedido de Compras para esse PI, então 
eu disparo por meio do JavaScript essa função para que já venha mostrar esse iframe ...*/
        showHide('alterar_fornecedores')
</Script>
<?
    }
?>
</form>
</body>
</html>