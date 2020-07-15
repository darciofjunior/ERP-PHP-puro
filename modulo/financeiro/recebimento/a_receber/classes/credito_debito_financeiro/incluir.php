<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA ¿ RECEBER INCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com as vari·veis que vem por par‚metro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_nome_fantasia 	= $_POST['txt_nome_fantasia'];
        $txt_razao_social 	= $_POST['txt_razao_social'];
    }else {
        $txt_nome_fantasia 	= $_GET['txt_nome_fantasia'];
        $txt_razao_social 	= $_GET['txt_razao_social'];
    }
//Aqui eu listo todos os Clientes do Representante logado ...
    $sql = "SELECT DISTINCT(c.`id_cliente`), c.`cod_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
            c.`id_uf`, c.`endereco`, c.`cidade`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo` 
            FROM `clientes` c 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`ativo` = '1' ORDER BY c.razaosocial ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cliente(s) p/ Incluir Conta ‡ Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cliente(s) p/ Incluir Conta ‡ Receber - 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Tipo de Cliente
        </td>
        <td>
            Tel Com
        </td>
        <td>
            EndereÁo
        </td>
        <td>
            Cidade / UF
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?passo=2&id_cliente='.$campos[$i]['id_cliente'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='link'>
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['endereco'];
            //DaÌ sim printa o complemento ...
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT sigla 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf 	= bancos::sql($sql);
            echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo est· preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
//Busca o ˙ltimo Valor do DÛlar e Euro cadastrados no Sistema ...
    $valor_dolar    = genericas::moeda_dia('dolar');
    $valor_euro     = genericas::moeda_dia('euro');
?>
<html>
<head>
<title>.:: Incluir Conta ‡ Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular() {
    if(document.form.cmb_tipo_moeda.value == 2) {//Moeda DÛlar ...
         var valor_reajustado = eval(strtofloat(document.form.txt_valor.value)) * eval('<?=$valor_dolar;?>')
    }else if(document.form.cmb_tipo_moeda.value == 3) {//Moeda Euro ...
        var valor_reajustado = eval(strtofloat(document.form.txt_valor.value)) * eval('<?=$valor_euro;?>')
    }else {
        var valor_reajustado = eval(strtofloat(document.form.txt_valor.value))
    }
//Se existir Valor de Desconto da Duplicata, aplico este em cima do Valor Reajustado tambÈm ...
    if(document.form.txt_valor_desconto.value != '') {
        if(document.form.cmb_tipo_moeda.value == 2) {//Moeda DÛlar ...
            var valor_desconto = eval(strtofloat(document.form.txt_valor_desconto.value)) * eval('<?=$valor_dolar;?>')
        }else if(document.form.cmb_tipo_moeda.value == 3) {//Moeda Euro ...
            var valor_desconto = eval(strtofloat(document.form.txt_valor_desconto.value)) * eval('<?=$valor_euro;?>')
        }else {
            var valor_desconto = eval(strtofloat(document.form.txt_valor_desconto.value))
        }
        document.form.txt_valor_reajustado.value = valor_reajustado - valor_desconto
    }else {//N„o existe valor de Duplicata ...
        document.form.txt_valor_reajustado.value = valor_reajustado
    }
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
    if(document.form.opt_credito_debito[0].checked == false && document.form.opt_credito_debito[1].checked == false) {
        alert('SELECIONE UMA OP«√O DE "CR…DITO" OU "D…BITO" !')
        document.form.opt_credito_debito[0].focus()
        return false
    }
//N.∫ da Conta / Nota ...
    if(!texto('form', 'txt_num_conta', '3', '1234567890-/ABCDEFGHIJKLMNOPQRSTUVWXYZ_. ', 'N.∫ DA CONTA', '2')) {
        return false
    }
//Tipo de Recebimento ...
    if(!combo('form', 'cmb_tipo_recebimento', '', 'SELECIONE UM TIPO DE RECEBIMENTO !')) {
        return false
    }
//Tipo de Moeda ...
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
//Valor Nacional / Estrangeiro ...
    if(!texto('form', 'txt_valor', '1', '1234567890,.-', 'VALOR NACIONAL / ESTRANGEIRO', '2')) {
        return false
    }
//Valor Desconto ...
    if(document.form.txt_valor_desconto.value != '') {
        if(!texto('form', 'txt_valor_desconto', '1', '1234567890,.', 'DESCONTO DUPLICATA' ,'2')) {
            return false
        }
    }
//Data de Emiss„o ...
    if(!data('form', 'txt_data_emissao', '4000', 'EMISS√O')) {
        return false
    }
//Data de Vencimento ...
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
//Comiss„o Estornada ...    
    if(document.form.txt_comissao_estornada.value != '') {
        if(!texto('form', 'txt_comissao_estornada', '1', '-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ', 'COMISS√O ESTORNADA', '1')) {
            return false
        }
    }
    document.form.txt_valor_reajustado.disabled = false
    return limpeza_moeda('form', 'txt_valor, txt_valor_desconto, txt_valor_reajustado, ')
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<!--****************************Controle de Tela****************************-->
<input type='hidden' name='hdd_cliente' value='<?=$_GET[id_cliente]?>'>
<!--************************************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Conta ‡ Receber 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <input type='radio' name='opt_credito_debito' id='opt_credito' value='C'>
            <label for='opt_credito'>
                CrÈdito
            </label>
            <font color='yellow'>
                <b>(DUPL. CEDIDA, ANTECIPA«√O)</b>
            </font>
            <br/>
            <input type='radio' name='opt_credito_debito' id='opt_debito' value='D'>
            <label for='opt_debito'>
                DÈbito
            </label>
            <font color='yellow'>
                <b>(CQ DEVOLVIDO DEP. EM C/C PELO CLIENTE) OU 
                (D…BITO FORNECEDOR)
                </b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <b>N.∫ da Conta / Nota: </b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            $sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
        <td>
            <input type='text' name='txt_num_conta' title='Digite o N.∫ da Conta' size='17' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo Recebimento:</b>
        </td>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' class='combo'>
            <?
                $sql = "SELECT id_tipo_recebimento, recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY recebimento ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;<input type='checkbox' name='chkt_previsao' value='1' id='label' class='checkbox' disabled>
            <label for='label'>Previs„o</label>
        </td>
        <td>
            <select name='cmb_tipo_moeda' title='Selecione o Tipo de Moeda' onchange='calcular()' class='combo'>
            <?
                $sql = "SELECT id_tipo_moeda, concat(simbolo,' - ',moeda) AS simbolo_moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, 1);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                Valor DÛlar:
            </font>
            <?='R$ '.number_format($valor_dolar, 4, ',', '.');?>
        </td>
        <td>
            <font color='blue'>
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Nacional / Estrangeiro:</b>
        </td>
        <td>
            Valor Desconto Duplicata:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_valor_desconto' title='Digite o Valor Desconto Duplicata' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Reajustado:
        </td>
        <td>
            <b>Data de Emiss„o:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Digite o Valor Reajustado' size='20' maxlength='15' class='textdisabled' disabled> em Reais
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=date('d/m/Y');?>' size='20' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp; <img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            Comiss„o Estornada:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_vencimento' title='Digite a Data de Vencimento' size='20' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
        <td>
            <input type='text' name='txt_comissao_estornada' title='Digite a Comiss„o Estornada' size='3' maxlength='2' class='caixadetexto' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            ObservaÁ„o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' rows='5' cols='100' maxlength='500' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $dia                = substr($_POST['txt_data_vencimento'], 0, 2);
    $mes                = substr($_POST['txt_data_vencimento'], 3, 2);
    $ano                = substr($_POST['txt_data_vencimento'], 6, 4);
    $semana             = data::numero_semana($dia, $mes, $ano);

    $data_emissao       = data::datatodate($_POST['txt_data_emissao'], '-');
    $data_vencimento    = data::datatodate($_POST['txt_data_vencimento'], '-');
    
    if($_POST['opt_credito_debito'] == 'C') {//Somente a opÁ„o de CrÈdito inverte o Sinal, gerando uma Conta Negativa ...
        $valor  = $_POST['txt_valor'] * (-1);
    }else {
        $valor  = $_POST['txt_valor'];
    }
    if(empty($_POST['chkt_previsao'])) $_POST['chkt_previsao'] = 0;

    $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `valor_desconto`, `comissao_estornada`, `data_sys`, `fase_implant`, `status`, `ativo`) VALUES (NULL, '$id_emp', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$_POST[hdd_cliente]', '$_POST[cmb_tipo_moeda]', '$_POST[txt_num_conta]', '$semana', '$_POST[chkt_previsao]', '$data_emissao', '$data_vencimento', '$data_vencimento', '$data_vencimento', '$valor', '$_POST[txt_valor_desconto]', '$_POST[txt_comissao_estornada]', '".date('Y-m-d H:i:s')."', '3', '0', '1') ";
    bancos::sql($sql);
    $id_conta_receber = bancos::id_registro();
       
//Registrando Follow-UP(s) ...
    if(!empty($_POST['txt_observacao'])) {
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[hdd_cliente]', '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    
    financeiros::atualizar_data_alterada($id_conta_receber, 'R');
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Incluir Conta ‡ Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(opener.parent.itens.document.form) == 'object') opener.parent.itens.recarregar_tela()
    }
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<!--****************************Controle de Tela****************************-->
<input type='hidden' name='nao_atualizar'>
<!--************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) p/ Incluir Conta ‡ Receber - 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Raz„o Social
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Raz„o Social' maxlength='50' size='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite a Nome Fantasia' maxlength='40' size='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value=1' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>