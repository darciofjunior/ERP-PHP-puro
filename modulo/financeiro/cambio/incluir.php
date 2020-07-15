<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CAMBIO INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>DATA INVÁLIDA.</font>";

if(!empty($_POST['txt_data'])) {
    $data_sys                           = date('Y-m-d H:i:s');
//Faço o Tratamento dessas Datas para poder gravar no Banco de Dados ...
    $data                               = data::datatodate($_POST['txt_data'], '-');
    $data_ptax                          = data::datatodate($_POST['txt_data_ptax'], '-');
    $dolar_do_custo                     = genericas::variavel(7);//Busco o Valor de Dólar do Custo ...
    $euro_do_custo                      = genericas::variavel(8);//Busco o Valor de Euro do Custo ...
    $variacao_maxima_admissivel_cambio  = genericas::variavel(67);

    $sql = "SELECT data 
            FROM `cambios` 
            ORDER BY id_cambio DESC LIMIT 1 ";
    $campos_cambios = bancos::sql($sql);
    $ultima_data    = $campos_cambios[0]['data'];
//Verifico se o Usuário não está inserindo uma Moeda Estrangeira com Data Inferior a da Última Inserção ...
    if($data < $ultima_data) {
        $valor = 2;
    }else {
        $diff_prec_dolar	= genericas::diff_porcentagem($_POST['txt_valor_dolar_dia'], $dolar_do_custo, $variacao_maxima_admissivel_cambio);
        $diff_prec_euro		= genericas::diff_porcentagem($_POST['txt_valor_euro_dia'], $euro_do_custo, $variacao_maxima_admissivel_cambio);
        //Variação Exagerada, o Valor da Moeda Estrangeira ultrapassou a variação Admissível ...
        if(($diff_prec_dolar == 1) || ($diff_prec_euro == 1)) {
            if(!class_exists('comunicacao')) require('../../../lib/comunicacao.php');
            /*Email Roberto => Ao incluir o cambio e c tiverem diferença superior a 5% (para + ou para -) 
            dos valores respectivos do Dólar ou Euro do Custo, mandar email ...*/
            $mensagem_email     = 'Os valores cambiais estão c/ diferenças superior à '.intval($variacao_maxima_admissivel_cambio).' % das variáveis de “moeda custo”.';
            $destino            = 'roberto@grupoalbafer.com.br';
            $assunto            = 'SCAN - Cambio Desatualizado - '.date('d-m-Y H:i:s');
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem_email);
        }
        $sql = "INSERT INTO `cambios` (`id_cambio`, `id_funcionario`, `valor_dolar_dia`, `valor_euro_dia`, `valor_dolar_ptax`, `valor_euro_ptax`, `data`, `data_ptax`, `datasys`) VALUES (NULL, '$_SESSION[id_login]', '$_POST[txt_valor_dolar_dia]', '$_POST[txt_valor_euro_dia]', '$_POST[txt_valor_dolar_ptax]', '$_POST[txt_valor_euro_ptax]', '$data', '$data_ptax', '$data_sys') ";
        bancos::sql($sql);
        $valor = 1;
    }
}
?>
<html>
<title>.:: Incluir Câmbio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data
    if(!data('form', 'txt_data', '4000', 'CADASTRO')) {
            return false
    }
//Data Ptax
    if(!data('form', 'txt_data_ptax', '4000', 'PTAX')) {
            return false
    }
//Valor Dólar do dia
    if(!texto('form', 'txt_valor_dolar_dia', '1', '0123456789,.', 'VALOR DÓLAR DIA', '2')) {
            return false
    }
//Dólar Ptax
    if(!texto('form', 'txt_valor_dolar_ptax', '1', '0123456789,.', 'VALOR DO DÓLAR PTAX', '2')) {
            return false
    }
//Valor do Euro dia
    if(!texto('form', 'txt_valor_euro_dia', '1', '0123456789,.', 'VALOR EURO DIA!', '2')) {
            return false
    }
//Euro Ptax
    if(!texto('form', 'txt_valor_euro_ptax', '1', '0123456789,.', 'VALOR DO EURO PTAX', '2')) {
            return false
    }
    return limpeza_moeda('form', 'txt_valor_dolar_dia, txt_valor_dolar_ptax, txt_valor_euro_dia, txt_valor_euro_ptax, ')
}

function adicionar_dia() {
    if(document.form.chkt_feriado.checked == true) {
        nova_data('document.form.txt_data_ptax', 'document.form.txt_data_ptax', 1)
    }else {
        document.form.txt_data_ptax.value = document.form.txt_data_antiga.value
    }
}
</Script>
<body onload='document.form.txt_data.focus()'>
<form name="form" method="post" action='' onSubmit='return validar()'>
<table width='60%' align="center" cellspacing ='1' cellpadding='1' border='0'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Câmbio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Data:</td>
        <td>Data Ptax:
            &nbsp;<input type="checkbox" name="chkt_feriado" class="checkbox" onclick='adicionar_dia()' id='feriado'>
            <label for='feriado'>Feriado</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="text" name="txt_data" value="<?=date('d/m/Y');?>" title="Digite a Data" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
        </td>
        <td>
        <?
            $dia_semana = date('w');
            if(($dia_semana + 1) == 6) {
                $nova_data = data::adicionar_data_hora(date('d/m/Y'), 3);
            }else {
                $nova_data = data::adicionar_data_hora(date('d/m/Y'), 1);
            }
        ?>
            <input type="text" name="txt_data_ptax" value="<?=$nova_data;?>" title="Digite a Data Ptax" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Valor Dólar dia R$: (UOL Câmbio)</td>
        <td>Valor Dólar Ptax R$: (B. Central)</td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="text" name="txt_valor_dolar_dia" title="Digite o Valor Dólar do Dia" maxlength="15" size="10" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type="text" name="txt_valor_dolar_ptax" title="Digite o Valor Dólar Pitax" maxlength="15" size="10" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Valor Euro Dia R$: (UOL Câmbio)</td>
        <td>Valor Euro Ptax R$: (B. Central)</td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="text" name="txt_valor_euro_dia" title="Digite o Valor Euro Dia" maxlength="15" size="10" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type="text" name="txt_valor_euro_ptax" title="Digite o Valor Euro Ptax" maxlength="15" size="10" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
<input type="hidden" name="txt_data_antiga" value="<?=$nova_data;?>">
</form>
</body>
</html>