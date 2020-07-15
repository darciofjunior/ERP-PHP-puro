<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/financeiros.php');
require('../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>FORNECEDOR ALTERADO COM SUCESSO.</font>";

$id_fornecedor 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_fornecedor'] : $_GET['id_fornecedor'];
$pop_up 		= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
$detalhes 		= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['detalhes'] : $_GET['detalhes'];

if(isset($_POST['txt_nf_minimo'])) {
    $insc_estadual                  = str_replace('.', '', $_POST['txt_insc_estadual']);
    $adquirimos_material            = (!empty($_POST['chkt_adquirimos_material'])) ? 1 : 0;
//Tratamento com a opção de Simples Nacional ...
    $optante_simples_nacional       = (!empty($_POST['chkt_optante_simples_nacional'])) ? 'S' : 'N';
//Tratamento com a opção Não aparecer no Follow-UP de Compras ...
    $aparecer_follow_up             = (!empty($_POST['chkt_aparecer_follow_up'])) ? 'S' : 'N';
//Tratamento com a opção Despachante ...
    $despachante                    = (!empty($_POST['chkt_despachante'])) ? 'S' : 'N';        
    $ignorar_impostos_financiamento = (!empty($_POST['chkt_ignorar_impostos_financiamento'])) ? 'S' : 'N';

    $sql = "UPDATE `fornecedores` SET `insc_est` = '$insc_estadual', `rg` = '$_POST[txt_rg]', `orgao` = '$_POST[txt_orgao]', `produto` = '$_POST[txt_produto]', `nf_minimo_tt` = '$_POST[txt_nf_minimo]', `material_ha_debitar` = '$adquirimos_material', `financiamento_taxa` = '$_POST[txt_taxa_financiamento]', `financiamento_prazo_dias` = '$_POST[txt_prazo_dias]', `pedagio` = '$_POST[txt_pedagio]', `qtde_meses_estocagem` = '$_POST[txt_qtde_meses_estocagem]', `optante_simples_nacional` = '$optante_simples_nacional', `aparecer_follow_up` = '$aparecer_follow_up', `despachante` = '$despachante', `ignorar_impostos_financiamento` = '$ignorar_impostos_financiamento' WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$sql = "SELECT f.*, p.pais 
        FROM `fornecedores` f 
        INNER JOIN `paises` p ON p.id_pais = f.id_pais 
        WHERE f.id_fornecedor = '$id_fornecedor' LIMIT 1 ";
$campos_fornecedores = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Inscrição Estadual
    if(typeof(document.form.txt_insc_estadual.value) == 'object' && document.form.txt_insc_estadual.value != '') {
        if(!texto('form', 'txt_insc_estadual', '12', '1234567890.', 'INSCRIÇÃO ESTADUAL', '1')) {
            return false
        }
    }
//Taxa de Financiamento
    if(document.form.txt_taxa_financiamento.value != '') {
        if(!texto('form', 'txt_taxa_financiamento', '1', '0123456789,.', 'TAXA DE FINANCIAMENTO', '1')) {
            return false
        }
    }
//Prazo Dias
    if(document.form.txt_prazo_dias.value != '') {
        if(!texto('form', 'txt_prazo_dias', '1', '0123456789', 'PRAZO DE DIA(S)', '2')) {
            return false
        }
    }
//Pedágio Internacional ...
    if(document.form.txt_pedagio.value != '') {
        if(!texto('form', 'txt_pedagio', '1', '0123456789,.', 'PEDÁGIO INTERNACIONAL', '2')) {
            return false
        }
    }
//Pedágio Internacional ...
    if(document.form.txt_qtde_meses_estocagem.value != '') {	
        if(!texto('form', 'txt_qtde_meses_estocagem', '1', '0123456789,.', 'QTDE DE MESES DE ESTOCAGEM', '1')) {
            return false
        }
    }
//Aqui é para não reler a Tela de Baixo quando Clicar no Botão Salvar, a idéia é apenas reler pelo Botão X do Pop-UP ...
    return limpeza_moeda('form', 'txt_nf_minimo, txt_taxa_financiamento, txt_pedagio, ')
    document.form.submit()
}

function atualizar_abaixo() {
    if(typeof(window.top.opener.document.form) == 'object') {
        var valor = eval('<?=$valor;?>')
        if(document.form.nao_atualizar.value == 0 && valor == 1) window.top.opener.document.form.submit()
    }
}
</Script>
</head>
<body onLoad="document.form.txt_nf_minimo.focus()" onUnload="atualizar_abaixo()">
<form name='form' method='post' action='' onSubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar' value='0'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>' onclick="validar()">
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='detalhes' value='<?=$detalhes;?>'>
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Fornecedor
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            Raz&atilde;o Social:
        </td>
        <td width='50%'>
            Nome Fantasia:
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <font color="darkblue" size="-1">
                            <b><?=$campos_fornecedores[0]['razaosocial'];?></b>
                    </font>
            </td>
            <td>
                    <font color="darkblue" size="-1">
                            <b><?=$campos_fornecedores[0]['nomefantasia'];?></b>
                    </font>
            </td>
    </tr>
    <!--*******************Dados de Empresa*******************-->
    <?
            if(strlen($campos_fornecedores[0]['cnpj_cpf']) == 14) {//Se essa variável for passada por parâmetro através do CNPJ ou CPF verifico se o Cliente já existe no BD ...
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            PESSOA JURÍDICA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ:
        </td>
        <td>
            Inscrição Estadual: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color="darkblue" size="-1">
                <b><?=substr($campos_fornecedores[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_fornecedores[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_fornecedores[0]['cnpj_cpf'], 12, 2);?></b>
            </font>
        </td>
        <td>
            <input type='text' name="txt_insc_estadual" value="<?=$campos_fornecedores[0]['insc_est'];?>" title="Digite a Inscrição Estadual" size="20" maxlength="15" class='caixadetexto'>
        </td>
    </tr>
    <?
            }else if(strlen($campos_fornecedores[0]['cnpj_cpf']) == 11) {//Se essa variável for passada por parâmetro através do CNPJ ou CPF verifico se o Cliente já existe no BD ...
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            PESSOA FÍSICA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            CPF:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color="darkblue" size="-1">
                <b><?=substr($campos_fornecedores[0]['cnpj_cpf'], 0, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 3, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 6, 3).'-'.substr($campos_fornecedores[0]['cnpj_cpf'], 9, 2);?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            RG:
        </td>
        <td>
            Orgão:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_rg" size="20" value="<?=$campos_fornecedores[0]['rg'];?>" maxlength="10" title="Digite o RG" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_orgao" value="<?=$campos_fornecedores[0]['orgao'];?>" title="Digite o Órgão" size="20" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <?
            }
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            OUTRAS INFORMAÇÕES
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            NF Mínimo (Tratamento Térmico) R$:
        </td>
        <td>
            Taxa de Financiamento:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_nf_minimo" value="<?=number_format($campos_fornecedores[0]['nf_minimo_tt'], 2, ',', '.');?>" title="Digite o NF Mínimo (Tratamento Térmico)" size="15" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_taxa_financiamento" value="<?=number_format($campos_fornecedores[0]['financiamento_taxa'], 1, ',', '.');?>" title="Digite a Taxa de Financiamento" onKeyUp="verifica(this, 'moeda_especial', '1', '', event)" size="7" maxlength="6" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo Dias:
        </td>
        <td>
            Pedágio Internacional:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_prazo_dias" value="<?=$campos_fornecedores[0]['financiamento_prazo_dias'];?>" title="Digite o Prazo Dias" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" size="7" maxlength="6" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_pedagio" value="<?=number_format($campos_fornecedores[0]['pedagio'], 3, ',', '.');?>" title="Digite o Pedágio" maxlength="12" size="16" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Qtde de Meses de Estocagem:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name="txt_qtde_meses_estocagem" value="<?=$campos_fornecedores[0]['qtde_meses_estocagem'];?>" title="Digite a Qtde de Meses de Estocagem" maxlength="12" size="16" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?$checked = ($campos_fornecedores[0]['material_ha_debitar'] == 1) ? 'checked' : '';?>
            <input type='checkbox' name="chkt_adquirimos_material" value="1" title="Adquirimos Material à debitar Posteriormente" id="label" class='checkbox' <?=$checked;?>>
            <label for='label'>
                Adquirimos Material à debitar Posteriormente
            </label>
        </td>
        <td>
            <?$checked = ($campos_fornecedores[0]['ignorar_impostos_financiamento'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_ignorar_impostos_financiamento' id='chkt_ignorar_impostos_financiamento' value='S' class='checkbox' <?=$checked;?>>
            <label for='chkt_ignorar_impostos_financiamento'>
                Ignorar Impostos no Financiamento
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos_fornecedores[0]['optante_simples_nacional'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_optante_simples_nacional" value='S' title="Optante pelo Simples Nacional" id="label2" class='checkbox' <?=$checked;?>>
            <label for='label2'>
                Optante pelo Simples Nacional
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos_fornecedores[0]['aparecer_follow_up'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_aparecer_follow_up" value='S' title="Aparecer no Follow-UP de Compras" id="label3" class='checkbox' <?=$checked;?>>
            <label for='label3'>
                Aparecer no Follow-UP de Compras
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos_fornecedores[0]['despachante'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_despachante" value='S' title="É Despachante Comércio Exterior" id='label4' class='checkbox' <?=$checked;?>>
            <label for='label4'>
                É Despachante Comércio Exterior
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Produtos:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_produto' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos_fornecedores[0]['produto'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
        //Quando essa tela, for aberta como Detalhes, não exibo esses botões ...
            if($pop_up != 1) {
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.parent.location = 'alterar.php<?=$parametro;?>'" class='botao'>
                <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');" style="color:#ff9900;" class='botao'>
                <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
            }else {
        ?>
                <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.parent.close()" class='botao'>
        <?		
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>