<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');

session_start('funcionarios');
session_unregister('id_emp');
$GLOBALS['id_emp'] = $id_emp2;
session_start('funcionarios');
session_register('id_emp');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/todas_empresas/index.php';
}

segurancas::geral($endereco, '../../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Consultar Conta(s) Paga(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Se a Data de Emissão estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_emissao_inicial.value != '' || document.form.txt_data_emissao_final.value != '') {
//Data de Emissão Inicial
        if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISSÃO INICIAL')) {
            return false
        }
//Data de Emissão Final
        if(!data('form', 'txt_data_emissao_final', '4000', 'EMISSÃO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
        var data_emissao_final = document.form.txt_data_emissao_final.value
        data_emissao_inicial = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
        data_emissao_final = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
        data_emissao_inicial = eval(data_emissao_inicial)
        data_emissao_final = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA DE EMISSÃO FINAL INVÁLIDA !!!\n DATA DE EMISSÃO FINAL MENOR DO QUE A DATA DE EMISSÃO INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
    }
//Se a Data de Vencimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_vencimento_inicial.value != '' || document.form.txt_data_vencimento_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_vencimento_inicial = document.form.txt_data_vencimento_inicial.value
        var data_vencimento_final = document.form.txt_data_vencimento_final.value
        data_vencimento_inicial = data_vencimento_inicial.substr(6,4) + data_vencimento_inicial.substr(3,2) + data_vencimento_inicial.substr(0,2)
        data_vencimento_final = data_vencimento_final.substr(6,4) + data_vencimento_final.substr(3,2) + data_vencimento_final.substr(0,2)
        data_vencimento_inicial = eval(data_vencimento_inicial)
        data_vencimento_final = eval(data_vencimento_final)

        if(data_vencimento_final < data_vencimento_inicial) {
            alert('DATA DE VENCIMENTO FINAL INVÁLIDA !!!\n DATA DE VENCIMENTO FINAL MENOR DO QUE A DATA DE VENCIMENTO INICIAL !')
            document.form.txt_data_vencimento_final.focus()
            document.form.txt_data_vencimento_final.select()
            return false
        }
    }
//Se a Data de Recebimento estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
//Data de Vencimento Inicial
        if(!data('form', 'txt_data_inicial', '4000', 'RECEBIMENTO INICIAL')) {
            return false
        }
//Data de Vencimento Final
        if(!data('form', 'txt_data_final', '4000', 'RECEBIMENTO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_inicial = document.form.txt_data_inicial.value
        var data_final = document.form.txt_data_final.value
        data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
        data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
        data_inicial = eval(data_inicial)
        data_final = eval(data_final)

        if(data_final < data_inicial) {
            alert('DATA DE RECEBIMENTO FINAL INVÁLIDA !!!\n DATA DE RECEBIMENTO FINAL MENOR DO QUE A DATA DE RECEBIMENTO INICIAL !')
            document.form.txt_data_final.focus()
            document.form.txt_data_final.select()
            return false
        }
    }
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload='document.form.txt_fornecedor.focus()'>
<form name='form' method='post' action='../classes/itens.php' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Conta(s) Paga(s)
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name="txt_fornecedor" title="Digite o Fornecedor" size="40" maxlength="45" class='caixadetexto'> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name="txt_numero_conta" title="Digite o Número da Conta" size="20" maxlength="18" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name="txt_data_emissao_inicial" title="Digite a Data de Emissão Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name="txt_data_emissao_final" title="Digite a Data de Emissão Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name="txt_data_vencimento_inicial" title="Digite a Data de Vencimento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name="txt_data_vencimento_final" title="Digite a Data de Vencimento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Pagamento
        </td>
        <td>
            <input type='text' name="txt_data_inicial" title="Digite a Data de Pagamento Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">  até&nbsp; 
            <input type='text' name="txt_data_final" title="Digite a Data de Pagamento Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='16' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Semana
        </td>
        <td>
            <input type='text' name="txt_semana" title="Digite a Semana" size="12" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name="txt_bairro" title="Digite o Bairro" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name="txt_cidade" title="Digite a Cidade" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Caixa
        </td>
        <td>
            <select name="cmb_conta_caixa" title="Selecione a Conta Caixa" class='combo'>
            <?
                //Traz somente as contas caixas do Módulo Financeiro
                $sql = "SELECT id_conta_caixa_pagar, conta_caixa 
                        FROM contas_caixas_pagares 
                        WHERE ativo = '1' ORDER BY conta_caixa ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importação
        </td>
        <td>
            <select name="cmb_importacao" title="Selecione a Importação" class='combo'>
            <?
                $sql = "Select id_importacao, nome 
                        from importacoes 
                        where ativo = 1 order by nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_importacao' value="1" title='Somente Importação' id='label1' class='checkbox'>
            <label for="label1">Somente Importação</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.reset();document.form.txt_fornecedor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>