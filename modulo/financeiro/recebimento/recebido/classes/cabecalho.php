<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/todas_empresas/index.php';
}

segurancas::geral($endereco, '../../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que o usuário já passou pela tela de contas à receber antes
    if($itens == 1) {
        session_start('funcionarios');
        $id_emp2 = $id_emp;
        session_unregister('id_emp');
    }
?>
    <Script language = 'JavaScript'>
        window.location = 'itens.php?id_emp2=<?=$id_emp2;?>&txt_cliente=<?=$_POST['txt_cliente'];?>&txt_descricao_conta=<?=$_POST['txt_descricao_conta'];?>&cmb_representante=<?=$_POST['cmb_representante'];?>&txt_numero_conta=<?=$_POST['txt_numero_conta'];?>&txt_data_emissao_inicial=<?=$_POST['txt_data_emissao_inicial'];?>&txt_data_emissao_final=<?=$_POST['txt_data_emissao_final'];?>&txt_data_vencimento_inicial=<?=$_POST['txt_data_vencimento_inicial'];?>&txt_data_vencimento_final=<?=$_POST['txt_data_vencimento_final'];?>&txt_data_inicial=<?=$_POST['txt_data_inicial'];?>&txt_data_final=<?=$_POST['txt_data_final'];?>&cmb_ano=<?=$_POST['cmb_ano'];?>&txt_semana=<?=$_POST['txt_semana'];?>&txt_data_cadastro=<?=$_POST['txt_data_cadastro'];?>&cmb_banco=<?=$_POST['cmb_banco'];?>&cmb_tipo_recebimento=<?=$_POST['cmb_tipo_recebimento'];?>&chkt_somente_exportacao=<?=$_POST['chkt_somente_exportacao'];?>&txt_bairro=<?=$_POST['txt_bairro'];?>&txt_cidade=<?=$_POST['txt_cidade'];?>&cmb_uf=<?=$_POST['cmb_uf'];?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Conta(s) Recebida(s) ::.</title>
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
}
</Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Conta(s) Recebida(s)
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
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' size='40' maxlength='45' class='caixadetexto'> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descrição da Conta
        </td>
        <td>
            <input type='text' name='txt_descricao_conta' title='Digite a Descrição da Conta' size='40' maxlength='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Conta
        </td>
        <td>
            <input type='text' name='txt_numero_conta' title='Digite o Número da Conta' size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name='txt_data_emissao_inicial' title='Digite a Data de Emissão Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_emissao_final' title='Digite a Data de Emissão Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_inicial' title='Digite a Data de Vencimento Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_vencimento_final' title='Digite a Data de Vencimento Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Recebimento
        </td>
        <td>
            <input type='text' name='txt_data_inicial' title='Digite a Data de Recebimento Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_final' title='Digite a Data de Recebimento Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data do Cadastro
        </td>
        <td>
            <input type='text' name='txt_data_cadastro' title='Digite a Data de Cadastro' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_cadastro&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencido Em
        </td>
        <td>
            <select name='cmb_ano' title='Selecione o Ano' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                for($i = 2004; $i <= date('Y') + 6; $i++) {
            ?>
                <option value='<?=$i;?>'><?=$i;?></option>
            <?
                }
            ?>
            </select>
            Semana 
            <input type='text' name='txt_semana' title='Digite a Semana' size='10' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, '');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Banco
        </td>
        <td>
            <select name='cmb_banco' title='Selecione o Banco' class='combo'>
            <?
                $sql = "SELECT id_banco, banco 
                        FROM `bancos` 
                        WHERE `ativo` = '1' ORDER BY banco ";
                echo combos::combo($sql);
            ?>
            </select> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Recebimento
        </td>
        <td>
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' class='combo'>
            <?
                $sql = "SELECT id_tipo_recebimento, recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY recebimento ";
                echo combos::combo($sql, '');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_exportacao' value='1' title='Somente Exportação' id='label1' class='checkbox'>
            <label for='label1'>Somente Exportação</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.reset();document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='itens' value='<?=$itens;?>'>
</form>
</body>
</html>
<?}?>