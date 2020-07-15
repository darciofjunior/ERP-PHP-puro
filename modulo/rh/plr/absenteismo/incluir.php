<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/absenteismo/opcoes.php', '../../../../');

if(!empty($_POST['cmb_periodo'])) {
/***********************Controle p/ saber se já foi gerado Absenteismo***********************/
//Verifico se já foi gerado pelo menos 1 Registro de Absenteismo no Período especificado ...
    $sql = "SELECT id_plr_absenteismo 
            FROM `plr_absenteismos` 
            WHERE `id_plr_periodo` = '".$_POST['cmb_periodo']."' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe 1 Absenteismo neste Período ...
        $valor = 3;
    }else {//Ainda não foi gerado nenhum Absenteimo ...
//Aqui eu verifico se foi preenchida a Qtde de Faltas referente aos Registros ...
        for($i = 0; $i < $_POST['txt_qtde_registros']; $i++) {
/*Se o o registro anterior ao próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
//Enquanto não chegar na último registro, eu vou fazendo essa comparação ...
            if(($i + 1) < $_POST['txt_qtde_registros']) {
                if($_POST['txt_qtde_faltas_anual'][$i] > $_POST['txt_qtde_faltas_anual'][$i + 1]) $valor = 1;
            }
        }

        if($valor != 1) {//Significa que a parte de Faltas está corretamente preenchida
//Disparando o Loop ...
            for($i = 0; $i < $txt_qtde_registros; $i++) $insert_extendido.= " (null, '".$_POST['cmb_periodo']."', '".$_POST['txt_qtde_faltas_anual'][$i]."', '".$_POST['txt_valor_premio_anual'][$i]."', '".$_POST['txt_percentagem_premio'][$i]."'), ";
            $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando os Absenteismos ...
            $sql = "INSERT INTO `plr_absenteismos` (`id_plr_absenteismo`, `id_plr_periodo`, `abs_qtde_faltas_anual`, `abs_valor_premio_anual`, `percentagem_premio`) VALUES 
                    $insert_extendido ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'opcoes.php?valor=<?=$valor;?>'
    </Script>
<?
}
//Se o usuário selecionou um período, então ... 
if(!empty($_GET['cmb_periodo'])) {
//Verifico se esse período já não foi utilizado anteriormente na Tela de Absenteismos ...
    $sql = "SELECT id_plr_periodo 
            FROM `plr_absenteismos` 
            WHERE id_plr_periodo = '".$_GET['cmb_periodo']."' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Ainda não foi utilizado, sendo assim ...
        $id_plr_periodo = $campos[0]['id_plr_periodo'];
    }else {//Já foi utilizado, então não posso mais utilizar ...
        echo '<Script Language="JavaScript">alert("ESSE PERÍODO JÁ FOI UTILIZADO !")</Script>';
    }
}
//Quando acaba de carregar a Tela ...	
if(empty($id_plr_periodo)) {
//Sugere o próximo Período ainda não utilizado pela Tela de Absenteismo ...
    $sql = "SELECT plrp.id_plr_periodo 
            FROM `plr_periodos` plrp 
            WHERE plrp.`id_plr_periodo` NOT IN 
            (SELECT plra.id_plr_periodo 
                    FROM `plr_absenteismos` plra 
                    INNER JOIN `plr_periodos` plrp ON plrp.id_plr_periodo = plra.id_plr_periodo 
            )
            ORDER BY id_plr_periodo DESC LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_plr_periodo = $campos[0]['id_plr_periodo'];
}
?>
<html>
<title>.:: Incluir Absenteismo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Período ...
    if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PERÍODO !')) {
        return false
    }
//Qtde de Registros ...
    if(!texto('form', 'txt_qtde_registros', '1', '1234567890', 'QTDE DE REGISTROS', '1')) {
        return false
    }
//Se for Vázia a Qtde de Registros ...
    if(document.form.txt_qtde_registros.value == 0) {
        alert('QTDE DE REGISTROS INVÁLIDA !')
        document.form.txt_qtde_registros.focus()
        document.form.txt_qtde_registros.select()
        return false
    }
//Verifico se já carregou a Tela do Iframe com as caixinhas então ...
    if(typeof(document.form.txt_qtde_faltas_anual1) != 'object') {//Ainda não gerou os Registros ...
        alert('É NECESSÁRIO GERAR OS REGISTROS !')
        document.form.cmd_gerar_registros.focus()
        return false
    }else {//Significa que já foi gerado a tela de Registros ...
/*Aqui eu verifico se o N.º de Parcelas que foi gerada anteriormente está compatível com a Qtde de Parcelas 
Digitada ...*/
        if(document.form.txt_qtde_registros.value != document.form.qtde_registros_gerado.value) {
            alert('A QTDE DE REGISTROS QUE FOI GERADA ESTÁ INCOMPATÍVEL A QTDE DE DIGITADA !!!')
            document.form.txt_qtde_registros.focus()
            document.form.txt_qtde_registros.select()
            return false
        }
//Continuação ...
        var elementos = document.form.elements
//Verifico se as Demais caixas do Iframe estão preenchidas ...
        for(var i = 0; i < elementos.length; i++) {
//Qtde de Faltas Anual ...
            if(elementos[i].name == 'txt_qtde_faltas_anual[]' && elementos[i].disabled == false) {
                if(elementos[i].value == '') {
                    alert('DIGITE A QTDE DE FALTAS ANUAL !')
                    elementos[i].focus()
                    return false
                }
            }
        }
    }
//Tratando os Elementos antes p/ enviar p/ o BD ...
    for(var i = 0; i < elementos.length; i++) {
//Se o Tipo de Objeto for caixa de Texto ...
        if(elementos[i].type == 'text') {
            elementos[i].value = strtofloat(elementos[i].value)
            elementos[i].disabled = false
        }
    }
}

function gerar_registros() {
//Período ...
    if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PERÍODO !')) {
        return false
    }
//Qtde de Registros ...
    if(!texto('form', 'txt_qtde_registros', '1', '1234567890', 'QTDE DE REGISTROS', '1')) {
        return false
    }
    ajax('qtde_registros.php?qtde_registros='+document.form.txt_qtde_registros.value, 'div_qtde_registros')
}

function calcular_proximos_registros() {
//Verifico se a Primeira Linha está preenchida ...
//Qtde de Faltas Anual ...
    if(document.getElementById('txt_qtde_faltas_anual1').value == '') {
        alert('DIGITE A QTDE DE FALTAS ANUAL !')
        document.getElementById('txt_qtde_faltas_anual1').focus()
        return false
    }
    var percentagem = 100//Referente ao Primeiro Valor Digitado ...
    document.getElementById('txt_percentagem_premio1').value = percentagem//Fixa o 1º Elem.
    //Depois de preenchida a primeira linha, então eu somente copio os val p/ as demais linhas ...

    document.getElementById('txt_valor_premio_semestral1').value = eval(strtofloat(document.getElementById('txt_valor_premio_anual1').value)) / 2
    document.getElementById('txt_valor_premio_semestral1').value = arred(document.getElementById('txt_valor_premio_semestral1').value, 2, 1)

    for(var i = 2; i <= document.form.elements['txt_qtde_faltas_anual[]'].length; i++) {
        indice_obj_ant = (i - 1)//A primeira linha é digitada manualmente as outras se baseiam nessa ...
        percentagem-= 20//A cada valor eu vou decrementando 20 % do Valor Principal ...

        document.getElementById('txt_valor_premio_anual'+i).value = eval(strtofloat(document.getElementById('txt_valor_premio_anual1').value)) * percentagem / 100
        document.getElementById('txt_valor_premio_anual'+i).value = arred(document.getElementById('txt_valor_premio_anual'+i).value, 2, 1)

        document.getElementById('txt_valor_premio_semestral'+i).value = eval(strtofloat(document.getElementById('txt_valor_premio_anual'+i).value)) / 2
        document.getElementById('txt_valor_premio_semestral'+i).value = arred(document.getElementById('txt_valor_premio_semestral'+i).value, 2, 1)

        document.getElementById('txt_qtde_faltas_anual'+i).value = eval(document.getElementById('txt_qtde_faltas_anual'+indice_obj_ant).value) + 1		
        document.getElementById('txt_percentagem_premio'+i).value = percentagem
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_registros.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Absenteísmo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Período: </b>
        </td>
        <td>
            <select name='cmb_periodo' title='Selecione o Período' onchange="window.location = 'incluir.php?cmb_periodo='+this.value" class='combo'>
            <?
                $sql = "SELECT id_plr_periodo, CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), ' à ', DATE_FORMAT(data_final, '%d/%m/%Y')) AS periodo 
                        FROM `plr_periodos` ";
                echo combos::combo($sql, $id_plr_periodo);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Registros:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_registros' value='5' title='Digite a Qtde de Registros' onkeyup="verifica(this, 'moeda_especial', '0', '', event)" size='5' maxlength='3' class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_gerar_registros' value='Gerar Registros' title='Gerar Registros' onclick='gerar_registros()' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <div name='div_qtde_registros' id='div_qtde_registros'></div>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes.php'" class="botao">
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_qtde_registros.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>