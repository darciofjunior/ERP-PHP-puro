<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
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

$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CHEQUE CANCELADO COM SUCESSO.</font>';

if($passo == 1) {
    switch($optopcao) {
        case 1:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`num_cheque` LIKE '$txtconsultar%' 
                    AND c.`status` = '0' 
                    AND c.`ativo` = '1' ORDER BY c.id_cheque DESC ";
        break;
        default:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE c.`status` = '0' 
                    AND c.`ativo` = '1' ORDER BY c.id_cheque DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'cancelar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cancelar Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2'?>" onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cancelar Cheque(s)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Cheque
        </td>
        <td>
            Número Inicial
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
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['num_cheque'];?>
        </td>
        <td>
            <?=$campos[$i]['num_inicial'];?>
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
        <td>
            <input type='checkbox' name='chkt_cheque[]' value='<?=$campos[$i]['id_cheque'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'cancelar.php'" class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avan&ccedil;ar &gt;&gt;' title="Cancelar" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2){
?>
<html>
<head>
<title>.:: Cancelar Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_justificativa.value == '') {
        alert('DIGITE A JUSTIFICATIVA !')
        document.form.txt_justificativa.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_justificativa.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<!--****************************Controle de Tela****************************-->
<!--Transformo esse vetor em String dentro Hidden p/ não ter problemas posteriores depois que submeter essa Tela ...-->
<input type='hidden' name='id_cheque' value="<?=implode(',', $_POST['chkt_cheque']);?>">
<!--************************************************************************-->
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Justificativa de Cancelamento p/ o(s) Cheque(s) N.º 
            <br/>
            <font color='yellow'>
<?
        //Listo abaixo p/ o usuário os N.ºs de Cheques que foram selecionados na Tela anterior p/ cancelamento ...
        foreach($_POST['chkt_cheque'] as $id_cheque) {
            $sql = "SELECT num_cheque 
                    FROM `cheques` 
                    WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['num_cheque'].'; ';
        }
?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <b>Justificativa:</b>
        </td>
        <td>
            <textarea name='txt_justificativa' cols='50' rows='4' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'cancelar.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_cancelar' value='Cancelar' title='Cancelar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Transforma em Vetor os Cheques que foram passados por parâmetro ...
    $vetor_cheque = explode(',', $_POST['id_cheque']);
    foreach($vetor_cheque as $id_cheque) {
        //Comando que cancela o Cheque ...
        $sql = "UPDATE `cheques` SET `status` = 4, `historico` = '$_POST[txt_justificativa]' WHERE `id_cheque` = '$id_cheque' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location= 'cancelar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) p/ Cancelar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) p/ Cancelar
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
            <input type='radio' name='opt_opcao' id='opt1' value='1' title='Consultar Cheque por N.º do Cheque' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt1'>N.º do Cheque</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar Todos os Cheques' onclick='limpar()' class='checkbox'>
            <label for='todos'>Todos os registros</label>
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