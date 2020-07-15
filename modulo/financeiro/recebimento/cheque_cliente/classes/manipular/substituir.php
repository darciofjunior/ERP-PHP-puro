<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=1';
    $endereco_volta = 'opcoes.php?id_emp2=1';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=2';
    $endereco_volta = 'opcoes.php?id_emp2=2';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=4';
    $endereco_volta = 'opcoes.php?id_emp2=4';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CHEQUE SUBSTITUIDO COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(cc.`id_cheque_cliente`), cc.* 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
                    INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` AND cr.`id_empresa` = '$id_emp2' 
                    WHERE cc.`num_cheque` LIKE '%$txt_consultar%' 
                    AND cc.`status` IN (1, 2) 
                    AND cc.`ativo` = '1' ORDER BY cc.`data_vencimento` ";
        break;
        default:
            $sql = "SELECT DISTINCT(cc.`id_cheque_cliente`), cc.* 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
                    INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` AND cr.`id_empresa` = '$id_emp2' 
                    WHERE cc.`status` IN (1, 2) 
                    AND cc.`ativo` = '1' ORDER BY cc.`data_vencimento` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'substituir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cheque(s) de Cliente para Substituir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cheque(s) de Cliente para Substituir
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.∫ Cheque
        </td>
        <td>
            Banco
        </td>
        <td>
            Correntista
        </td>
        <td>
            Valor
        </td>
        <td>
            Data Venc.
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "substituir.php?passo=2&id_cheque_cliente=".$campos[$i]['id_cheque_cliente'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'substituir.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    //Aqui o sistema traz o N.∫ do Cheque "Antigo" passado por par‚metro ...
    $sql = "SELECT valor, num_cheque 
            FROM `cheques_clientes` 
            WHERE `id_cheque_cliente` = '$_GET[id_cheque_cliente]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Substituir Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de CobranÁa
    if(!combo('form', 'cmb_tipo_cobranca', '', 'SELECIONE O TIPO DE COBRAN«A !')) {
        return false
    }
//N˙mero Cheque
    if(!texto('form', 'txt_num_cheque', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’0123456789 ', 'N⁄MERO DO CHEQUE', '2')) {
        return false
    }
//Banco
    if(!texto('form', 'txt_banco', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’&-/.,_* ', 'BANCO', '2')) {
        return false
    }
//Correntista
    if(!texto('form', 'txt_correntista', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’&-/.,_* ', 'CORRENTISTA', '2')) {
        return false
    }
//Data de Vencimento
    if(!data('form', 'txt_data_vencimento' , '4000', 'VENCIMENTO')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_num_cheque.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3'?>" onsubmit='return validar()'>
<input type='hidden' name='id_cheque_cliente' value='<?=$_GET[id_cheque_cliente];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Substituir Cheque N.∫ 
            <font color='yellow'>
                <?=$campos[0]['num_cheque'];?> - <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Cheque:
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($campos[0]['valor'], 2, ',', '.');?>' title='Valor' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de CobranÁa:</b>
        </td>
        <td>
            <select name='cmb_tipo_cobranca' title='Selecione o Tipo de CobranÁa' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' selected>Carteira</option>
                <option value='1'>CobranÁa Banc·ria</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.∫ do Cheque:</b>
        </td>
        <td>
            <input type='text' name='txt_num_cheque' title='Digite o N˙mero do Cheque' maxlength='20' size='21' onkeyup="verifica(this, 'aceita', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’0123456789 ', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type='text' name='txt_banco' title='Digite o Banco' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Correntista:</b>
        </td>
        <td>
            <input type='text' name='txt_correntista' title='Digite o Correntista' size='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            <input type='text' name='txt_data_vencimento' title='Digite a Data de Vencimento' maxlength='10' size='12' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            HistÛrico:
        </td>
        <td>
            <textarea name='txt_historico' title='Digite o HistÛrico' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt' title='Voltar' onclick="window.location = 'substituir.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $data_vencimento    = data::datatodate($_POST['txt_data_vencimento'], '-');
    $historico          = $_POST['txt_historico'].' - Cheque Substituido ';
    //Aqui o sistema faz a SubstituiÁ„o do Cheque Antigo pelo Cheque Novo ...
    $sql = "UPDATE `cheques_clientes` SET `num_cheque` = '$_POST[txt_num_cheque]', `banco` = '$_POST[txt_banco]', `correntista` = '$_POST[txt_correntista]', `historico` = CONCAT(`historico`, '$historico'), `tipo_cobranca` = '$_POST[cmb_tipo_cobranca]', data_vencimento = '$data_vencimento', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_cheque_cliente` = '$_POST[id_cheque_cliente]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) de Cliente para Substituir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) de Cliente para Substituir
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Cheque por N˙mero do Cheque' onclick='document.form.txt_consultar.focus()' id='opt1' checked>
            <label for='opt1'>N˙mero do Cheque</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='2' title='Consultar todos os Cheques' onclick='limpar()' id='todos' class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt' title='Voltar' onclick="window.location = '<?=$endereco_volta;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>