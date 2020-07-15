<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');

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
$mensagem[2] = '<font class="confirmacao">CHEQUE ALTERADO COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">CHEQUE J¡ EXISTENTE.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`num_cheque` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
        case 2:
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`correntista` LIKE '%$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
        case 3:
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`valor` LIKE '$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
        case 4:
            $txt_consultar = data::datatodate($txt_consultar, '-');
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`data_vencimento` LIKE '$txt_consultar%' 
                    AND cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
        case 5:
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` AND c.`razaosocial` LIKE '%$txt_consultar%' 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
        default:
            $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                    WHERE cc.`id_empresa` = '$id_emp2' 
                    AND cc.`ativo` = '1' 
                    AND cc.`status_disponivel` = '1' ORDER BY cc.data_vencimento ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'alterar_cheques.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Cheque(s) de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Alterar Cheque(s) de Cliente 
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
            Cliente
        </td>
        <td>
            Correntista
        </td>
        <td>
            Valor
        </td>
        <td>
            Data de Venc.
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "alterar_cheques.php?passo=2&id_emp2=".$id_emp2."&id_cheque_cliente=".$campos[$i]['id_cheque_cliente'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = "../../../../../../imagem/seta_direita.gif" width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td align='right'>
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
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar_cheques.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
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
    //Busca dados do id_cheque_cliente passado por par‚metro ...
    $sql = "SELECT c.razaosocial, cc.* 
            FROM `cheques_clientes` cc 
            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
            WHERE cc.`id_cheque_cliente` = '$_GET[id_cheque_cliente]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $tipo_cobranca      = $campos[0]['tipo_cobranca'];
    $predatado          = $campos[0]['predatado'];
?>
<html>
<head>
<title>.:: Alterar Cheque(s) de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
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
<body onload='document.form.txt_num_cheque.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<input type='hidden' name='id_cheque_cliente' value='<?=$_GET['id_cheque_cliente'];?>'>
<input type='hidden' name='id_emp2' value='<?=$_GET['id_emp2'];?>'>
<!--Significa que esse arquivo est· sendo puxado da tela de Recebimento de Contas-->
<input type='hidden' name='controle_recebimento' value='<?=$_GET['controle_recebimento'];?>'>
<table width='60%' border='0' cellpadding='1' cellspacing ='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cheque(s) de Cliente 
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
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N˙mero do Cheque:</b>
        </td>
        <td>
            <input type='text' name='txt_num_cheque' value='<?=$campos[0]['num_cheque'];?>' title='Digite o N˙mero do Cheque' maxlength='20' size='21' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type='text' name='txt_banco' value="<?=$campos[0]['banco'];?>" title='Digite o Banco' maxlength='50' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Correntista:</b>
        </td>
        <td>
            <input type='text' name='txt_correntista' value='<?=$campos[0]['correntista'];?>' title='Digite o Correntista' maxlength='50' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($campos[0]['valor'], 2, ',', '.');?>' title='Digite o Valor do Cheque' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='16' maxlength='15' class='caixadetexto'> R$
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            <input type='text' name='txt_data_vencimento' value='<?=data::datetodata($campos[0]['data_vencimento'], '/');?>' title='Digite a Data de Vencimento' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de CobranÁa:</b>
        </td>
        <td>
            <select name='cmb_tipo_cobranca' title='Selecione o Tipo de CobranÁa' class='combo'>
                <?
                    if($tipo_cobranca == 0) {
                        $selected0 = 'selected';
                    }else {
                        $selected1 = 'selected';
                    }
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' <?=$selected0;?>>Carteira</option>
                <option value='1' <?=$selected1;?>>CobranÁa Banc·ria</option>
            </select>
            &nbsp;
            <?
                //PrÈ-Datado
                $checked = ($predatado == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_predatado' id='predatado' value='1' <?=$checked;?> class='checkbox'>
            <label for='predatado'>PrÈ-datado</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            HistÛrico:
        </td>
        <td>
            <textarea name='txt_historico' title='Digite o HistÛrico' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['historico'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
//Significa que esse arquivo est· sendo puxado da tela de Recebimento de Contas
	if($_GET['controle_recebimento'] == 1) {
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
<?
	}else {
?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar_cheques.php<?=$parametro;?>'" class='botao'>
<?
	}
?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_num_cheque.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $data_vencimento = data::datatodate($_POST['txt_data_vencimento'], '-');
    //Verifico se existe algum outro cheque com mesmo N.∫ e Correntista diferente do atual que est· sendo alterado ...
    $sql = "SELECT id_cheque_cliente 
            FROM `cheques_clientes` 
            WHERE `num_cheque` = '$_POST[txt_num_cheque]' 
            AND `correntista` = '$_POST[txt_correntista]' 
            AND `id_cheque_cliente` <> '$_POST[id_cheque_cliente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $predatado = ($_POST['chkt_predatado'] == 1) ? 1 : 0;
        $sql = "UPDATE `cheques_clientes` SET `num_cheque` = '$_POST[txt_num_cheque]', `banco` = '$_POST[txt_banco]', `correntista` = '$_POST[txt_correntista]', `historico` = '$_POST[txt_historico]', `valor` = '$_POST[txt_valor]', `valor_disponivel` = '$_POST[txt_valor]', `tipo_cobranca` = '$_POST[cmb_tipo_cobranca]', `predatado` = '$_POST[predatado]', `data_vencimento` = '$data_vencimento' WHERE `id_cheque_cliente` = '$_POST[id_cheque_cliente]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
//Significa que esse arquivo est· sendo puxado da tela de Recebimento de Contas
<?
    if($_POST['controle_recebimento'] == 1) {
?>
        window.location = 'alterar_cheques.php?passo=2&id_emp2=<?=$_POST['id_emp2'];?>&id_cheque_cliente=<?=$_POST['id_cheque_cliente'];?>&controle_recebimento=<?=$_POST['controle_recebimento'];?>&valor=<?=$valor;?>'
<?
    }else {
?>
        window.location = 'alterar_cheques.php?id_emp2=<?=$_POST['id_emp2'];?>&valor=<?=$valor;?>'
<?
    }
?>
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Cheque(s) de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 5; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 5;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cheque(s) de Cliente 
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
            <input type='radio' name='opt_opcao' value='2' title='Consultar Cheque por Correntista' onclick='document.form.txt_consultar.focus()' id='opt2'>
            <label for='opt2'>Correntista</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Cheque por Valor' onclick='document.form.txt_consultar.focus()' id='opt3'>
            <label for='opt3'>Valor</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Consultar Cheque por Data de Vencimento' onclick='document.form.txt_consultar.focus()' id='opt4'>
            <label for='opt4'>Data de Vencimento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value="5" title="Consultar Cheque por Cliente" onclick='document.form.txt_consultar.focus()' id='opt5'>
            <label for='opt5'>Cliente</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Cheques' onclick='limpar()' id='todos' class='checkbox'>
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