<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM  RESULTADO.</FONT>";
$mensagem[2] = "<font class='confirmacao'>AGÊNCIA EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>EXISTE(M) CONTA(S) CORRENTE(S) UTILIZANDO ESSA AGÊNCIA.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco AND b.`banco` LIKE '$txt_consultar%' 
                    WHERE a.`ativo` = '1' ORDER BY b.banco ";
        break;
        case 2:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE a.`cod_agencia` LIKE '$txt_consultar%' 
                    AND a.`ativo` = '1' ORDER BY b.banco ";
        break;
        default:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE a.`ativo` = '1' ORDER BY b.banco ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script language = 'JavaScript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Agência ::.</title>
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
<table width='60%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Excluir Agência(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Código da Agência
        </td>
        <td>
            Banco
        </td>
        <td>
            <input type="checkbox" name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_agencia[]" value="<?=$campos[$i]['id_agencia'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
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
    foreach($_POST['chkt_agencia'] as $id_agencia) {
        //Verifico se essa Agência está sendo utilizada por alguma Conta Corrente ...
        $sql = "SELECT id_contacorrente  
                FROM contas_correntes 
                WHERE `id_agencia` = '$id_agencia' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Não sendo utilizada por nenhuma Conta Corrente, então posso excluir ...
            $sql = "UPDATE `agencias` SET `ativo` = '0' WHERE id_agencia = '$id_agencia' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {//Está sendo utilizada ...
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
<title>.:: Excluir Agência(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
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
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Agência(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input id="opt1" type="radio" name="opt_opcao" value="1" checked onclick="document.form.txt_consultar.focus()" title="Consultar Banco por Nome do Banco">
            <label for="opt1">Nome do Banco</label>
        </td>
        <td width="20%">
            <input id="opt2" type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar Agências por Código da Agencia">
            <label for="opt2">Código da Agência</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input id="todos" type='checkbox' name='opcao' onclick='limpar()'  value='3' title="Consultar todos as agências" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>