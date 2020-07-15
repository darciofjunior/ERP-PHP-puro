<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>DATA DO BL ALTERADA COM SUCESSO.</font>";

if(!empty($_POST['txt_data_bl'])) {
    $data_bl = data::datatodate($_POST['txt_data_bl'], '-');
    foreach($_POST['hdd_conta_receber'] as $i => $hdd_conta_receber) {
//Aqui eu já atualizo as Datas de Venc. e B/L das próprias Duplicatas do financeiro 'Contas Receberes'
        $data_venc_prorrog = data::datatodate($_POST['txt_data_venc_alterada'][$i], '-');
        $sql = "UPDATE `contas_receberes` SET `data_vencimento_alterada` = '$data_venc_prorrog', `data_bl` = '$data_bl' WHERE `id_conta_receber` = '$hdd_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
    }
//Busca do id_nf da Nota Fiscal do Faturamento
    $sql = "SELECT id_nf 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$_POST[id_conta_receber]' 
            AND `id_nf` > '0' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
        //Aqui eu já atualizo a Data do B/L da Própria Nota Fiscal de lá Faturamento do Paçoquinha ...
        $sql = "UPDATE `nfs` SET `data_bl` = '$_POST[txt_data_bl]' WHERE `id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Procedimento normal quando carrega a Tela ...
$id_conta_receber = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_conta_receber'] : $_GET['id_conta_receber'];

//Seleção dos dados de contas à receber - aqui é genérico para os 3 tipos de casos ...
$sql = "SELECT id_empresa, id_cliente, id_tipo_moeda, num_conta, data_emissao, data_bl 
        FROM `contas_receberes` 
        WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota 	= $campos[0]['id_empresa'];
$id_tipo_moeda      = $campos[0]['id_tipo_moeda'];
$numero_nf          = $campos[0]['num_conta'];
$ultimo_digito      = substr($numero_nf, strlen($numero_nf) - 1, 1);
//Aqui faz um tratamento do Número da Nota para depois poder puxar as demais vias de duplicatas
if($ultimo_digito == 'A' || $ultimo_digito == 'B' || $ultimo_digito == 'C' || $ultimo_digito == 'D') {
    $numero_nf      = substr($numero_nf, 0, strlen($numero_nf) - 1);
}
$data_bl            = data::datetodata($campos[0]['data_bl'], '/');
$id_cliente         = $campos[0]['id_cliente'];
$ano_emissao        = substr($campos[0]['data_emissao'], 0, 4);
?>
<html>
<head>
<title>.:: Alterar Data do B/L ::.</title>
<meta http-equiv ='Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv ='pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data do B/L
    if(!data('form', 'txt_data_bl', '4000', 'B/L')) {
            return false
    }
//Verifica se as Novas Datas não são menor do que a que já era antes das Alterações
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_conta_receber[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_conta_receber[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        data_vencimento             = document.getElementById('txt_data_venc'+i).value
        data_vencimento_alterada    = document.getElementById('txt_data_venc_alterada'+i).value
/*Aqui inverte as data para transformar em número e poder fazer a comparação p/ ver se as Datas não
ficaram menores do que ainda já são*/
        data_vencimento             = data_vencimento.substr(6, 4) + data_vencimento.substr(3, 2) + data_vencimento.substr(0, 2)
        data_vencimento_alterada    = data_vencimento_alterada.substr(6, 4) + data_vencimento_alterada.substr(3, 2) + data_vencimento_alterada.substr(0, 2)
//Aqui estou garantindo que este tipo de Dado é um número realmente
        data_vencimento             = eval(data_vencimento)
        data_vencimento_alterada    = eval(data_vencimento_alterada)
//Aqui eu verifico se a nova Data de Prorrogação, não é menor do que a que já tinhamos antigamente
        if(data_vencimento_alterada < data_vencimento) {
            alert('DATA DE VENCIMENTO / ALTERADA INVÁLIDA !\nDATA DE VENCIMENTO / ALTERADA < DO QUE A DATA ANTERIOR !')
            document.getElementById('txt_data_venc_alterada'+i).focus()
            document.getElementById('txt_data_venc_alterada'+i).select()
            return false
        }
    }
//Tratamento para gravar no BD
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_data_venc_alterada'+i).disabled = false
    }
//Aqui é para não atualizar a Tela abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}

//Sempre que terminar de digitar a Data do B/L, então o sistema vai sugerir uma nova Data de Vencimento
function nova_data_vencimento() {
//Só vai fazer esse recálculo, se a Data estiver toda digitada ...
    if(document.form.txt_data_bl.value.length == 10 && document.form.txt_data_bl.value != '00/00/0000') {
//Aqui vai fazer as adaptações para as novas datas
        var elementos = document.form.elements
        //Prepara a Tela p/ poder gravar no BD ...
	if(typeof(elementos['hdd_conta_receber[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
	}else {
            var linhas = (elementos['hdd_conta_receber[]'].length)
	}
        for(var i = 0; i < linhas; i++) {
            data_emissao        = document.getElementById('txt_data_emissao'+i).value
            data_vencimento     = document.getElementById('txt_data_venc'+i).value
            document.form.caixa_auxiliar.value  = document.getElementById('txt_data_venc_alterada'+i).value
            prazo = diferenca_datas(data_emissao, data_vencimento)
/*Aqui eu preciso jogar o valor da variável p/ um hidden caixa auxiliar pq a função não aceita trabalhar
com 'txt_data_venc'+i, 'txt_data_venc_alterada'+i*/
            nova_data('document.form.txt_data_bl', 'document.form.caixa_auxiliar', prazo)
            document.getElementById('txt_data_venc_alterada'+i).value = document.form.caixa_auxiliar.value
        }
    }
//Se estiver em um desses casos ele redefine a Tela
    if(document.form.txt_data_bl.value == '' || document.form.txt_data_bl.value == '00/00/0000') {
        document.form.reset()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
//Aqui vai fazer as adaptações para as novas datas
        var elementos = document.form.elements
        //Prepara a Tela p/ poder gravar no BD ...
	if(typeof(elementos['hdd_conta_receber[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
	}else {
            var linhas = (elementos['hdd_conta_receber[]'].length)
	}
        for(var i = 0; i < linhas; i++) {
/*Iguala a caixa de Venc. do Pop-Up de baixo com o da Data de Venc. dessa tela atual, pq depois 
a tela de baixo é gravada no banco de dados, para o usuário não perder as alterações que ele alterou*/
            opener.document.form.txt_data_vencimento.value = document.getElementById('txt_data_venc_alterada'+i).value
        }
        if(typeof(window.opener.document.form.nao_atualizar) == 'object') {
            opener.document.form.nao_atualizar.value = 1
            opener.document.form.submit()
        }
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********************************************-->
<input type='hidden' name='id_conta_receber' value='<?=$id_conta_receber;?>'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar Data do B/L
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Data do B/L:</b>
        </td>
        <td width='80%' colspan='4'>
            <input type='text' name='txt_data_bl' value='<?=$data_bl;?>' title='Data do B/L' size='12' maxlength="10" onkeyup="verifica(this, 'data', '', '', event);if(this.value.length == 10) {nova_data_vencimento()}" onblur="if(this.value.length < 10) {document.form.reset()}else {nova_data_vencimento()}" class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_bl&tipo_retorno=1&chamar_funcao=2&caixa_auxiliar=executar', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<?
/*Aki lista todas as contas que possui o mesmo número, em que só varia as Duplicatas e que sejam 
importadas do Faturamento para o Financeiro*/
    $sql = "SELECT * 
            FROM `contas_receberes` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_empresa` = '$id_empresa_nota' 
            AND (`num_conta` LIKE '".$numero_nf."_' OR `num_conta` LIKE '$numero_nf') 
            AND SUBSTRING(`data_emissao`, 1, 4) = '$ano_emissao' ORDER BY num_conta LIMIT 4 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhadestaque" align='center'>
        <td>
            N.&ordm; da Duplicata
        </td>
        <td>
            Valor em 
        <?
            $sql = "SELECT simbolo 
                    FROM `tipos_moedas` 
                    WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
            $campos_moeda   = bancos::sql($sql);
            $simbolo_moeda  = $campos_moeda[0]['simbolo'];
            echo $simbolo_moeda;
        ?>
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data Venc (Original)
        </td>
        <td>
            Data Venc / Prorrog
        </td>
        <td>
            Situação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
//Aqui nessa caixa eu guardo o id_conta_receber para facilitar depois na hora em que submeter os valores
?>
            <input type='hidden' name='hdd_conta_receber[]' id='hdd_conta_receber<?=$i;?>' value='<?=$campos[$i]['id_conta_receber'];?>'>
            <?=$campos[$i]['num_conta'];?>
        </td>
        <td align='right'>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <input type='text' name='txt_data_emissao[]' id='txt_data_emissao<?=$i;?>' value='<?=data::datetodata($campos[$i]['data_emissao'], '/');?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_venc[]' id='txt_data_venc<?=$i;?>' value='<?=data::datetodata($campos[$i]['data_vencimento'], '/');?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_venc_alterada[]' id='txt_data_venc_alterada<?=$i;?>' value='<?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
        <?
            if($campos[$i]['status'] == 1) {//Duplicata que foi rec. de forma parcial
                echo '<b><font color="green">REC. PARCIAL</font></b>';
            }else if($campos[$i]['status'] == 2) {//Duplicata que foi rec. de forma total
                echo '<b><font color="red">REC. TOTAL</font></b>';
            }else {//Ainda não foi rec. nada dessa duplicata
                echo '<b><font color="blue">EM ABERTO</font></b>';
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_data_bl.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='executar' onclick='nova_data_vencimento()'>
<!--Utilizo essa caixa para a função nova_data_vencimento() em JS lá em cima do head-->
<input type='hidden' name='caixa_auxiliar'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>