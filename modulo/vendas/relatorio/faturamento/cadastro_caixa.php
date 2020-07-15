<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/faturamento/faturamento.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CADASTRO DE CAIXA ATUALIZADO COM SUCESSO.</font>";

$valor_dolar_dia    = genericas::moeda_dia('dolar');
$valor_euro_dia     = genericas::moeda_dia('euro');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_atualizacao = date('Y-m-d H:i:s');
//Aqui eu busco qual o login do Usuário que está logado no Sistema p/ poder gravar no Banco de Dados ...
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login = $campos[0]['login'];
//Verifico se já existe pelo menos 1 registro de Caixa no Banco de Dados
    $sql = "SELECT id_rel_caixa 
            FROM `rel_caixas` ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Ainda não existe, então na 1ª vez, dou um Insert na Base de Dados ...
        $sql = "INSERT INTO `rel_caixas` (`caixa_alba`, `caixa_tool`, `caixa_c2`, `cambio_liberado`, `dolar_especie`, `dolar_paralelo`, `atrasados_menor_60_dias`, `dupl_sgd_s_prot`, `dupl_alba_s_prot`, `dupl_tool_s_prot`, `atrasados_maior_60_dias`, `estoque_aco`, `estoque_rolamento`, `emprestimo_func`, `emprestimo_rs`, `saldo_k2`, `saldo_carine`, `produtos_acabados`, `semi_acabado_prod`, `importacoes`, `semi_acabado_comp`, `contas_apagar_alba`, `contas_apagar_tool`, `contas_apagar_grupo`, `contas_apagar_sandra`, `dif_contas_apagar_carine`, `dif_contas_apagar_k2`, `dup_areceber_maior_30`, `total_emprestimos_pagar`, `valor_mensal_emprestimo`, `total_pago_consorcio_taxa_adm`, `valor_mensal_consorcio`, `login`, `data_atualizacao`) values ('$_POST[txt_caixa_alba]', '$_POST[txt_caixa_tool]', '$_POST[txt_caixa_c2]', '$_POST[txt_cambio_liberado]', '$_POST[txt_dolar_especie]', '$_POST[txt_valor_dolar_paralelo_rs]', '$_POST[txt_atrasados_menor_60_dias]', '$_POST[txt_duplicata_sgd_s_protesto]', '$_POST[txt_duplicata_alba_s_protesto]', '$_POST[txt_duplicata_tool_s_protesto]', '$_POST[txt_atrasados_maior_60_menor_180_dias]', '$_POST[txt_estoque_aco]', '$_POST[txt_estoque_rolamento]', '$_POST[txt_emprestimo_funcionario]', '$_POST[txt_emprestimo_rs]', '$_POST[txt_saldo_k2]', '$_POST[txt_saldo_carine]', '$_POST[txt_produtos_acabados]', '$_POST[txt_semi_acabado_produzido]', '$_POST[txt_importacoes]', '$_POST[txt_semi_acabado_comprado]', '$_POST[txt_contas_apagar_alba]', '$_POST[txt_contas_apagar_tool]', '$_POST[txt_contas_apagar_grupo]', '$_POST[txt_contas_apagar_sandra]', '$_POST[txt_dif_contas_pagar_carine]', '$_POST[txt_dif_contas_pagar_k2]', '$_POST[txt_duplicata_maior_30_dias]', '$_POST[txt_total_emprestimos_pagar]', '$_POST[txt_valor_mensal_emprestimo]', '$_POST[txt_total_pago_consorcio_taxa_adm]', '$_POST[txt_valor_mensal_consorcio]', '$login', '$data_atualizacao') ";
    }else {//Simplesmente atualiza os Campos ...
        $sql = "UPDATE `rel_caixas` SET `caixa_alba` = '$_POST[txt_caixa_alba]', `caixa_tool` = '$_POST[txt_caixa_tool]', `caixa_c2` = '$_POST[txt_caixa_c2]', `cambio_liberado` = '$_POST[txt_cambio_liberado]', `dolar_especie` = '$_POST[txt_dolar_especie]', `dolar_paralelo` = '$_POST[txt_valor_dolar_paralelo_rs]', `atrasados_menor_60_dias` = '$_POST[txt_atrasados_menor_60_dias]', `dupl_sgd_s_prot` = '$_POST[txt_duplicata_sgd_s_protesto]', `dupl_alba_s_prot` = '$_POST[txt_duplicata_alba_s_protesto]', `dupl_tool_s_prot` = '$_POST[txt_duplicata_tool_s_protesto]', `atrasados_maior_60_dias` = '$_POST[txt_atrasados_maior_60_menor_180_dias]', `estoque_aco` = '$_POST[txt_estoque_aco]', `estoque_rolamento` = '$_POST[txt_estoque_rolamento]', `emprestimo_func` = '$_POST[txt_emprestimo_funcionario]', `emprestimo_rs` = '$_POST[txt_emprestimo_rs]', `saldo_k2` = '$_POST[txt_saldo_k2]', `saldo_carine` = '$_POST[txt_saldo_carine]', `produtos_acabados` = '$_POST[txt_produtos_acabados]', `semi_acabado_prod` = '$_POST[txt_semi_acabado_produzido]', `importacoes` = '$_POST[txt_importacoes]', `semi_acabado_comp` = '$_POST[txt_semi_acabado_comprado]', `contas_apagar_alba` = '$_POST[txt_contas_apagar_alba]', `contas_apagar_tool` = '$_POST[txt_contas_apagar_tool]', `contas_apagar_grupo` = '$_POST[txt_contas_apagar_grupo]', `contas_apagar_sandra` = '$_POST[txt_contas_apagar_sandra]', `dif_contas_apagar_carine` = '$_POST[txt_dif_contas_pagar_carine]', `dif_contas_apagar_k2` = '$_POST[txt_dif_contas_pagar_k2]', `dup_areceber_maior_30` = '$_POST[txt_duplicata_maior_30_dias]', `total_emprestimos_pagar` = '$_POST[txt_total_emprestimos_pagar]', `valor_mensal_emprestimo` = '$_POST[txt_valor_mensal_emprestimo]', `total_pago_consorcio_taxa_adm` = '$_POST[txt_total_pago_consorcio_taxa_adm]', `valor_mensal_consorcio` = '$_POST[txt_valor_mensal_consorcio]', `login` = '$login', `data_atualizacao` = '$data_atualizacao' LIMIT 1 ";
    }
    bancos::sql($sql);
    $valor = 1;
}

//Faço busca de Dados na Tabela de Caixa ...
$sql = "SELECT * 
        FROM `rel_caixas` ";
$campos                 = bancos::sql($sql);
$caixa_alba             = number_format($campos[0]['caixa_alba'], 2, ',', '.');
$caixa_tool             = number_format($campos[0]['caixa_tool'], 2, ',', '.');
$caixa_c2               = number_format($campos[0]['caixa_c2'], 2, ',', '.');
$cambio_liberado        = $campos[0]['cambio_liberado'];
$dolar_especie          = $campos[0]['dolar_especie'];
$dolar_paralelo         = $campos[0]['dolar_paralelo'];
$atrasados_menor_60_dias = number_format($campos[0]['atrasados_menor_60_dias'], 2, ',', '.');
$dupl_sgd_s_prot        = number_format($campos[0]['dupl_sgd_s_prot'], 2, ',', '.');
$dupl_alba_s_prot       = number_format($campos[0]['dupl_alba_s_prot'], 2, ',', '.');
$dupl_tool_s_prot       = number_format($campos[0]['dupl_tool_s_prot'], 2, ',', '.');
$atrasados_maior_60_dias = number_format($campos[0]['atrasados_maior_60_dias'], 2, ',', '.');
$estoque_aco            = number_format($campos[0]['estoque_aco'], 2, ',', '.');
$estoque_rolamento      = number_format($campos[0]['estoque_rolamento'], 2, ',', '.');
$emprestimo_func        = number_format($campos[0]['emprestimo_func'], 2, ',', '.');
$emprestimo_rs          = number_format($campos[0]['emprestimo_rs'], 2, ',', '.');
$saldo_k2               = number_format($campos[0]['saldo_k2'], 2, ',', '.');
$saldo_carine           = number_format($campos[0]['saldo_carine'], 2, ',', '.');
$produtos_acabados      = number_format($campos[0]['produtos_acabados'], 2, ',', '.');
$semi_acabado_prod      = number_format($campos[0]['semi_acabado_prod'], 2, ',', '.');
$importacoes            = number_format($campos[0]['importacoes'], 2, ',', '.');
$semi_acabado_comp      = number_format($campos[0]['semi_acabado_comp'], 2, ',', '.');
$contas_apagar_alba     = number_format($campos[0]['contas_apagar_alba'], 2, ',', '.');
$contas_apagar_tool     = number_format($campos[0]['contas_apagar_tool'], 2, ',', '.');
$contas_apagar_grupo    = number_format($campos[0]['contas_apagar_grupo'], 2, ',', '.');
$contas_apagar_sandra   = number_format($campos[0]['contas_apagar_sandra'], 2, ',', '.');
$dif_contas_apagar_carine = number_format($campos[0]['dif_contas_apagar_carine'], 2, ',', '.');
$dif_contas_apagar_k2   = number_format($campos[0]['dif_contas_apagar_k2'], 2, ',', '.');
$dup_areceber_maior_30  = number_format($campos[0]['dup_areceber_maior_30'], 2, ',', '.');
$total_emprestimos_pagar = number_format($campos[0]['total_emprestimos_pagar'], 2, ',', '.');
$valor_mensal_emprestimo = number_format($campos[0]['valor_mensal_emprestimo'], 2, ',', '.');
$total_pago_consorcio_taxa_adm = number_format($campos[0]['total_pago_consorcio_taxa_adm'], 2, ',', '.');
$valor_mensal_consorcio = number_format($campos[0]['valor_mensal_consorcio'], 2, ',', '.'); 
$login = $campos[0]['login'];
$data_atualizacao = $campos[0]['data_atualizacao'];
?>
<html>
<head>
<title>.:: Cadastro de Caixa ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Alba
    if(document.form.txt_caixa_alba.value != '') {
        if(!texto('form', 'txt_caixa_alba', '1', '1234567890,.-', 'CAIXA ALBA', '2')) {
            return false
        }
    }
//Caixa Tool
    if(document.form.txt_caixa_tool.value != '') {
        if(!texto('form', 'txt_caixa_tool', '1', '1234567890,.-', 'CAIXA TOOL', '2')) {
            return false
        }
    }
//Caixa C2
    if(document.form.txt_caixa_c2.value != '') {
        if(!texto('form', 'txt_caixa_c2', '1', '1234567890,.-', 'CAIXA C2', '2')) {
            return false
        }
    }
//Câmbio Liberado
    if(document.form.txt_cambio_liberado.value != '') {
        if(!texto('form', 'txt_cambio_liberado', '1', '1234567890,.-', 'CÂMBIO LIBERADO', '2')) {
            return false
        }
    }
//U$ em Espécie
    if(document.form.txt_dolar_especie.value != '') {
        if(!texto('form', 'txt_dolar_especie', '1', '1234567890,.-', 'U$ EM ESPÉCIE', '2')) {
            return false
        }
    }
//Valor do Dólar Paralelo (R$)
    if(document.form.txt_valor_dolar_paralelo_rs.value != '') {
        if(!texto('form', 'txt_valor_dolar_paralelo_rs', '1', '1234567890,.-', 'VALOR DO DÓLAR PARALELO (R$)', '2')) {
            return false
        }
    }
//Atrasados < 60 dias
    if(document.form.txt_atrasados_menor_60_dias.value != '') {
        if(!texto('form', 'txt_atrasados_menor_60_dias', '1', '1234567890,.-', 'ATRASADO(S) < 60 DIA(S)', '2')) {
            return false
        }
    }
//Duplicata SGD s/ Protesto
    if(document.form.txt_duplicata_sgd_s_protesto.value != '') {
        if(!texto('form', 'txt_duplicata_sgd_s_protesto', '1', '1234567890,.-', 'DUPLICATA SGD S/ PROTESTO', '1')) {
            return false
        }
    }
//Duplicata Alba s/ Protesto
    if(document.form.txt_duplicata_alba_s_protesto.value != '') {
        if(!texto('form', 'txt_duplicata_alba_s_protesto', '1', '1234567890,.-', 'DUPLICATA ALBA S/ PROTESTO', '1')) {
            return false
        }
    }
//Duplicata Tool s/ Protesto
    if(document.form.txt_duplicata_tool_s_protesto.value != '') {
        if(!texto('form', 'txt_duplicata_tool_s_protesto', '1', '1234567890,.-', 'DUPLICATA TOOL S/ PROTESTO', '1')) {
            return false
        }
    }
//Atrasados > 60 e < 180 dias
    if(document.form.txt_atrasados_maior_60_menor_180_dias.value != '') {
        if(!texto('form', 'txt_atrasados_maior_60_menor_180_dias', '1', '1234567890,.-', 'ATRASADO(S) > 60 DIA(S)', '2')) {
            return false
        }
    }
//Estoque Aço
    if(document.form.txt_estoque_aco.value != '') {
        if(!texto('form', 'txt_estoque_aco', '1', '1234567890,.-', 'ESTOQUE AÇO', '2')) {
            return false
        }
    }
//Estoque Rolamento
    if(document.form.txt_estoque_rolamento.value != '') {
        if(!texto('form', 'txt_estoque_rolamento', '1', '1234567890,.-', 'ESTOQUE ROLAMENTO', '2')) {
            return false
        }
    }
//Empréstimo Funcionário
    if(document.form.txt_emprestimo_funcionario.value != '') {
        if(!texto('form', 'txt_emprestimo_funcionario', '1', '1234567890,.-', 'EMPRÉSTIMO FUNCIONÁRIO', '2')) {
            return false
        }
    }
//Empréstimo R/S
    if(document.form.txt_emprestimo_rs.value != '') {
        if(!texto('form', 'txt_emprestimo_rs', '1', '1234567890,.-', 'EMPRÉSTIMO R/S', '2')) {
            return false
        }
    }
//Saldo K2
    if(document.form.txt_saldo_k2.value != '') {
        if(!texto('form', 'txt_saldo_k2', '1', '1234567890,.-', 'SALDO K2', '2')) {
            return false
        }
    }
//Saldo Carine
    if(document.form.txt_saldo_carine.value != '') {
        if(!texto('form', 'txt_saldo_carine', '1', '1234567890,.-', 'SALDO CARINE', '2')) {
            return false
        }
    }
//Saldo Carine
    if(document.form.txt_saldo_carine.value != '') {
        if(!texto('form', 'txt_saldo_carine', '1', '1234567890,.-', 'SALDO CARINE', '2')) {
            return false
        }
    }
//Produtos Acabados
    if(document.form.txt_produtos_acabados.value != '') {
        if(!texto('form', 'txt_produtos_acabados', '1', '1234567890,.-', 'PRODUTO(S) ACABADO(S)', '2')) {
            return false
        }
    }
//Semi Acabado Produzido
    if(document.form.txt_semi_acabado_produzido.value != '') {
        if(!texto('form', 'txt_semi_acabado_produzido', '1', '1234567890,.-', 'SEMI ACABADO PRODUZIDO', '2')) {
            return false
        }
    }
//Importações
    if(document.form.txt_importacoes.value != '') {
        if(!texto('form', 'txt_importacoes', '1', '1234567890,.-', 'IMPORTAÇÃO(ÕES)', '1')) {
            return false
        }
    }
//Semi Acabado Comprado
    if(document.form.txt_semi_acabado_comprado.value != '') {
        if(!texto('form', 'txt_semi_acabado_comprado', '1', '1234567890,.-', 'SEMI ACABADO COMPRADO', '2')) {
            return false
        }
    }
//Contas à Pagar Alba
    if(document.form.txt_contas_apagar_alba.value != '') {
        if(!texto('form', 'txt_contas_apagar_alba', '1', '1234567890,.-', 'CONTA(S) À PAGAR ALBA', '1')) {
            return false
        }
    }
//Contas à Pagar Tool
    if(document.form.txt_contas_apagar_tool.value != '') {
        if(!texto('form', 'txt_contas_apagar_tool', '1', '1234567890,.-', 'CONTA(S) À PAGAR TOOL', '1')) {
            return false
        }
    }
//Contas à Pagar Grupo
    if(document.form.txt_contas_apagar_grupo.value != '') {
        if(!texto('form', 'txt_contas_apagar_grupo', '1', '1234567890,.-', 'CONTA(S) À PAGAR GRUPO', '1')) {
            return false
        }
    }
//Contas à Pagar Sandra
    if(document.form.txt_contas_apagar_sandra.value != '') {
        if(!texto('form', 'txt_contas_apagar_sandra', '1', '1234567890,.-', 'CONTA(S) À PAGAR SANDRA', '1')) {
            return false
        }
    }
//Dif. Contas à Pagar Carine
    if(document.form.txt_dif_contas_pagar_carine.value != '') {
        if(!texto('form', 'txt_dif_contas_pagar_carine', '1', '1234567890,.-', 'CONTA(S) À PAGAR CARINE', '1')) {
            return false
        }
    }
//Dif. Contas à Pagar K2
    if(document.form.txt_dif_contas_pagar_k2.value != '') {
        if(!texto('form', 'txt_dif_contas_pagar_k2', '1', '1234567890,.-', 'CONTA(S) À PAGAR K2', '1')) {
            return false
        }
    }
//Duplicata à Receber > 30 dias
    if(document.form.txt_duplicata_maior_30_dias.value != '') {
        if(!texto('form', 'txt_duplicata_maior_30_dias', '1', '1234567890,.-', 'DUPLICATA À RECEBER > 30 DIAS', '1')) {
            return false
        }
    }
//Aqui eu travo o botão p/ que o usuário não fique submetendo mais de uma vez ...
    document.form.cmd_salvar.disabled = true
    return limpeza_moeda('form', 'txt_caixa_alba, txt_caixa_tool, txt_caixa_c2, txt_cambio_liberado, txt_dolar_especie, txt_valor_dolar_paralelo_rs, txt_atrasados_menor_60_dias, txt_duplicata_sgd_s_protesto, txt_duplicata_alba_s_protesto, txt_duplicata_tool_s_protesto, txt_atrasados_maior_60_menor_180_dias, txt_estoque_aco, txt_estoque_rolamento, txt_emprestimo_funcionario, txt_emprestimo_rs, txt_saldo_k2, txt_saldo_carine, txt_produtos_acabados, txt_semi_acabado_produzido, txt_importacoes, txt_semi_acabado_comprado, txt_contas_apagar_alba, txt_contas_apagar_tool, txt_contas_apagar_grupo, txt_contas_apagar_sandra, txt_dif_contas_pagar_carine, txt_dif_contas_pagar_k2, txt_duplicata_maior_30_dias, txt_total_emprestimos_pagar, txt_valor_mensal_emprestimo, txt_total_pago_consorcio_taxa_adm, txt_valor_mensal_consorcio,')
}

//Dólar Reais
function calculo_dolar_reais() {
    var valor_dolar_dia = eval('<?=$valor_dolar_dia;?>')
//Se estiver preenchido este campo, cálculo normalmente ...
    if(document.form.txt_cambio_liberado.value != '') {
        var cambio_liberado = eval(strtofloat(document.form.txt_cambio_liberado.value))
//Cálculo do Dólar em Reais
        document.form.txt_calculo_dolar_reais.value = valor_dolar_dia * cambio_liberado
        document.form.txt_calculo_dolar_reais.value = arred(document.form.txt_calculo_dolar_reais.value, 2, 1)
//Se não estiver preenchido, igualo igual a Zero
    }else {
        document.form.txt_calculo_dolar_reais.value = '0,00'
    }
}

function calculo_dolar_especie_paralelo() {
//Dólar Espécie ...
    if(document.form.txt_dolar_especie.value != '') {
        var dolar_especie = eval(strtofloat(document.form.txt_dolar_especie.value))
    }else {
        var dolar_especie = 0
    }
//Dólar Paralelo R$
    if(document.form.txt_valor_dolar_paralelo_rs.value != '') {
        var valor_dolar_paralelo_rs = eval(strtofloat(document.form.txt_valor_dolar_paralelo_rs.value))
    }else {
        var valor_dolar_paralelo_rs = 0
    }
    document.form.txt_calculo_dolar_especie_paralelo.value = dolar_especie * valor_dolar_paralelo_rs
    document.form.txt_calculo_dolar_especie_paralelo.value = arred(document.form.txt_calculo_dolar_especie_paralelo.value, 2, 1)
}

function atualizar_abaixo() {
    window.opener.document.getElementById('cmd_consultar').click()
    window.close()
}
</Script>
</head>
<body onload='document.form.txt_caixa_alba.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cadastro de Caixa
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Caixa Alba:
        </td>
        <td>
            <input type='text' name="txt_caixa_alba" value="<?=$caixa_alba;?>" title="Digite o Caixa Alba" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Airton
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Caixa Tool:
        </td>
        <td>
            <input type='text' name="txt_caixa_tool" value="<?=$caixa_tool;?>" title="Digite o Caixa Tool" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Marcia
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Caixa C2:
        </td>
        <td>
            <input type='text' name="txt_caixa_c2" value="<?=$caixa_c2;?>" title="Digite o Caixa C2" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Simone
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Câmbio Liberado U$:
        </td>
        <td>
            <input type='text' name="txt_cambio_liberado" value="<?=number_format($cambio_liberado, 2, ',', '.');?>" title="Digite o Câmbio Liberado" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calculo_dolar_reais()" class='caixadetexto'>
            &nbsp;
            R$ <input type='text' name="txt_calculo_dolar_reais" value="<?=number_format($valor_dolar_dia * $cambio_liberado, 2, ',', '.');?>" size="13" maxlength="13" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            U$ em Espécie:
        </td>
        <td>
            <input type='text' name="txt_dolar_especie" value="<?=number_format($dolar_especie, 2, ',', '.');?>" title="Digite o U$ em Espécie" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calculo_dolar_especie_paralelo()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Dólar Paralelo m&eacute;dio (R$):
        </td>
        <td>
            <input type='text' name="txt_valor_dolar_paralelo_rs" value="<?=number_format($dolar_paralelo, 2, ',', '.');?>" title="Digite o Valor do Dólar Paralelo (R$)" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calculo_dolar_especie_paralelo()" class='caixadetexto'>
            &nbsp;
            R$ <input type='text' name="txt_calculo_dolar_especie_paralelo" value="<?=number_format($dolar_especie * $dolar_paralelo, 2, ',', '.');?>" size="13" maxlength="13" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Atrasados < 60 dias:
        </td>
        <td>
            <input type='text' name="txt_atrasados_menor_60_dias" value="<?=$atrasados_menor_60_dias;?>" title="Digite os Atrasados < 60 dias" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Contas + Cheques - Caução * 0,93 (Semanas Anteriores)
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Duplicata SGD:
        </td>
        <td>
            <input type='text' name="txt_duplicata_sgd_s_protesto" value="<?=$dupl_sgd_s_prot;?>" title="Digite a Duplicata SGD s/ Protesto" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Contas + Cheques - Caução * 0,93
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Duplicata Alba:
        </td>
        <td>
            <input type='text' name="txt_duplicata_alba_s_protesto" value="<?=$dupl_alba_s_prot;?>" title="Digite a Duplicata Alba s/ Protesto" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Contas + Cheques - Caução * 0,93
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Duplicata Tool:
        </td>
        <td>
            <input type='text' name="txt_duplicata_tool_s_protesto" value="<?=$dupl_tool_s_prot;?>" title="Digite a Duplicata Tool s/ Protesto" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Contas + Cheques - Caução * 0,93
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Atrasados > 60 e < 180 dias:
        </td>
        <td>
            <input type='text' name="txt_atrasados_maior_60_menor_180_dias" value="<?=$atrasados_maior_60_dias;?>" title="Digite os Atrasados > 60 e < 180 dias" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Vencidas + de 60 e - de 180 dias (Contas + Cheques - Caução * 0,93)
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estoque Aço:
        </td>
        <td>
            <input type='text' name="txt_estoque_aco" value="<?=$estoque_aco;?>" title="Digite o Estoque Aço" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estoque Rolamento:
        </td>
        <td>
            <input type='text' name="txt_estoque_rolamento" value="<?=$estoque_rolamento;?>" title="Digite o Estoque Rolamento" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empréstimo Funcionário:
        </td>
        <td>
            <input type='text' name="txt_emprestimo_funcionario" value="<?=$emprestimo_func;?>" title="Digite o Empréstimo Funcionário" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Sandra' style='cursor:help' color='darkblue'>
                <b>S</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Empréstimo R/S:
            </td>
            <td>
                    <input type='text' name="txt_emprestimo_rs" value="<?=$emprestimo_rs;?>" title="Digite o Empréstimo R/S" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
                    <font title='Sandra' style='cursor:help' color='darkblue'>
                            <b>S</b>
                    </font>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Saldo K2 <b>at&eacute; hoje:</b>
        </td>
        <td>
            <input type='text' name="txt_saldo_k2" value="<?=$saldo_k2;?>" title="Digite o Saldo K2" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                <b>CC - Parcerias (Saldo a Favor Albafer até hoje) </b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Saldo Carine<b> at&eacute; hoje:</b>
        </td>
        <td>
            <input type='text' name="txt_saldo_carine" value="<?=$saldo_carine;?>" title="Digite o Saldo Carine" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                <b>CC - Parcerias (Saldo a Favor Albafer até hoje) </b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Produtos Acabados:
        </td>
        <td>
            <input type='text' name="txt_produtos_acabados" value="<?=$produtos_acabados;?>" title="Digite os Produtos Acabados" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Semi Acabado Produzido:
        </td>
        <td>
            <input type='text' name="txt_semi_acabado_produzido" value="<?=$semi_acabado_prod;?>" title="Digite o Semi Acabado Produzido" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Estimado
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importações:
        </td>
        <td>
            <input type='text' name="txt_importacoes" value="<?=$importacoes;?>" title="Digite as Importações" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Sandra' style='cursor:help' color='darkblue'>
                <b>S</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Semi Acabado Comprado:
        </td>
        <td>
            <input type='text' name="txt_semi_acabado_comprado" value="<?=$semi_acabado_comp;?>" title="Digite o Semi Acabado Comprado" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font title='Roberto' style='cursor:help' color='darkblue'>
                <b>R</b> Sem Dados
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contas à Pagar Alba:
        </td>
        <td>
            <input type='text' name="txt_contas_apagar_alba" value="<?=$contas_apagar_alba;?>" title="Digite as Contas à Pagar Alba" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Total por Empresa 
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contas à Pagar Tool:
        </td>
        <td>
            <input type='text' name="txt_contas_apagar_tool" value="<?=$contas_apagar_tool;?>" title="Digite as Contas à Pagar Tool" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Total por Empresa 
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contas à Pagar Grupo:
        </td>
        <td>
            <input type='text' name="txt_contas_apagar_grupo" value="<?=$contas_apagar_grupo;?>" title="Digite as Contas à Pagar Grupo" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Total por Empresa 
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contas à Pagar Sandra:
        </td>
        <td>
            <input type='text' name="txt_contas_apagar_sandra" value="<?=$contas_apagar_sandra;?>" title="Digite as Contas à Pagar Sandra" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
            <font color='darkblue'>
                Conta_corrente.xls
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dif. Contas à Pagar Carine:
        </td>
        <td>
            <input type='text' name="txt_dif_contas_pagar_carine" value="<?=$dif_contas_apagar_carine;?>" title="Digite a Dif. de Contas à Pagar Carine" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dif. Contas à Pagar K2:
        </td>
        <td>
            <input type='text' name="txt_dif_contas_pagar_k2" value="<?=$dif_contas_apagar_k2;?>" title="Digite a Dif. de Contas à Pagar K2" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dupl. à Receber > 30 dias:
        </td>
        <td>
            <input type='text' name="txt_duplicata_maior_30_dias" value="<?=$dup_areceber_maior_30;?>" title="Digite a Dupl. à Receber > 30 dias" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total de Empréstimos à Pagar:
        </td>
        <td>
            <input type='text' name="txt_total_emprestimos_pagar" value="<?=$total_emprestimos_pagar;?>" title="Digite o Total de Empréstimos à Pagar" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Mensal de Empréstimo:
        </td>
        <td>
            <input type='text' name="txt_valor_mensal_emprestimo" value="<?=$valor_mensal_emprestimo;?>" title="Digite o Valor Mensal de Empréstimo" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total Pago Consórcio-Taxa Adm:
        </td>
        <td>
            <input type='text' name="txt_total_pago_consorcio_taxa_adm" value="<?=$total_pago_consorcio_taxa_adm;?>" title="Digite o Total Pago Consórcio-Taxa Administrativo" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Mensal do Consórcio:
        </td>
        <td>
            <input type='text' name="txt_valor_mensal_consorcio" value="<?=$valor_mensal_consorcio;?>" title="Digite o Valor Mensal do Consórcio" size="13" maxlength="13" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
<?
    if(!empty($data_atualizacao)) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            Login: <?=$login;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Última Atualização: 
        <?
            if($data_atualizacao != '0000-00-00 00:00:00') echo data::datetodata(substr($data_atualizacao, 0, 10), '/').' - '.substr($data_atualizacao, 11, 8);
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='blue'>
                Valor Dólar do Dia R$: 
            </font>
            <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='blue'>
                Valor Euro do Dia R$: 
            </font>
            <?=number_format($valor_euro_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_caixa_alba.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar e Atualizar' title='Fechar e Atualizar' onClick='atualizar_abaixo()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Importação: Númerário(s) Pago(s) e não retirado(s) - FOB(s) não pago(s) (de Importação(ões) já retirado(s))
* Câmbio Liberado: Duplicata(s) já recebida(s) no ERP, ainda sem fechamento de câmbio.

<font color='darkblue'>
* Dif. Contas à Pagar Carine = Valor Mensal Parceira - (Somatório Contas à Pagar Carine (Alba + SGD) até dia 26 do mês subsequente) 
* Dif. Contas à Pagar K2 = (-Somatório Contas à Pagar K2 (Alba + SGD)) + Valor Mensal Parceria (70 mil) 
* Dupl. à Receber > 30 dias = [Total (Contas + Cheques - Caução * 0,93) do Relatório Contas à Receber 
c/ vencimento p/ daqui à 6 meses] - Total... c/ vencimento p/ daqui à 30 dias do <i><b>relatório Geral</b></i>.
</font>
</pre>