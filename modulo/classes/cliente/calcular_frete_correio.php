<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');

if($_GET['id_orcamento_venda']) {//Quando vier pelo caminho de Orçamento ...
    //Busca de alguns dados de Orçamento que podem implicar no Imposto Abaixo ...
    $sql = "SELECT c.`id_uf`, ov.`nota_sgd` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Se a Negociação for como 'Grupo', então não existe essa variável de Impostos Federais ...
    if($campos[0]['nota_sgd'] == 'S') {//Grupo ...
        $outros_impostos_federais = 0;
    }else {//Alba ou Tool ...
        $outros_impostos_federais = genericas::variavel(34);
    }
    //Busca o Valor do Maior ICMS da NF ...
    $sql = "SELECT icms.`icms` AS maior_icms 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
            INNER JOIN `icms` ON icms.`id_classific_fiscal` = cf.`id_classific_fiscal` AND icms.`id_uf` = '".$campos[0]['id_uf']."' 
            WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY icms.`icms` DESC LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $maior_icms = $campos[0]['maior_icms'];
}else if(!empty($_GET['id_nf'])) {//Quando vier pelo caminho de NF de Saída ...
    //Busca de alguns dados de NF que podem implicar no Imposto Abaixo ...
    $sql = "SELECT `id_empresa` 
            FROM `nfs` 
            WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Se a Empresa = 'Grupo', então não existe essa variável de Impostos Federais ...
    if($campos[0]['id_empresa'] == 4) {//Grupo ...
        $outros_impostos_federais = 0;
    }else {//Alba ou Tool ...
        $outros_impostos_federais = genericas::variavel(34);
    }
    //Busca o Valor do Maior ICMS da NF ...
    $sql = "SELECT `icms` AS maior_icms 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$_GET[id_nf]' ORDER BY `icms` DESC LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $maior_icms = $campos[0]['maior_icms'];
}else if(!empty($_GET['id_nf_outra'])) {//Quando vier pelo caminho de NF Outras ...
    //Busca de alguns dados de NF que podem implicar no Imposto Abaixo ...
    $sql = "SELECT `id_empresa` 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Se a Empresa = 'Grupo', então não existe essa variável de Impostos Federais ...
    if($campos[0]['id_empresa'] == 4) {//Grupo ...
        $outros_impostos_federais = 0;
    }else {
        $outros_impostos_federais = genericas::variavel(34);
    }
    //Busca o Valor do Maior ICMS da NF ...
    $sql = "SELECT `icms` AS maior_icms 
            FROM `nfs_outras_itens` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' ORDER BY `icms` DESC LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $maior_icms = $campos[0]['maior_icms'];
}else {//Quando vier pelo caminho do Vale trato como se fosse SGD ...
    $outros_impostos_federais   = 0;
    $maior_icms                 = 0;
}
?>
<html>
<head>
<title>.:: Calcular Frete Correio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function atualizar() {
    if(document.form.txt_valor_frete.value == '') {
        alert('DIGITE O VALOR DO FRETE ! ')
        document.form.txt_valor_frete.focus()
        document.form.txt_valor_frete.select()
        return false
    }
    calcular()//Se já estiver digitado, então calcula o valor ...
//Pergunta se deseja atualizar o Valor Frete na Tela de Baixo ...
    resposta = confirm('DESEJA ATUALIZAR ESSE VALOR DO FRETE PARA A TELA DE CABEÇALHO ?')
    if(resposta == true) {
        if(typeof(opener.document.form.txt_valor_frete_estimado) == 'object') {//Essa caixa só existe no Cabeçalho do Orçamento de Vendas ...
            opener.document.form.txt_valor_frete_estimado.value = document.form.txt_valor_frete_com_impostos.value
        }else {//Outras telas ...
            opener.document.form.txt_valor_frete.value = document.form.txt_valor_frete_com_impostos.value
        }
        window.close()
    }
}

function calcular() {
    if(document.form.txt_valor_frete.value != '') {
        var valor_frete                                     = eval(strtofloat(document.form.txt_valor_frete.value))
        var maior_perc_icms                                 = eval(strtofloat(document.form.txt_maior_perc_icms.value))
        var outros_impostos_federais                        = eval(strtofloat(document.form.txt_outros_impostos_federais.value))
        document.form.txt_valor_frete_com_impostos.value    = valor_frete / ((100 - maior_perc_icms - outros_impostos_federais) / 100)
        document.form.txt_valor_frete_com_impostos.value    = arred(document.form.txt_valor_frete_com_impostos.value, 2, 1)
    }else {
        document.form.txt_valor_frete_com_impostos.value    = ''
    }
}

function tecla_pressionada(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13) atualizar()
    }else {
        if(event.which == 13) atualizar()
    }
}
</Script>
</head>
<body onload='document.form.txt_valor_frete.focus()'>
<form name='form'>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Calcular Frete Correio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor do Frete Site Correio: </b>
        </td>
        <td>
            <input type='text' name='txt_valor_frete' title='Valor do Frete' maxlength='12' size='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular();tecla_pressionada(event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Maior % ICMS da NF: 
        </td>
        <td>
            <input type='text' name='txt_maior_perc_icms' title='Maior % de ICMS' value="<?=number_format($maior_icms, 2, ',', '.');?>" maxlength='5' size='6' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Outros Impostos Federais: 
        </td>
        <td>
            <input type='text' name='txt_outros_impostos_federais' value='<?=number_format($outros_impostos_federais, 2, ',', '.');?>' title='Outros Impostos Federais' maxlength='5' size='6' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Frete c/ Impostos: 
        </td>
        <td>
            <input type='text' name='txt_valor_frete_com_impostos' title='Valor do Frete c/ Impostos' maxlength='8' size='9' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_valor_frete.focus()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick='atualizar()' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>