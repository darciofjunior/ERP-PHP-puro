<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if(!empty($_POST['txt_pecas_emb'])) {
//Aqui eu busco o produto acabado através do id_produto_acabado_custo
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
//Verifico quantas embalagens q estão atreladas ao produto acabado
    $sql = "SELECT COUNT(ppe.id_pa_pi_emb) AS qtde_itens 
            FROM `pas_vs_pis_embs` ppe 
            WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ";
    $campos_itens   = bancos::sql($sql);
    $qtde_itens     = $campos_itens[0]['qtde_itens'];
    if($qtde_itens == 1) {//Se existir apenas uma embalagem atrelada, essa é obrigada a ser a embalagem principal ...
        if(empty($chkt_embalagem_principal)) $chkt_embalagem_principal = 1;
//Mais de uma embalagem atrelada, zera todas as embalagens principais e daí se assumi a embalagem principal + abaixo ...
    }else {
        if($chkt_embalagem_principal == 1) {//Significa q o usuário acionou o checkbox de embalagem principal p/ este PA ...
//Busca a situação da embalagem principal referente ao produto selecionado
            $sql = "SELECT embalagem_default 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_pa_pi_emb` = '$_POST[id_pa_pi_emb]' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $embalagem_default  = $campos[0]['embalagem_default'];
/*Significa que esta não era a embalagem principal e vai passar a ser, sendo assim vai atualizar todas as embalagem 
para 0, e mais abaixo vai ativar somente esta para 1, dizendo q esta é a embalagem principal ...*/
            if($embalagem_default == 0) {
                $sql = "UPDATE `pas_vs_pis_embs` SET `embalagem_default` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' ";
                bancos::sql($sql);
            }
        }else {//Significa q o usuário desmarcou o checkbox de embalagem principal p/ este PI ...
//Verifica se existe algum produto pelo menos como sendo o produto principal, fora o q está selecionado ...
            $sql = "SELECT id_pa_pi_emb 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_pa_pi_emb` <> '$_POST[id_pa_pi_emb]' 
                    AND `embalagem_default` = '1' ";
            $campos = bancos::sql($sql);
/*Significa q não existe nem uma embalagem como sendo principal, e isso não pode o sistema força aquele produto a ser 
embalagem principal, ao menos que o usuário venha estar trocando para q outra embalagem passe a ser a embalagem principal,
do contrário este continuará sendo, mesmo que o usuário venha estar desmarcando o checkbox ...*/
            if(count($campos) == 0) $chkt_embalagem_principal = 1;
        }
    }
    $sql = "UPDATE `pas_vs_pis_embs` SET `pecas_por_emb` = '$_POST[txt_pecas_emb]', `embalagem_default` = '$chkt_embalagem_principal' WHERE `id_pa_pi_emb` = '$_POST[id_pa_pi_emb]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
//Atualização do Funcionário que alterou os dados no custo ...
    $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
	
    if($_POST['hdd_adicionar_novo'] == 'S') {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_embalagem.php?id_produto_acabado_custo=<?=$_POST[id_produto_acabado_custo];?>'
    </Script>
<?
    }
}

$id_produto_acabado_custo   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];
$fator_custo1               = genericas::variavel(12);

//Aqui eu trago o produto acabado do id_produto_acabado_custo
$sql = "SELECT `id_produto_acabado` 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_produto_acabado = $campos[0]['id_produto_acabado'];

//Aqui eu já busco a Unidade do PA que será utilizada mais abaixo ...
$sql = "SELECT u.`sigla` 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos_unidade_pa = bancos::sql($sql);

//Seleciona a qtde de itens que existe do produto acabado na etapa 1
$sql = "SELECT COUNT(ppe.`id_pa_pi_emb`) AS qtde_itens 
        FROM `produtos_insumos` pi 
        INNER JOIN `pas_vs_pis_embs` ppe ON ppe.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ";
$campos_itens   = bancos::sql($sql);
$qtde_itens     = $campos_itens[0]['qtde_itens'];

//Aqui traz todos os produtos insumos que estão relacionados ao produto acabado passado por parâmetro ...
$sql = "SELECT ppe.`id_pa_pi_emb`, ppe.`pecas_por_emb`, ppe.`embalagem_default`, pi.`id_produto_insumo`, pi.`discriminacao`, 
        pi.`unidade_conversao`, u.`sigla` 
        FROM `pas_vs_pis_embs` ppe 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY ppe.`id_pa_pi_emb` ";
if(empty($posicao)) 	$posicao = $qtde_itens;
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
?>
<html>
<head>
<title>.:: Alterar Embalagem(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa1() {
    var fator_custo         = eval('<?=$fator_custo1;?>')
    var unidade_conversao   = eval('<?=$campos[0]['unidade_conversao'];?>')
    if(unidade_conversao == 0) unidade_conversao = 1//Para não dar erro de divisão no cálculo abaixo
/***********************************************/
    //Se tracinho, iguala a 1
    var pecas_emb           = (document.form.txt_pecas_emb.value == '-') ? 1 : eval(strtofloat(document.form.txt_pecas_emb.value))
    var preco_unitario_rs   = eval(strtofloat(document.form.txt_preco_unitario_rs.value))

    //Tem como realizar o cálculo
    if(pecas_emb != 0)         document.form.txt_total1.value = ((1 / unidade_conversao) / pecas_emb) * preco_unitario_rs * fator_custo

    if(isNaN(document.form.txt_total1.value)) {
        document.form.txt_total1.value = ''
    }else {
        document.form.txt_total1.value = arred(document.form.txt_total1.value, 2, 1)
    }
}

function calculo_qtde_pis() {
    if(document.form.txt_pecas_emb.value != '') {
        var pecas_emb = eval(strtofloat(document.form.txt_pecas_emb.value))
        if(document.form.txt_unidade_conversao.value == 'S. C.') {
            var unidade_conversao = 1
        }else {
            var unidade_conversao = eval(strtofloat(document.form.txt_unidade_conversao.value))
        }
        var qtde_pis = (1 / unidade_conversao) / (pecas_emb)
        document.form.txt_qtde_pis.value = qtde_pis
        document.form.txt_qtde_pis.value = arred(document.form.txt_qtde_pis.value, 2, 1)
    }else {
        document.form.txt_qtde_pis.value = ''
    }
}

function validar(posicao) {
    var pecas_emb = eval(strtofloat(document.form.txt_pecas_emb.value))
    if(pecas_emb == 0 || typeof(pecas_emb) == 'undefined') {
        alert('PEÇAS / EMBALAGEM INVÁLIDA ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
        document.form.txt_pecas_emb.focus()
        document.form.txt_pecas_emb.select()
        return false
    }
    limpeza_moeda('form', 'txt_pecas_emb, ')
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
<body onload='calculo_qtde_pis();document.form.txt_pecas_emb.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>')">
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pa_pi_emb' value="<?=$campos[0]['id_pa_pi_emb'];?>">
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
            1&ordf; Etapa: Alterar Embalagem(ns)
        </td>
    </tr>
    <tr class='linhadestaque'> 
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                <font color="#FFFF00">
                    Unid.:
                </font>
                <?=$campos[0]['sigla'];?>
                - <font color='#FFFF00'>
                    Discrim.:
                </font>
                <?=$campos[0]['discriminacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pçs / Emb: 
        </td>
        <td>
            <input type='text' name='txt_pecas_emb' value="<?=number_format($campos[0]['pecas_por_emb'], 3, ',', '.');?>" onKeyUp="verifica(this, 'moeda_especial', '3', '', event);calculo_etapa1();calculo_qtde_pis()" size='10' class='caixadetexto'>/
            <?
                if($campos[0]['unidade_conversao'] > 0) {
                    $unidade_conversao = number_format($campos[0]['unidade_conversao'], 2, ',', '.');
                }else {
                    $unidade_conversao = 'S. C.';
                }
            ?>
            <input type='text' name='txt_unidade_conversao' value='<?=$unidade_conversao;?>' size='10' class='disabled' disabled>
            <font title='Unidade de Conversão' style='cursor:help' color="brown"><b>U.C.</b></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde PIs x <?=$campos_unidade_pa[0]['sigla'];?>: 
        </td>
        <td>
            <input type='text' name='txt_qtde_pis' size='10' class='textdisabled' disabled>
            <?=$campos[0]['sigla'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['embalagem_default'] == 1) ? 'checked' : '';?>
            <input type='checkbox' name='chkt_embalagem_principal' value='1' id='chkt_embalagem_principal' <?=$checked;?> class='checkbox'>
            <label for='chkt_embalagem_principal'>Embalagem Principal</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço Unitário sem ICMS R$:
        </td>
        <td>
            <?$preco_unitario = custos::preco_custo_pi($campos[0]['id_produto_insumo']);?>
            <input type='text' name='txt_preco_unitario_rs' value="<?=number_format($preco_unitario, 2, ',', '.');?>" size='12' class='disabled' disabled>
            <?=$campos[0]['sigla'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total R$:
        </td>
        <?
            $unidade_conversao = $campos[0]['unidade_conversao'];
            if($unidade_conversao == 0) $unidade_conversao = 1;//Para não dar erro de divisão no cálculo abaixo
            $total = ((1 / $unidade_conversao) / $campos[0]['pecas_por_emb']) * $preco_unitario * $fator_custo1;
        ?>
        <td>
            <input type='text' name='txt_total1' value="<?=number_format($total, 2, ',', '.');?>" size='15' class='disabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_adicionar_novo' value='Adicionar Novo' title='Adicionar Novo' onclick="document.form.hdd_adicionar_novo.value = 'S';validar('<?=$posicao;?>')" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa1()" style='color:#ff9900' class='botao'>
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