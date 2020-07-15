<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='atencao'>CONS”RCIO BLOQUEADO !!! J¡ FOI GERADO VALE PARA ESTE CONS”RCIO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONS”RCIO ALTERADO COM SUCESSO.</font>";

if($_POST['passo'] == 1) {
/*Aqui eu verifico se j· foi gerado Vale p/ este ConsÛrcio, caso foi gerado, ent„o eu n„o posso alterar
os dados de CabeÁalho do ConsÛrcio ...*/
    $sql = "SELECT gerado_vale 
            FROM `consorcios` 
            WHERE `id_consorcio` = '$_POST[id_consorcio]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $gerado_vale    = $campos[0]['gerado_vale'];
    if(strtoupper($gerado_vale) == 'S') {//N„o posso estar + alterando os dados pq j· foi gerado vale ...
        $valor = 1;
    }else {//Ainda n„o foi gerado vale, sendo assim posso alterar os dados normalmente ...
        $observacao =  ucfirst(strtolower($_POST['txt_observacao']));
        $sql = "UPDATE `consorcios` SET `nome_grupo` = '$_POST[txt_nome_grupo]', `valor` = '$_POST[txt_valor]', `juros` = '$_POST[txt_juros]', `data_inicial` = '$_POST[cmb_data_holerith]', `meses`= '$_POST[txt_meses]', `observacao` = '$observacao' WHERE `id_consorcio` = '$_POST[id_consorcio]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_cabecalho.php?id_consorcio=<?=$_POST['id_consorcio'];?>&valor=<?=$valor;?>'
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    </Script>
<?
}

//Busco dados do "id_consorcio" passado por par‚metro ...
$sql = "SELECT * 
        FROM `consorcios` 
        WHERE `id_consorcio` = '$_GET[id_consorcio]' LIMIT 1 ";
$campos     = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar CabeÁalho do ConsÛrcio ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Nome do Grupo
    if(!texto('form', 'txt_nome_grupo', '3', '0123456789„ı√’·ÈÌÛ˙¡…Õ”⁄abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ« _-', 'NOME DO GRUPO', '2')) {
        return false
    }
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Juros
    if(!texto('form', 'txt_juros', '1', '1234567890,.', 'JUROS', '2')) {
        return false
    }
//Data do Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE A DATA DE HOLERITH !')) {
        return false
    }
//Meses
    if(!texto('form', 'txt_meses', '1', '1234567890', 'QUANTIDADE DE MESES', '1')) {
        return false
    }
    document.form.passo.value = 1
    return limpeza_moeda('form', 'txt_valor, txt_juros, ')
}

function incluir_data_holerith() {
    nova_janela('../vales/class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
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
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_nome_grupo.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_consorcio' value='<?=$_GET['id_consorcio'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--Esse hidden È um controle de Tela-->
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar CabeÁalho do ConsÛrcio N.∫ 
            <font color='yellow'>
                <?=$_GET['id_consorcio']?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome do Grupo:</b>
        </td>
        <td>
            <input type='text' name='txt_nome_grupo' value='<?=$campos[0]['nome_grupo'];?>' title='Digite o Nome do Grupo' size='26' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($campos[0]['valor'], 2, ',', '.');?>' title='Digite o Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Juros:</b>
        </td>
        <td>
            <input type='text' name='txt_juros' value='<?=number_format($campos[0]['juros'], 2, ',', '.');?>' title='Digite os Juros' size='6' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith Inicial:</b>
        </td>
        <td>
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith Inicial" class="combo">
            <?
                $data_atual = date('Y-m-d');
//SÛ listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual ...
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` > '$data_atual' ORDER BY data ";
                echo combos::combo($sql, $campos[0]['data_inicial']);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick='alterar_data_holerith()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Meses:</b>
        </td>
        <td>
            <input type='text' name='txt_meses' value='<?=$campos[0]['meses'];?>' title='Digite os Meses' size='8' maxlength='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome_grupo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>