<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE AVULSO ALTERADO COM SUCESSO.</font>";

$id_vale_dp = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_vale_dp'] : $_GET['id_vale_dp'];

if(!empty($_POST['txt_valor'])) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_sys = date('Y-m-d H:i:s');
//Alterando o Vale na Tabela ...
    $sql = "UPDATE `vales_dps` SET `valor` = '$_POST[txt_valor]', `data_debito` = '$_POST[cmb_data_holerith]', `observacao` = '$_POST[txt_observacao]', `data_sys` = '$data_sys' WHERE `id_vale_dp` = '$_POST[id_vale_dp]' LIMIT 1 ";
    bancos::sql($sql);   
    $valor = 1;
}

//Busca dados de vale através do id_vale_dp passado por parâmetro ...
$sql = "SELECT * 
        FROM `vales_dps` 
        WHERE `id_vale_dp` = '$id_vale_dp' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale Avulso ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Data do Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE A DATA DE HOLERITH !')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    atualizar_abaixo()
    return limpeza_moeda('form', 'txt_valor, ')
}

function incluir_data_holerith() {
    nova_janela('../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.recarregar_tela()
}
</Script>
</head>
<body onload='document.form.txt_valor.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_vale_dp' value='<?=$id_vale_dp;?>'>
<input type='hidden' name='nao_atualizar'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='passo' onclick="atualizar()">
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale Avulso
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Funcionário:</b>
        </td>
        <td>
        <?
            $sql = "SELECT id_empresa, nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
//Controle com o Pop-Up ... 
            $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[0]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        ?>
            <a href="<?=$url;?>" title="Detalhes Funcionário" class="link">
                <?=$campos_empresa[0]['nome'];?>
            </a>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <?=genericas::nome_empresa($campos_empresa[0]['id_empresa']);?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title='Digite o Valor' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith" class="combo">
            <?
                $data_atual_menos_60 = data::adicionar_data_hora(date('d/m/Y'), -60);
                $data_atual_menos_60 = data::datatodate($data_atual_menos_60, '-');
/*Só listo nessa Combo as Datas de Holeriths que sejam maiores que a Data de 2 meses atrás, eu só 
mantenho esses 2 meses ainda p/ que se possa consultar algum dado de vale antigo dentro desse período ...*/
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_atual_menos_60' ORDER BY data ";
                echo combos::combo($sql, $campos[0]['data_debito']);
            ?>
            </select>
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/incluir.png" border='0' title="Incluir Data de Holerith" alt="Incluir Data de Holerith" onClick="incluir_data_holerith()">
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Data de Holerith" alt="Alterar Data de Holerith" onClick="alterar_data_holerith()">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='55' rows='2' maxlength='110' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_valor.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>