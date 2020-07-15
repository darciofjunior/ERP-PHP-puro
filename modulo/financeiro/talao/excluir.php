<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TALÃO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>HÁ CHEQUES USANDO ESSE TALÃO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT t.id_talao, t.num_inicial, cc.conta_corrente, cc.id_contacorrente, a.id_agencia, a.cod_agencia, b.banco 
                    FROM `taloes` t 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE t.`num_inicial` LIKE '$txt_consultar%' 
                    AND t.`ativo` = '1' ORDER BY t.num_inicial ";
        break;
        default:
            $sql = "SELECT t.id_talao, t.num_inicial, cc.conta_corrente, cc.id_contacorrente, a.id_agencia, a.cod_agencia, b.banco 
                    FROM `taloes` t 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE t.`ativo` = '1' ORDER BY t.num_inicial ";
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
<title>.:: Excluir Talão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form','SELECIONE UMA OPÇÃO !')">
<table width='60%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='5'>
            Excluir Talão(ões)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Número Inicial
        </td>
        <td>
            Conta Corrente
        </td>
        <td>
            Código da Agência
        </td>
        <td>
            Banco
        </td>
        <td>
            <input type="checkbox" name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
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
            <input type="checkbox" name="chkt_talao[]" value="<?=$campos[$i]['id_talao'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location='excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}elseif ($passo == 2) {
    foreach ($_POST['chkt_talao'] as $id_talao) {
        //Aqui eu verifico se tem cheque desse Talão que foi utilizado para saber se posso ou não apagar o talão ...
        $sql = "SELECT id_cheque 
                FROM `cheques` 
                WHERE `id_talao` = '$id_talao' 
                AND `status` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {
            //Apaga os cheques sem uso ...
            $sql = "UPDATE `cheques` SET `ativo` = '0' WHERE id_talao = '$id_talao' ";
            bancos::sql($sql);
            //Apaga o talão ...
            $sql = "UPDATE `taloes` SET `ativo` = '0' WHERE `id_talao` = '$id_talao' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {
            $valor = 3;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location= 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language="JavaScript">
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
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho'>
            <td colspan="2" align='center'>
                Excluir Talão(ões)
            </td>
    </tr>
    <tr class='linhanormal' align='center'>
            <td colspan='2'>
                Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input id="opt1" type="radio" name="opt_opcao" value="1" title="Consultar talão por: Número Inicial" onclick="document.form.txt_consultar.focus()" checked>
            <label for="opt1">Número Inicial</label>
        </td>
        <td width="20%">
            <input id="todos" type='checkbox' name='opcao' value='1' title="Consultar todos os talões" onclick='limpar()' class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>