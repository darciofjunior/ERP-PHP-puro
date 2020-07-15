<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA CORRENTE EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>HÁ TALÕES USANDO ESSA CONTA.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cc.*, a.cod_agencia AS cod_agencia, b.banco AS banco, e.nomefantasia AS nomefantasia 
                    FROM `contas_correntes` cc 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    INNER JOIN `empresas` e ON e.id_empresa = cc.id_empresa 
                    WHERE cc.`conta_corrente` LIKE '$txt_consultar%' 
                    AND cc.`ativo` = '1' ORDER BY cc.conta_corrente ";
        break;
        default:
            $sql = "SELECT cc.*, a.cod_agencia AS cod_agencia, b.banco AS banco, e.nomefantasia AS nomefantasia 
                    FROM `contas_correntes` cc 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    INNER JOIN `empresas` e ON e.id_empresa = cc.id_empresa 
                    WHERE cc.`ativo` = '1' ORDER BY cc.conta_corrente ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Conta(s) Corrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=2'?>" onsubmit="return validar()">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align='center'>
        <td colspan='6'>
            Excluir Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Conta Corrente
        </td>
        <td>
            Cód. Agência
        </td>
        <td>
            Banco
        </td>
        <td>
            Empresa
        </td>
        <td>
            Uso de Fat.
        </td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
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
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
        <?
            if($campos[$i]['status_faturamento_sgd'] == 1) {
                echo '<font color="blue">SIM</font>';
            }else {
                echo '<font color="red">NÃO</font>';
            }
        ?>
        </td>
        <td>
            <input type="checkbox" name="chkt_conta_corrente[]" value="<?=$campos[$i]['id_contacorrente'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location='excluir.php'" class="botao">
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
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
}else if($passo == 2) {
    foreach($_POST['chkt_conta_corrente'] as $id_conta_corrente) {
        $sql = "SELECT id_talao 
                FROM `taloes` 
                WHERE `id_contacorrente` = '$id_conta_corrente' 
                AND `ativo` = '1' LIMIT 1";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            $sql = "UPDATE `contas_correntes` SET `ativo` = '0' WHERE `id_contacorrente` = '$id_conta_corrente' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {
            $valor = 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Conta(s) Corrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1'; ?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="1" title="Consultar Conta Corrente por Conta Corrente" onclick="document.form.txt_consultar.focus()" id="opt1" checked>
            <label for="opt1">Conta Corrente</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos as Contas Correntes" id="todos" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' style="color:#ff9900;" onclick="document.form.opcao.checked = false;limpar()" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>