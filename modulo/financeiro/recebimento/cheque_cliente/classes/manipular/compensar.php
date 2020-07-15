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

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CHEQUE COMPENSADO COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(cc.id_cheque_cliente), cc.* 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
                    INNER JOIN `contas_receberes` cr ON cr.id_conta_receber = crq.id_conta_receber AND cr.`id_empresa` = '$id_emp2' 
                    WHERE cc.num_cheque LIKE '%$txt_consultar%' 
                    AND cc.`status` = '1' 
                    AND cc.`ativo` = '1' 
                    AND cc.`valor_disponivel` = '0' ORDER BY cc.data_vencimento ";
        break;
        default:
            $sql = "SELECT DISTINCT(cc.id_cheque_cliente), cc.* 
                    FROM `cheques_clientes` cc 
                    INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_cheque_cliente` = cc.`id_cheque_cliente` 
                    INNER JOIN `contas_receberes` cr ON cr.id_conta_receber = crq.id_conta_receber AND cr.`id_empresa` = '$id_emp2' 
                    WHERE cc.`status` = '1' 
                    AND cc.`ativo` = '1' 
                    AND cc.`valor_disponivel` = '0' ORDER BY cc.data_vencimento ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'compensar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Compensar Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'cheque.js'></Script>
<Script Language = 'JavaScript'>
function transferir(valor) {
    linha_elemento = eval(document.form.linha.value)
    if(linha_elemento == 0) {
        var valor_obj = 3
    }else if(linha_elemento == 1) {
        var valor_obj = 6
    }else if(linha_elemento > 1) {
        var valor_obj = (eval(linha_elemento) * 3) + 3
    }
    document.form.elements[valor_obj].value = valor.value
}

function check(indice, tipo_objeto, check_posicao) {
    linha_elemento = indice
    if(check_posicao >= 0) {
        if(document.form.elements[check_posicao].checked == false) checkbox_habilita('form', 'chkt_tudo', linha_elemento, '#E8E8E8')
        document.form.retorno.value = 1
    }else {
        if(document.form.retorno.value != 1) {
            checkbox_habilita('form', 'chkt_tudo', linha_elemento, '#E8E8E8')
        }else {
            document.form.retorno.value = 0
        }
    }
}

function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
    return true
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Compensar Cheque(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            N.º do Cheque
        </td>
        <td>
            Valor
        </td>
        <td>
            Data de Venc.
        </td>
        <td>
            Hist&oacute;rico
        </td>
        <td>
            Data de Compensação
        </td>
    </tr>
<?
        $contador_objetos = 1;
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="check('<?=$i;?>', 'linha')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' onclick="check('<?=$i;?>','checkbox')" name='chkt_cheque_cliente[]' value="<?=$campos[$i]['id_cheque_cliente'];?>" class='checkbox'>
            <?$contador_objetos++;?>
        </td>
        <td>
            <a href="javascript:nova_janela('detalhes.php?id_cheque_cliente=<?=$campos[$i]['id_cheque_cliente'];?>', 'DETALHES_CHEQUES', '', '', '', '', 500, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta à Receber' class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
            <input type="text" name="txt_historico[]" size="20" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
            <?$contador_objetos++;?>
        </td>
        <td>
            <input type="text" name="txt_data[]" size="12" maxlength="10" onclick="checkbox_habilita('form', 'chkt_tudo','<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'data', '', '', event)" class='textdisabled' disabled>&nbsp;
            <img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:document.form.linha.value='<?=$i?>';nova_janela('../../../../../../calendario/calendario.php?campo=data_compensacao&tipo_retorno=1&chamar_funcao=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c');check('<?=$i;?>', 'calendario', '<?=$contador_objetos - 2;?>')">
            <?$contador_objetos++;?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'compensar.php'" class='botao'>
            <input type='submit' name='cmd_compensar' value='Compensar' title='Compensar' style="color:green" class='botao'>
            <input type='button' name='cmd_relatorio' value='Relatório' title='Relatório' onclick="nova_janela('relatorio/cheques_compensar.php?id_emp2=<?=$id_emp2;?>&txt_consultar=<?=$txt_consultar;?>', 'CONSULTAR', 'F')" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='data_compensacao' onclick='transferir(this)'>
<input type='hidden' name='linha'>
<input type='hidden' name='retorno'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    foreach($_POST['chkt_cheque_cliente'] as $i => $id_cheque_cliente) {
/*Aqui tem esse controle porque pode entrar outra pessoa pode acessar pela tela de pagamento com cheques de Clientes 
e esse já ter sido utilizado*/
        $sql = "SELECT status 
                FROM `cheques_clientes` 
                WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['status'] == 1) {//Significa que a conta ainda está para Compensar
            $data_vencimento    = data::datatodate($_POST['txt_data'][$i], '-');
            $sql = "UPDATE `cheques_clientes` SET `historico` = '".$_POST['txt_historico'][$i]."', `data_vencimento` = '$data_vencimento', `status` = '2', `predatado` = '0' WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'compensar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) de Cliente para Compensar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
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
            Consultar Cheque(s) de Cliente para Compensar
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
            <input type='radio' name='opt_opcao' value='1' title='Consultar Cheque por Número do Cheque' onclick='document.form.txt_consultar.focus()' id='opt1' checked>
            <label for='opt1'>Número do Cheque</label>
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