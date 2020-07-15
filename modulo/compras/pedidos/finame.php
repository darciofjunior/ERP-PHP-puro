<?
require('../../../lib/segurancas.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='erro'>PREENCHIMENTO INCORRETO P/ OS PRAZOS DO FINANCIAMENTO.</font>";
$mensagem[2] = "<font class='confirmacao'>FINANCIAMENTO REALIZADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>FINANCIAMENTO JÁ EXISTENTE.</font>";

if($passo == 1) {
/***********************Controle p/ saber se já foi gerado Pedido de Financiamento***********************/
//Aqui eu verifico se já foi criado pelo menos 1 Pedido de Financiamento p/ este Pedido ...
    $sql = "SELECT id_pedido_financiamento 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe 1 Financiamento p/ este Pedido
        $valor = 3;
    }else {//Ainda não foi gerado nenhum financiamento
//Aqui eu verifico se foi preenchida a Qtde de dias referente as Parcelas ...
        for($i = 0; $i < $txt_qtde_parcelas; $i++) {
/*Se o a parcela anterior a próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
//Enquanto não chegar na última parcela, eu vou fazendo essa comparação ...
            if(($i + 1) < $txt_qtde_parcelas) {
                if($_POST['txt_dias'][$i] > $_POST['txt_dias'][$i + 1]) $valor = 1;
            }
        }

        if($valor != 1) {//Significa que a parte de Dias dos Prazos está corretamente preenchida
//Disparando o Loop ...
            for($i = 0; $i < $txt_qtde_parcelas; $i++) {
                $dias = $_POST['txt_dias'][$i];
                $data = data::datatodate($_POST['txt_data'][$i], '-');
                $insert_extendido.= " ('$id_pedido', '$dias', '$data'), ";
            }
            $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando os Pedidos de Financiamentos ...
            $sql = "INSERT INTO `pedidos_financiamentos` (`id_pedido`, `dias`, `data`) VALUES 
                    $insert_extendido ";
            bancos::sql($sql);
//Por garantia atualizo o Prazo de Entrega na tabela de Pedidos que foi passado por parâmetro ...
            $txt_data_entrega_atual = data::datatodate($_POST['txt_data_entrega_atual'], '-');
            $sql = "UPDATE `pedidos` SET `prazo_entrega` = '$txt_data_entrega_atual' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        var valor = '<?=$valor;?>'
        if(valor == 2) {//Significa que foi possível fazer o financiamento ...
            window.location = 'alterar_cabecalho.php?id_pedido=<?=$id_pedido;?>&valor=<?=$valor;?>'
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }else {
            window.location = 'finame.php?id_pedido=<?=$id_pedido;?>&valor=<?=$valor;?>'
        }
    </Script>
<?
}else {
//Busca dos Dados de Cabeçalho deste Pedido ...
    $sql = "SELECT f.razaosocial, f.id_pais, p.*, tm.simbolo, concat(tm.simbolo, ' - ', tm.moeda) AS moeda 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
            WHERE p.id_pedido = '$id_pedido' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_pais        = $campos[0]['id_pais'];
    $razaosocial    = $campos[0]['razaosocial'];
    $simbolo        = $campos[0]['simbolo'];
    $moeda          = $campos[0]['moeda'];

//Data de Emissão ...
    if(substr($campos[0]['data_emissao'], 0, 10) == '0000-00-00') {
        $data_emissao = '';
    }else {
        $data_emissao = data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');
    }
    $observacao = $campos[0]['observacao'];

/***********************Controle p/ saber se já foi gerado Pedido de Financiamento***********************/
//Aqui eu verifico se já foi criado pelo menos 1 Pedido de Financiamento p/ este Pedido ...
    $sql = "SELECT id_pedido_financiamento 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe 1 Financiamento p/ este Pedido
        $gerado_financiamento = 1;
    }
/********************************************************************************************************/
//Aqui eu busco o Valor Total do Pedido com IPI ...
    $valor_total_ped = compras_new::valor_total_ped_com_ipi($id_pedido);
?>
<html>
<title>.:: Financiamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(gerado_financiamento) {
    if(gerado_financiamento == 1) {
        alert('FINANCIAMENTO JÁ EXISTENTE P/ ESTE PEDIDO !')
        return false
    }else {
//Qtde de Parcelas ...
        if(!texto('form', 'txt_qtde_parcelas', '1', '1234567890', 'QTDE DE PARCELAS', '1')) {
            return false
        }
//Se for Vázia a Qtde de Parcelas ...
        if(document.form.txt_qtde_parcelas.value == 0) {
            alert('QTDE DE PARCELAS INVÁLIDA !')
            document.form.txt_qtde_parcelas.focus()
            document.form.txt_qtde_parcelas.select()
            return false
        }
//Verifico se já carregou a Tela do Iframe com as caixinhas então ...
        if(typeof(document.form.txt_dias1) != 'object') {//Ainda não gerou as Parcelas ...
            alert('É NECESSÁRIO GERAR AS PARCELAS !')
            document.form.cmd_gerar_parcelas.focus()
            return false
        }else {//Significa que já foi gerado a tela de Parcelas ...
/*Aqui eu verifico se o N.º de Parcelas que foi gerada anteriormente está compatível com a Qtde de Parcelas 
Digitada ...*/
            if(document.form.txt_qtde_parcelas.value != document.form.qtde_parcelas_gerada.value) {
                alert('A QTDE DE PARCELAS QUE FOI GERADA ESTÁ INCOMPATÍVEL A QTDE DE DIGITADA !!!')
                document.form.txt_qtde_parcelas.focus()
                document.form.txt_qtde_parcelas.select()
                return false
            }
//Continuação ...
            var elementos = document.form.elements
//Verifico se as Demais caixas do Iframe estão preenchidas ...
            for(var i = 0; i < elementos.length; i++) {
//Dias ...
                if(elementos[i].name == 'txt_dias[]') {
                    if(elementos[i].value == '') {
                        alert('PREENCHA O N.º DE DIAS DO FINANCIAMENTO !')
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
                            alert('PREENCHIMENTO INCORRETO P/ OS PRAZOS DO FINANCIAMENTO !')
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
            document.form.submit()
        }
    }
}

function tipo_negociacao() {
    document.form.txt_qtde_dias.value = ''
    if(document.form.opt_tipo_negociacao[0].checked == true) {//Dia Fixo ...
        document.form.txt_qtde_dias.disabled    = true
        document.form.txt_qtde_dias.className   = 'textdisabled'
    }else {//Intervalo Fixo ...
        document.form.txt_qtde_dias.disabled    = false
        document.form.txt_qtde_dias.className   = 'caixadetexto'
        document.form.txt_qtde_dias.focus()
    }
}

function gerar_parcelas(gerado_financiamento) {
    if(gerado_financiamento == 1) {
        alert('FINANCIAMENTO JÁ EXISTENTE P/ ESTE PEDIDO !')
        return false
    }else {
//Qtde de Parcelas ...
        if(!texto('form', 'txt_qtde_parcelas', '1', '1234567890', 'QTDE DE PARCELAS', '1')) {
            return false
        }
//Tipo de Negociação ...
        if(document.form.opt_tipo_negociacao[0].checked == false && document.form.opt_tipo_negociacao[1].checked == false) {
            alert('SELECIONE UM TIPO DE NEGOCIAÇÃO !')
            document.form.opt_tipo_negociacao[0].focus()
            return false
        }
//Se a opção selecionada foi Intervalo Fixo ...
        if(document.form.opt_tipo_negociacao[1].checked == true) {
//Qtde de Dias ...
            if(!texto('form', 'txt_qtde_dias', '1', '1234567890', 'QTDE DE DIAS', '1')) {
                return false
            }
        }
//Data da Primeira Parcela ...
        if(!data('form', 'txt_data_primeira_parcela', '4000', 'DA PRIMEIRA PRIMEIRA PARCELA')) {
            return false
	}
        var tipo_negociacao = (document.form.opt_tipo_negociacao[0].checked == true) ? 1 : 2
        
        ajax('finame_parcelas.php?qtde_parcelas='+document.form.txt_qtde_parcelas.value+'&tipo_negociacao='+tipo_negociacao+'&qtde_dias='+document.form.txt_qtde_dias.value+'&data_primeira_parcela='+document.form.txt_data_primeira_parcela.value+'&txt_data_entrega_atual=<?=$txt_data_entrega_atual;?>', 'finame_parcelas')
        /*Tenho que colocar um Timer ... pq são +/- as frações de milésimos de segundo que o Ajax precisa 
        p/ atualizar a Div e reconhecer o objeto ...*/
        setTimeout('document.getElementById("txt_dias1").focus()', 100)
    }
}

function calcular_novo_prazo(data, dias) {
    if(document.getElementById(dias).value != '') {//Se a Qtde de Dias estiver preenchida ...
        nova_data(document.form.txt_data_primeira_parcela.value, "document.getElementById('"+data+"')", "document.getElementById('"+dias+"')")
    }else {//Limpa a caixa ...
        document.getElementById(data).value = ''
    }
}
</Script>
<body onload='document.form.txt_qtde_parcelas.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar('<?=$gerado_financiamento;?>')">
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='txt_data_entrega_atual' value='<?=$txt_data_entrega_atual;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Financiamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td>
            <?=$moeda;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Emissão:</b>
        </td>
        <td>
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
        <td>
            <?=$txt_data_entrega_atual;?>
        </td>
    </tr>
<!--*************************************************************************************-->
    <tr class='linhanormal'>
        <td>
            <b>Valor do Pedido:</b>
        </td>
        <td>
            <?=$simbolo.' '.number_format($valor_total_ped, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo da 1ª Parcela:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_primeira_parcela' title='Prazo da 1ª Parcela' onkeyup="verifica(this, 'aceita', 'numeros', '', event);nova_data('<?=$txt_data_entrega_atual;?>', 'document.form.txt_data_primeira_parcela', 'document.form.txt_prazo_primeira_parcela')" size='3' maxlength='3' class='caixadetexto'> dias
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data da 1ª Parcela:</b>
        </td>
        <td>
            <input type='text' name='txt_data_primeira_parcela' title='Digite a Data da 1ª Parcela' onkeyup="verifica(this, 'data', '', '', event)" maxlength='10' size='12' class='textdisabled' disabled>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_primeira_parcela&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Parcelas:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_parcelas' title='Digite a Qtde de Parcelas' onkeyup="verifica(this, 'moeda_especial', '0', '', event)" size='5' maxlength='3' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Negociação:</b>
        </td>
        <td>
            <input type='radio' name='opt_tipo_negociacao' id='opt_tipo_negociacao1' value='1' title='Dia Fixo' onclick='tipo_negociacao()'>
            <label for='opt_tipo_negociacao1'>
                Dia Fixo
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_tipo_negociacao' id='opt_tipo_negociacao2' value='2' title='Intervalo Fixo' onclick='tipo_negociacao()'>
            <label for='opt_tipo_negociacao2'>
                Intervalo Fixo
            </label>
            &nbsp;-&nbsp;
            Qtde de Dias: <input type='text' name='txt_qtde_dias' title='Digite a Qtde de Dias' onkeyup="verifica(this, 'moeda_especial', '0', '', event)" size='5' maxlength='3' class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_gerar_parcelas' value='Gerar Parcelas' title='Gerar Parcelas' onclick="gerar_parcelas('<?=$gerado_financiamento;?>')" class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <div name='finame_parcelas' id='finame_parcelas'></div>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar_cabecalho' value='&lt;&lt; Voltar p/ Cabeçalho &lt;&lt;' title='Voltar p/ Cabeçalho' onclick="window.location = 'alterar_cabecalho.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_parcelas.focus()" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick="return validar('<?=$gerado_financiamento;?>')" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>