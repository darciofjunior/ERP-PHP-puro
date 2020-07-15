<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/albafer/index.php';
    $endereco_volta = 'albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/tool_master/index.php';
    $endereco_volta = 'tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/grupo/index.php';
    $endereco_volta = 'grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CHEQUE COMPENSADO COM SUCESSO.</font>';

if($passo == 1) {
    //Abaixo independente do Filtro só trago Talões que possuem N.º de Cheques Compensados ...
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(t.id_talao), t.num_inicial, t.num_final, cc.conta_corrente, a.cod_agencia, b.banco 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` AND t.`num_inicial` <= $txt_consultar AND t.`num_final` >= '$txt_consultar' 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY t.num_inicial ";
        break;
        case 2:
            $sql = "SELECT DISTINCT(t.id_talao), t.num_inicial, t.num_final, cc.conta_corrente, a.cod_agencia, b.banco 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` AND a.`cod_agencia` = '$txt_consultar' 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY t.num_inicial ";
        break;
        case 3:
            $sql = "SELECT DISTINCT(t.id_talao), t.num_inicial, t.num_final, cc.conta_corrente, a.cod_agencia, b.banco 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' AND cc.`conta_corrente` = '$txt_consultar' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY t.num_inicial ";
        break;
        default:
            $sql = "SELECT DISTINCT(t.id_talao), t.num_inicial, t.num_final, cc.conta_corrente, a.cod_agencia, b.banco 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY t.num_inicial ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'compensar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cheque(s) p/ Compensar ::.</title>
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
        <td colspan='6'>
            Cheque(s) p/ Compensar
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Inicial / N.º Final
        </td>
        <td>
            Conta Corrente
        </td>
        <td>
            Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="compensar.php?passo=2&id_talao=<?=$campos[$i]['id_talao'];?>" title='Cheques do tal&atilde;o <?=$campos[$i]['num_inicial'];?>/<?=$campos[$i]['num_final'];?>' class='link'>
                <?=$campos[$i]['num_inicial']."/".$campos[$i]['num_final'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['conta_corrente'];?>
        </td>
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
  </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location='compensar.php'" class='botao'>
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
    //Busca todos os Cheques do Talão passado por parâmetro ...
    $sql = "SELECT * 
            FROM `cheques` 
            WHERE `id_talao` = '$_GET[id_talao]' 
            AND `status` = '2' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Compensar Cheque(s) de Talão ::.</title>
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
    var indice = document.form.indice.value
    document.getElementById('txt_data'+indice).value = valor
}

function validar() {
    var elementos   = document.form.elements, valor = false, caracteres2 = '0123456789/'
    //Verifica o N.º de Linhas ...
    if(typeof(elementos['chkt_cheque[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_cheque[]'].length)
    }
    for (var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_cheque'+i).checked == true) {
            valor = true
            break;//Para sair fora do Loop ...
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_cheque'+i).checked == true) {
                if(document.getElementById('txt_data'+i).value == '') {
                    alert('DIGITE A DATA DE COMPENSAÇÃO !')
                    document.getElementById('txt_data'+i).focus()
                    return false
                }
                for(j = 0; j < document.getElementById('txt_data'+i).value.length; j++) {
                    if(caracteres2.indexOf(document.getElementById('txt_data'+i).value.charAt(j), 0) == -1) {
                        alert('DATA DE COMPENSAÇÃO INVÁLIDA !')
                        document.getElementById('txt_data'+i).focus()
                        document.getElementById('txt_data'+i).select()
                        return false
                    }
                }
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<!--******************Controles de Tela******************-->
<input type='hidden' name='indice'>
<input type='hidden' name='data_compensacao' onclick="transferir(this.value)">
<!--*****************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Compensar Cheque(s) de Talão
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
            N&uacute;mero do Cheque
        </td>
        <td>
            Valor
        </td>
        <td>
            Hist&oacute;rico
        </td>
        <td>
            Data de Compensa&ccedil;&atilde;o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" align='center'>
            <input type='checkbox' name='chkt_cheque[]' id='chkt_cheque<?=$i;?>' value="<?=$campos[$i]['id_cheque'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')">
            <?=$campos[$i]['num_cheque'];?>
        </td>
        <td onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')">
            <input type='text' name='txt_historico[]' id='txt_historico<?=$i;?>' size='20' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data[]' id='txt_data<?=$i;?>' size='12' maxlength='10' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'data', '', '', event)" class='textdisabled' disabled>&nbsp;
            <img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="if(document.getElementById('chkt_cheque<?=$i;?>').checked == true) {document.form.indice.value = '<?=$i;?>';nova_janela('../../../../../../calendario/calendario.php?campo=data_compensacao&tipo_retorno=1&chamar_funcao=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'compensar.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_compensar' value='Compensar' title='Compensar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    foreach($_POST['chkt_cheque'] as $i => $id_cheque) {
        $data_compensacao = data::datatodate($_POST['txt_data'][$i], '-');
        $sql = "UPDATE `cheques` SET `historico` = '".$_POST['txt_historico'][$i]."', `data_compensacao` = '$data_compensacao', `status` = '3', predatado = '0' WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
        bancos::sql($sql);
        //Atualiza todas as contas que foram pagas com esse cheque ...
        $sql = "SELECT id_conta_apagar 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_cheque` = '$id_cheque' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                /*Busca a Conta que foi paga com o Tal Cheque se este estiver Pré-Datado e se a Conta a Pagar estiver 
                Pré-Datada ...*/
                $sql = "SELECT ca.id_conta_apagar 
                        FROM `contas_apagares` ca 
                        INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_conta_apagar` = ca.`id_conta_apagar` 
                        INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.`predatado` = '1' 
                        WHERE ca.`id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' 
                        AND ca.`predatado` = '1' LIMIT 1 ";
                $campos_cheque = bancos::sql($sql);
                if(count($campos_cheque) == 0) {
                    $sql = "UPDATE `contas_apagares` SET `predatado` = '0' WHERE `id_conta_apagar` = '".$campos[$i]['id_conta_apagar']."' AND `predatado` = '1' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'compensar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) p/ Compensar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
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
</script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) p/ Compensar
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
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt1' value='1' title='Consultar Cheque por Tal&atilde;o' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt1'>Tal&atilde;o</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt2' value='2' title='Consultar Cheque por Ag&ecirc;ncia' onclick='document.form.txt_consultar.focus()'>
            <label for='opt2'>Ag&ecirc;ncia</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' id='opt3' value='3' title='Consultar Cheque por Conta Corrente' onclick='document.form.txt_consultar.focus()'>
            <label for='opt3'>Conta Corrente</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todos os Cheques' onclick='limpar()' class='checkbox'>
            <label for='todos'>Todos os registros (Tal&otilde;es)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../<?=$endereco_volta;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>