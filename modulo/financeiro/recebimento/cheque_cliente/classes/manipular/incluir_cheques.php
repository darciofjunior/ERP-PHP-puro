<?
require('../../../../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/incluir_cheques.php?id_emp2=1';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/incluir_cheques.php?id_emp2=2';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/incluir_cheques.php?id_emp2=4';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = '<font class="confirmacao">CHEQUE INCLUIDO COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">CHEQUE J¡ EXISTENTE.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `clientes` 
                    WHERE `nomefantasia` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `razaosocial` ";
        break;
        case 2:
            $sql = "SELECT * 
                    FROM `clientes` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `razaosocial` ";
        break;
        case 3:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT * 
                    FROM `clientes` 
                    WHERE `cnpj_cpf` = '$txt_consultar' 
                    AND `ativo` = '1' ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `clientes` 
                    WHERE `ativo` = '1' ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir_cheques.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cliente(s) p/ Incluir Cheque(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cliente(s) p/ Incluir Cheque(s) - 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Raz„o Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Cr
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $credito    = financeiros::controle_credito($campos[$i]['id_cliente']);
        $url        = "incluir_cheques.php?passo=2&id_emp2=".$id_emp2."&id_cliente=".$campos[$i]['id_cliente'];
?>
    <tr class='linhanormal'>
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <a href="<?=$url;?>">
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'" width="310">
            <a href="<?=$url;?>">
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_cliente'] == 0) {
                echo 'RA';
            }else if($campos[$i]['tipo_cliente'] == 1) {
                echo 'RI';
            }else if($campos[$i]['tipo_cliente'] == 2) {
                echo 'CO';
            }else if($campos[$i]['tipo_cliente'] == 3) {
                echo 'ID';
            }else if($campos[$i]['tipo_cliente'] == 4) {
                echo 'AT';
            }else if($campos[$i]['tipo_cliente'] == 5) {
                echo 'DT';
            }else if($campos[$i]['tipo_cliente'] == 6) {
                echo 'IT';
            }else if($campos[$i]['tipo_cliente'] == 7) {
                echo 'FN';
            }
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td align='center'>
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
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_cheques.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color="blue"><b>RA</b></font> -> Revenda Ativa
 <font color="blue"><b>RI</b></font> -> Revenda Inativa
 <font color="blue"><b>CO</b></font> -> Cooperado
 <font color="blue"><b>ID</b></font> -> Ind˙stria
 <font color="blue"><b>AT</b></font> -> Atacadista
 <font color="blue"><b>DT</b></font> -> Distribuidor
 <font color="blue"><b>IT</b></font> -> Internacional
 <font color="blue"><b>FN</b></font> -> Fornecedor
</pre>
<?
    }
}else if($passo == 2) {
?>
<html>
<head>
<title>.:: Incluir Cheques ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//N˙mero Cheque
    if(!texto('form', 'txt_num_cheque', '1', '0123456789 abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-', 'N⁄MERO DO CHEQUE', '2')) {
        return false
    }
//Banco
    if(!texto('form', 'txt_banco', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’ ', 'BANCO', '2')) {
        return false
    }
//Correntista
    if(!texto('form', 'txt_correntista', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’ ', 'CORRENTISTA', '2')) {
        return false
    }
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Valor Inv·lido
    if(document.form.txt_valor.value == '0,00') {
        alert('VALOR INV¡LIDO !')
        document.form.txt_valor.focus()
        document.form.txt_valor.select()
        return false
    }
//Data de Vencimento
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
//Tipo de CobranÁa
    if(!combo('form', 'cmb_tipo_cobranca', '', 'SELECIONE O TIPO DE COBRAN«A !')) {
        return false
    }
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload="document.form.txt_num_cheque.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name="id_cliente" value="<?=$id_cliente;?>">
<input type='hidden' name="id_emp2" value="<?=$id_emp2;?>">
<!--Significa que esse arquivo est· sendo puxado da tela de Recebimento de Contas-->
<input type='hidden' name="pop_up" value="<?=$pop_up;?>">
<table width='70%' border="0" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Cheque(s) - 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
        <?
            $sql = "SELECT `razaosocial` 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['razaosocial'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N˙mero do Cheque:</b>
        </td>
        <td>
            <input type="text" name="txt_num_cheque" title="Digite o N˙mero do Cheque" maxlength="20" size="21" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type="text" name="txt_banco" title="Digite o Banco" maxlength="50" size="30" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Correntista:</b>
        </td>
        <td>
            <input type="text" name="txt_correntista" title="Digite o Correntista" maxlength="50" size="30" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type="text" name="txt_valor" title="Digite o Valor" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="16" maxlength="15" class="caixadetexto"> R$
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            <input type="text" name="txt_data_vencimento" value="<?=date('d/m/Y');?>" title="Digite a Data de Vencimento" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de CobranÁa:</b>
        </td>
        <td>
            <select name="cmb_tipo_cobranca" title="Selecione o Tipo de CobranÁa" class="combo">
                <option value="" style="color:red">SELECIONE</option>
                <option value="0" selected>Carteira</option>
                <option value="1">CobranÁa Banc·ria</option>
            </select>
            &nbsp;
            <input type="checkbox" name="chkt_predatado" value="1" id="predatado" class="checkbox" checked>
            <label for="predatado">PrÈ-datado</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            HistÛrico:
        </td>
        <td>
            <textarea name='txt_historico' title='Digite o HistÛrico' maxlength='150' cols='50' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
//Significa que esse arquivo est· sendo puxado da tela de Recebimento de Contas
	if($pop_up == 1) {
?>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
<?
	}else {
?>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir_cheques.php<?=$parametro;?>'" class='botao'>
<?
	}
?>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_num_cheque.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $txt_data_vencimento = data::datatodate($txt_data_vencimento, '-');
        
    $sql = "SELECT `id_cheque_cliente` 
            FROM `cheques_clientes` 
            WHERE `num_cheque` = '$txt_num_cheque' 
            AND `correntista` = '$txt_correntista' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $predatado = ($_POST['chkt_predatado'] == 1) ? 1 : 0;
        
        $sql = "INSERT INTO `cheques_clientes` (`id_cheque_cliente`, `id_cliente`, `id_empresa`, `num_cheque`, `banco`, `correntista`, `historico`, `valor`, `valor_disponivel`, `tipo_cobranca`, `predatado`, `data_vencimento`) VALUES ('', '$id_cliente', '$id_emp2', '$txt_num_cheque', '$txt_banco', '$txt_correntista', '$txt_historico', '$txt_valor', '$txt_valor', '$cmb_tipo_cobranca', '$predatado', '$txt_data_vencimento') ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_cheques.php<?=$parametro;?>&passo=2&id_emp2=<?=$id_emp2;?>&id_cliente=<?=$id_cliente;?>&pop_up=<?=$pop_up;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Incluir Cheque(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.disabled    = true
    }else {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.disabled    = false
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
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) p/ Incluir Cheque(s) - 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar cliente por: Nome Fantasia" onclick='document.form.txt_consultar.focus()' id="opt1">
            <label for="opt1">Nome Fantasia</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar cliente por: Raz„o Social" onclick='document.form.txt_consultar.focus()' id="opt2" checked>
            <label for="opt2">Raz„o Social</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="3" title="Consultar cliente por: CNPJ ou CPF" onclick='document.form.txt_consultar.focus()' id="opt3">
            <label for="opt3">CNPJ / CPF</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os clientes" onclick='limpar()' id="todos" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>