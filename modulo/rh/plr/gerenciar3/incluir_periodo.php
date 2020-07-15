<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar3/opcoes.php', '../../../../');

if(!empty($_POST['txt_data_pagamento'])) {
//Muda o Formato das variáveis p/ poder gravar no BD ...
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final     = data::datatodate($_POST['txt_data_final'], '-');
    $data_pagamento = data::datatodate($_POST['txt_data_pagamento'], '-');
/*Na tabela de Relatórios atualizo a Data Final e Data de Pagamento de todos os funcionários que estão na 
Data Inicial do Pagamento especificado ...*/
    $sql = "INSERT INTO `plr_periodos` (`id_plr_periodo`, `data_inicial`, `data_final`, `data_pagamento`) values (NULL, '$data_inicial', '$data_final', '$data_pagamento') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'opcoes.php?valor=1'
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Incluir Período ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Data de Pagamento
    if(!data('form', 'txt_data_pagamento', '4000', 'PAGAMENTO')) {
        return false
    }
/*****************************Seguranças com as Datas*****************************/
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    var data_pagamento = document.form.txt_data_pagamento.value

    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_pagamento = data_pagamento.substr(6,4)+data_pagamento.substr(3,2)+data_pagamento.substr(0,2)

    data_inicial = eval(data_inicial)
    data_final = eval(data_final)
    data_pagamento = eval(data_pagamento)
//Comparações entre as Datas de Prazo do Período ...
    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Comparações entre as Data Final do Período e a Data de Pagamento ...
    if(data_pagamento < data_final) {
        alert('DATA DE PAGAMENTO INVÁLIDA !!!\n DATA DE PAGAMENTO MENOR DO QUE A DATA FINAL !')
        document.form.txt_data_pagamento.focus()
        document.form.txt_data_pagamento.select()
        return false
    }
//Desabilito p/ poder gravar no BD ...
    document.form.txt_data_inicial.disabled = false
    document.form.txt_data_final.disabled = false
}

</Script>
</head>
<body onload='document.form.txt_data_pagamento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Período
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Inicial: </b>
        </td>
        <td>
        <?
/*Aqui eu busco a última Data Final de PLR p/ sugerir mais abaixo a próxima 
Data de Incluir de Hora Extra ...*/
            $sql = "SELECT DATE_FORMAT(data_final, '%d/%m/%Y') AS data_final 
                    FROM `plr_periodos` 
                    ORDER BY id_plr_periodo DESC LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não existe nenhum Período cadastrado ...
                //Aqui eu busco as Datas do PLR de acordo com o Semestre Atual em que o usuário se encontra ...
                if(date('m') < 7) {//Significa que ainda estou no Primeiro Semestre ...
                    $data_inicial = '26/12/'.(date('Y') - 1);
                }else {
                    $data_inicial = '26/06/'.date('Y');
                }
            }else {//Já existe pelo menos 1 período cadastrado, sendo assim busco a Data Final + 1 ...
                $data_inicial = data::adicionar_data_hora($campos[0]['data_final'], 1);	
            }
        ?>
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' size='13' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Final: </b>
        </td>
        <?
            $mes = substr($data_inicial, 3, 2);
            $ano = substr($data_inicial, 6, 4);
            //Na Data Final sempre irá sugerir o último dia do Semestre da Data Inicial ...
            if($mes == '12') {//Significa que é o Primeiro Semestre ...
                $data_final = '25/06/'.($ano + 1);
            }else {//Segundo Semestre ...
                $data_final = '25/12/'.$ano;
            }
        ?>
        <td>
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' size='13' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Pagamento: </b>
        </td>
        <td>
            <input type='text' name='txt_data_pagamento' title='Digite a Data de Pagamento' size='13' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src='../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_pagamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes.php'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data_pagamento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>