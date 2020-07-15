<?
require('../../../lib/segurancas.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='erro'>PREENCHIMENTO INCORRETO P/ OS PRAZOS DO VENCIMENTO.</font>";
$mensagem[2] = "<font class='confirmacao'>VENCIMENTO REALIZADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>VENCIMENTO JÁ EXISTENTE.</font>";

if($passo == 1) {
//Aqui eu verifico se foi preenchida a Qtde de dias referente as Parcelas ...
    for($i = 0; $i < count($_POST['hdd_pedido_financiamento']); $i++) {
/*Se o a parcela anterior a próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
//Enquanto não chegar na última parcela, eu vou fazendo essa comparação ...
        if(($i + 1) < count($_POST['hdd_pedido_financiamento'])) {
            if($_POST['txt_dias'][$i] > $_POST['txt_dias'][$i + 1]) $valor = 1;
        }
    }

    if($valor != 1) {//Significa que a parte de Dias dos Prazos está corretamente preenchida
//Disparando o Loop ...
        for($i = 0; $i < count($_POST['hdd_pedido_financiamento']); $i++) {
            $dias = $_POST['txt_dias'][$i];
            $data = data::datatodate($_POST['txt_data'][$i], '-');

//Alterando os dados da Tabela de Pedidos de Vencimento ...
            $sql = "UPDATE `pedidos_financiamentos` SET `dias` = '$dias', `data` = '$data' WHERE `id_pedido_financiamento` = '".$_POST['hdd_pedido_financiamento'][$i]."' LIMIT 1 ";
            bancos::sql($sql);
        }
/*Por garantia atualizo o Prazo de Entrega na tabela de Pedidos que foi passado 
por parâmetro ...*/
        $data_entrega_atual = data::datatodate($_POST['txt_data_entrega_atual'], '-');
        $sql = "UPDATE `pedidos` SET `prazo_entrega` = '$data_entrega_atual' where id_pedido = '$_POST[id_pedido]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
    <Script Language = 'Javascript'>
        var valor = '<?=$valor;?>'
        if(valor == 2) {//Significa que foi possível fazer o vencimento ...
            window.location = 'alterar_cabecalho.php?id_pedido=<?=$_POST['id_pedido'];?>&valor=<?=$valor;?>'
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }else {
            window.location = 'alterar_finame.php?id_pedido=<?=$_POST['id_pedido'];?>&valor=<?=$valor;?>'
        }
    </Script>
<?
}else {
//Busca dos Dados de Cabeçalho deste Pedido ...
    $sql = "SELECT f.razaosocial, f.id_pais, p.*, tm.simbolo, CONCAT(tm.simbolo, ' - ', tm.moeda) AS moeda 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
            WHERE p.`id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_pais        = $campos[0]['id_pais'];
    $razaosocial    = $campos[0]['razaosocial'];
    $simbolo        = $campos[0]['simbolo'];
    $moeda          = $campos[0]['moeda'];
//Data de Emissão ...
    $data_emissao = (substr($campos[0]['data_emissao'], 0, 10) == '0000-00-00') ? '' : data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
//Aqui eu busco o Valor Total do Pedido com IPI ...
    $valor_total_ped = compras_new::valor_total_ped_com_ipi($_GET['id_pedido']);
/***********************Busca dos Vencimentos Gerados p/ este Pedido***********************/
    $sql = "SELECT * 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' ORDER BY dias ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
?>
<html>
<title>.:: Alterar Vencimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Continuação ...
    var elementos = document.form.elements
//Verifico se as Demais caixas do Iframe estão preenchidas ...
    for(var i = 0; i < elementos.length; i++) {
//Dias ...
        if(elementos[i].name == 'txt_dias[]') {
            if(elementos[i].value == '') {
                alert('PREENCHA O N.º DE DIAS DO VENCIMENTO !')
                elementos[i].focus()
                return false
            }
        }
    }
    var valor_anterior = 0//Utilizado mais abaixo ...
//Aqui eu verifico se foi preenchida a Qtde de dias referente as Parcelas ...
    for(var i = 0; i < elementos.length; i++) {
/*Se o a parcela anterior a próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
        if(elementos[i].name == 'txt_dias[]') {
//Enquanto não chegar na última parcela, eu vou fazendo essa comparação ...
            if(valor_anterior != 0) {//Se está variável estiver preenchida ...
                if(eval(valor_anterior) > eval(elementos[i].value)) {
                    alert('PREENCHIMENTO INCORRETO P/ OS PRAZOS DO VENCIMENTO !')
                    elementos[i].focus()
                    elementos[i].select()
                    return false
                }
            }
            valor_anterior = elementos[i].value
        }
    }

//Tratando os Elementos antes p/ enviar p/ o BD ...
    for(var i = 0; i < elementos.length; i++) {
//Se o Tipo de Objeto for caixa de Texto ...
        if(elementos[i].type == 'text') {
            elementos[i].value = strtofloat(elementos[i].value)
            elementos[i].disabled = false
        }
    }
}

function calcular_todos_prazos() {
    var linhas_financiamentos = eval('<?=$linhas_financiamento;?>')
    for(i = 1; i <= linhas_financiamentos; i++) {
/*Chama a função em JavaScript p/ atualizar os Vencimentos assim que entrar na Tela 
logo de cara*/
        calcular_novo_prazo('txt_data'+i, 'txt_dias'+i)
    }
}

function calcular_novo_prazo(data, dias) {
    if(document.getElementById(dias).value != '') {//Se a Qtde de Dias estiver preenchida ...
        nova_data('<?=$txt_data_entrega_atual;?>', "document.getElementById('"+data+"')", "document.getElementById('"+dias+"')")
    }else {//Limpa a caixa ...
        document.getElementById(data).value = ''
    }
}
</Script>
<body onload='calcular_todos_prazos();document.form.elements[2].focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='id_pedido' value="<?=$_GET['id_pedido'];?>">
<input type='hidden' name='txt_data_entrega_atual' value="<?=$txt_data_entrega_atual;?>">
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Vencimento(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td colspan='2'>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td colspan='2'>
            <?=$moeda;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Emissão:</b>
        </td>
        <td colspan='2'>
            <?=$data_emissao;?>
        </td>
    </tr>
<!--
/***************************************************************************************/
Esse parâmetro txt_data_entrega_atual, veio de outra Tela porque é fundamental p/ o cálculo 
dos Prazos de Financiamento aqui nessa parte ...-->
    <tr class='linhanormal'>
        <td>
            <font color="darkgreen">
            <?
                if($id_pais == 31) {
                    echo '<b>Prazo de Entrega:</b>';
                }else {
                    echo '<b>Prazo de Embarque:</b>';
                }
            ?>
            </font>
        </td>
        <td colspan='2'>
            <?=$txt_data_entrega_atual;?>
        </td>
    </tr>
<!--*************************************************************************************-->
    <tr class='linhanormal'>
        <td>
            <b>Valor do Pedido:</b>
        </td>
        <td colspan='2'>
            <?=$simbolo.' '.number_format($valor_total_ped, 2, ',', '.');?>
        </td>
    </tr>
<?
/*********************Listagem dos Vencimentos Gerados p/ este Pedido*********************/
    $cont_tab = 0;
    for($i = 0; $i < $linhas_financiamento; $i++) {
?>
    <tr class='linhanormal'>
        <td width='150'>
            <b>Parcela N.º <?=$i + 1;?>:</b>
        </td>
        <td>
            Dias: <input type='text' name='txt_dias[]' value="<?=$campos_financiamento[$i]['dias'];?>" id='txt_dias<?=$i + 1;?>' title="Digite o N.º de Dias" size="8" maxlength="7" onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_novo_prazo('txt_data<?=$i + 1;?>', 'txt_dias<?=$i + 1;?>')" tabIndex="<?='10'.$cont_tab;?>" class='caixadetexto'>
        </td>
        <td>
            Data: <input type='text' name='txt_data[]' value="<?=data::datetodata($campos_financiamento[$i]['data'], '/');?>" id="txt_data<?=$i + 1;?>" title='Data' size='12' class='textdisabled' disabled>&nbsp;&nbsp;
            <input type='hidden' name='hdd_pedido_financiamento[]' value="<?=$campos_financiamento[$i]['id_pedido_financiamento'];?>">
        </td>
    </tr>
<?
        $cont_tab++;
    }
/********************************************************************************************************/
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_voltar_cabecalho' value='&lt;&lt; Voltar p/ Cabeçalho &lt;&lt;' title='Voltar p/ Cabeçalho' onclick="window.location = 'alterar_cabecalho.php?id_pedido=<?=$_GET['id_pedido'];?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.elements[1].focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>