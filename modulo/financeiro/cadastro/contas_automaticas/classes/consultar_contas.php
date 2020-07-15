<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');

if($id_empresa_menu == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
}else if($id_empresa_menu == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
}else if($id_empresa_menu == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Consultar Conta(s) Automática(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
</head>
<body onload='document.form.txt_numero_conta.focus()'>
<form name='form' method='post' action='../classes/itens.php'>
<input type='hidden' name='id_empresa_menu' value='<?=$id_empresa_menu;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Conta(s) Automática(s)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' title='Digite o Fornecedor' size='55' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name='txt_numero_conta' title='Digite o Número da Conta' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_inicial' title='Digite a Data de Vencimento Inicial' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_vencimento_final' title='Digite a Data de Vencimento Final' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src= '../../../../../imagem/calendario.gif' width='12' height='12' border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_mostrar_contas_inativas' value='S' title='Mostrar Conta(s) Inátiva(s)' id='chkt_mostrar_contas_inativas' class='caixadetexto'>
            <label for='chkt_mostrar_contas_inativas'>
                Mostrar Conta(s) Inativa(s)
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_conta.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_incluir_conta_automatica' value='Incluir Conta Automática' title='Incluir Conta Automática' onclick="nova_janela('../classes/cadastrar_conta/consultar_fornecedor.php?id_empresa_menu=<?=$id_empresa_menu;?>', 'POP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:black' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>