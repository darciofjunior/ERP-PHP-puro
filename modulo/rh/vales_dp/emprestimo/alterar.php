<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE EMPR�STIMO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_sys               = date('Y-m-d H:i:s');
//Transformo o campo no formato em que vai ser reconhecido pelo BD ...
    $txt_nova_data_debito   = data::datatodate($txt_nova_data_debito, '-');
    $txt_data_emissao       = data::datatodate($txt_data_emissao, '-');
//Alterando o Vale de Empr�stimo na Tabela ...
    $sql = "UPDATE `vales_dps` SET `valor` = '$_POST[txt_novo_valor_parcela]', `data_debito` = '$txt_nova_data_debito', `data_emissao` = '$txt_data_emissao', `observacao` = '$_POST[txt_observacao]', `data_sys`= '$data_sys' WHERE `id_vale_dp` = '$_POST[id_vale_dp]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?id_vale_dp=<?=$_POST['id_vale_dp'];?>&valor=1'
    </Script>
<?
}else {
    $data_atual = date('Y-m-d');
//Busca os dados de Vale com o id_vale_dp passado por par�metro ...
    $sql = "SELECT e.nomefantasia, f.nome, vd.* 
            FROM `vales_dps` vd 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
            INNER JOIN `empresas` e ON f.`id_empresa` = e.`id_empresa` 
            WHERE vd.`id_vale_dp` = '$_GET[id_vale_dp]' LIMIT 1 ";
    $campos = bancos::sql($sql);
//Vou utilizar essa vari�vel em JavaScript e em PHP mais abaixo ...
    $vencimento         = data::diferenca_data($campos[0]['data_emissao'], $campos[0]['data_debito']);
    $diferenca_anterior = $vencimento[0];
?>
<html>
<head>
<title>.:: Alterar Vale Empr�stimo ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Emiss�o
    if(!data('form', 'txt_data_emissao', '4000', 'EMISS�O')) {
        return false
    }
//Data do D�bito
    if(!combo('form', 'cmb_data_debito', '', 'SELECIONE A DATA DE D�BITO !')) {
        return false
    }
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    atualizar_abaixo()
/*********************Controle p/ Desabilitar as caixas e gravar no BD*********************/
//Desabilitando as Caixas p/ gravar no BD ...
    document.form.txt_data_emissao.disabled = false
    document.form.txt_nova_data_debito.disabled = false
    document.form.txt_novo_vencimento.disabled = false
    document.form.txt_novo_valor_parcela.disabled = false
//Deixando as Caixas num formato em que eu consiga gravar no BD ...
    document.form.txt_novo_valor_parcela.value = strtofloat(document.form.txt_novo_valor_parcela.value)
/******************************************************************************************/
}

function incluir_data_debito() {
    nova_janela('../class_data_debito/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_debito() {
    if(document.form.cmb_data_debito.value == '') {
        alert('SELECIONE A DATA DE D�BITO !')
        document.form.cmb_data_debito.focus()
        return false
    }else {
        nova_janela('../class_data_debito/alterar.php?data='+document.form.cmb_data_debito.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function calcular_valor_parcela() {
    var taxa_aplicacao = '<?=$campos[0]["taxa_emprestimo"];?>'
//Valor do Empr�stimo
    if(document.form.txt_valor_atual.value != '' && document.form.txt_data_emissao.value.length == 10) {
        var valor_atual = eval(strtofloat(document.form.txt_valor_atual.value))
/****************************************************************************************************/
//C�lculo p/ a vari�vel Fator Di�rio q vai estar sendo utilizada + abaixo nas parcelas ...
        var fator_diario = (Math.pow((1 + taxa_aplicacao/100), 1/30))
/****************************************************************************************************/
//Calculando o Valor da Parcela com o Reajuste ...
//Data de D�bito selecionada no combo pelo usu�rio ...
        var cmb_data_debito = document.form.cmb_data_debito[document.form.cmb_data_debito.selectedIndex].text
        document.form.txt_nova_data_debito.value = cmb_data_debito
/*Vencimento, retorna a diferen�a em dias da Data de Emiss�o at� a pr�xima Data de D�bito 
cadastrada no Sys ...*/
        document.form.txt_novo_vencimento.value = diferenca_datas(document.form.txt_data_emissao, document.form.txt_nova_data_debito)
//Valor da Parcela ...
        var diferenca_anterior = '<?=$diferenca_anterior;?>'

        valor_atual = valor_atual / Math.pow(fator_diario, diferenca_anterior)
        valor_atual = valor_atual * Math.pow(fator_diario, document.form.txt_novo_vencimento.value)
        document.form.txt_novo_valor_parcela.value = valor_atual
        document.form.txt_novo_valor_parcela.value = arred(document.form.txt_novo_valor_parcela.value, 2, 1)
    }else {
        document.form.txt_nova_data_debito.value = ''
        document.form.txt_novo_vencimento.value = ''
        document.form.txt_novo_valor_parcela.value = ''
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.recarregar_tela()
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_vale_dp' value='<?=$_GET['id_vale_dp'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--Esse hidden � um controle de Tela-->
<input type="hidden" name='passo' onclick="atualizar()">
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale Empr�stimo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcion�rio:</b>
        </td>
        <td>
        <?
/*Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
e o par�metro pop_up significa que est� tela est� sendo aberta como pop_up e sendo assim � para n�o exibir
o bot�o de Voltar que existe nessa tela*/
                $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[0]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        ?>
            <a href="<?=$url;?>" title="Detalhes Funcion�rio" class="link">
                <?=$campos[0]['nome'];?>
            </a>
            (<?=$campos[0]['nomefantasia'];?>)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Atual da Parcela:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_atual' value='<?=number_format($campos[0]['valor'], 2, ',', '.');?>' title='Digite o Valor da Parcela' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emiss�o:</b>
        </td>
        <td>
            <input type='text' name="txt_data_emissao" value="<?=data::datetodata($campos[0]['data_emissao'], '/');?>" size="12" onkeyup="verifica(this, 'data', '', '', event)" onblur="calcular_valor_parcela()" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de D�bito:</b>
        </td>
        <td>
            <select name="cmb_data_debito" title="Selecione a Data de D�bito" onchange="calcular_valor_parcela()" class="combo">
            <?
                $data_atual = date('Y-m-d');
//S� listo nessa Combo as Datas de D�bitos que sejam > que a Data de Atual ...
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` > '$data_atual' ORDER BY data ";
                echo combos::combo($sql, $campos[0]['data_debito']);
            ?>
            </select>
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/incluir.png" border='0' title="Incluir Data de D�bito" alt="Incluir Data de D�bito" onClick="incluir_data_debito()">
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Data de D�bito" alt="Alterar Data de D�bito" onClick="alterar_data_debito()">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Taxa de Aplic.:</b>
        </td>
        <td>
            <?=number_format($campos[0]['taxa_emprestimo'], 2, ',', '.').' %';?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            <input type='text' name="txt_nova_data_debito" value="<?=data::datetodata($campos[0]['data_debito'], '/');?>" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vencimento:</b>
        </td>
        <td>
            <input type='text' name="txt_novo_vencimento" value="<?=$diferenca_anterior;?>" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Novo Valor da Parcela:</b>
        </td>
        <td>
            <input type='text' name="txt_novo_valor_parcela" value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='55' rows='2' maxlength='110' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>