<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$chkt_listar_todos_periodos = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['chkt_listar_todos_periodos'] : $_GET['chkt_listar_todos_periodos'];
?>
<html>
<head>
<title>.:: Consultar Abono(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function incluir_data_holerith() {
    nova_janela('../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '280', '800', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../vales/class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    listar_todos_periodos = (document.form.chkt_listar_todos_periodos.checked == true) ? 1 : 0
    document.location = '../abono/consultar.php?chkt_listar_todos_periodos='+listar_todos_periodos
}
</Script>
</head>
<body onload='document.form.txt_funcionario.focus()'>
<form name='form' method='post' action='itens.php'>
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Abono(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcionário
        </td>
        <td>
            <input type='text' name='txt_funcionario' title='Digite o Funcionário' size='40' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Holerith
        </td>
        <td>
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith" class="combo">
            <?
//Se esse Checkbox não estiver marcado, então eu só listo os Período Recentes ...
                if($chkt_listar_todos_periodos != 1) $condicao_data_atual = " WHERE `data` > '".date('Y-m-d')."' ";
//Só listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual ...
                $sql = "SELECT id_vale_data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        $condicao_data_atual ORDER BY data ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onClick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onClick='alterar_data_holerith()'>
            <?
                if($chkt_listar_todos_periodos == 1) $checked = 'checked';
            ?>
            <input type='checkbox' id='listar_todos_periodos' name='chkt_listar_todos_periodos' value='1' title='Listar Todos os Períodos' onclick='atualizar()' class='checkbox' <?=$checked;?>>
            <label for='listar_todos_periodos'>Listar Todos os Períodos</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Descontar
        </td>
        <td>
            <select name='cmb_descontar_pd_pf' title='Selecione o Descontar' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='PD'>PD</option>
                <option value='PF'>PF</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_nao_descontado' value='N' title='Somente não Descontado' id='id_somente_nao_descontado' class='checkbox' checked>
            <label for='id_somente_nao_descontado'>
                Somente não Descontado
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_funcionario.focus()" style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>