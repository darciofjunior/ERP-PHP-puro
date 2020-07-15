<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp2 == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>BORDERO(S) JÁ EXISTENTE.</font>";

if(!empty($_POST['cmb_tipo_recebimento'])) {
    $data_sys   = date('Y-m-d H:i:s');
    $data       = data::datatodate($_POST['txt_data'], '-');
    
    /*Verifico se já existe algum Bordero cadastrado na Data digitada pelo Usuário na mesma Conta Corrente e 
    no mesmo Tipo de Recebimento ...*/
    $sql = "SELECT id_bordero 
            FROM `borderos` 
            WHERE SUBSTRING(`data`, 1, 10) = '$data' 
            AND `id_contacorrente` = '$_POST[cmb_conta_corrente]' 
            AND `id_tipo_recebimento` = '$_POST[cmb_tipo_recebimento]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe o bordero para a conta à receber ...
        $sql = "INSERT INTO `borderos` (`id_bordero`, `id_contacorrente`, `id_tipo_recebimento`, `data`, `data_sys`) VALUES (NULL, '$_POST[cmb_conta_corrente]', '$_POST[cmb_tipo_recebimento]', '$data', '$data_sys') ";
        bancos::sql($sql);
        $id_bordero = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('BORDERO(S) INCLUÍDO COM SUCESSO !')
        /*Já redireciono o usuário p/ a Tela de incluir as várias Contas da empresa do Menu se quiser 
        no Bordero que acabou de Incluir no Sys ...*/
        window.location = 'incluir_contas_em_bordero.php?passo=1&id_emp2=<?=$_POST['id_emp2'];?>&id_bordero=<?=$id_bordero;?>'
    </Script>
<?
    }else {//Já existe o Bordero ...
        $valor = 1;
    }
}
?>
<html>
<head>
<title>.:: Incluir Novo Bordero ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de Recebimento ...
    if(!combo('form', 'cmb_tipo_recebimento', '', 'SELECIONE UM TIPO DE RECEBIMENTO !')) {
        return false
    }
//Conta Corrente ...
    if(!combo('form', 'cmb_conta_corrente', '', 'SELECIONE UM BANCO / AGÊNCIA / CONTA CORRENTE !')) {
        return false
    }
//Data do Bordero ...
    if(!texto('form', 'txt_data', '10', '1234567890/', 'DATA DO BORDERO', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_tipo_recebimento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Novo Bordero para 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo Recebimento:</b>
        </td>
        <td> 
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' class='combo'>
            <?
                $sql = "SELECT id_tipo_recebimento, recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `status` = '1' 
                        AND `ativo` = '1' ORDER BY recebimento ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <b>Banco / Agência / Conta Corrente:</b>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione o Banco / Agência / Conta Corrente' class='combo'>
            <?
                $sql = "SELECT DISTINCT(cc.id_contacorrente) AS id_contacorrente, CONCAT(b.banco, ' / ', cod_agencia, ' | ', nome_agencia,' / ',conta_corrente) AS agencia 
                        FROM `bancos` b 
                        INNER JOIN `agencias` a ON a.`id_banco` = b.`id_banco` AND a.`ativo` = '1' 
                        INNER JOIN `contas_correntes` cc ON cc.`id_agencia` = a.`id_agencia` AND cc.`id_empresa` = '$id_emp2' 
                        WHERE b.`ativo` = '1' ORDER BY b.banco ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
   
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data:</b>
        </td>
        <td>
            <input type='text' name='txt_data' value='<?=date('d/m/Y');?>' title='Digite a Data' onkeyup="verifica(this, 'data', '','', event)" size='15' maxlength='10' class='caixadetexto'>&nbsp;
            <img src='../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_bordero.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>