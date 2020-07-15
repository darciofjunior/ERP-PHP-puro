<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TALÃO INCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>TALÃO JÁ EXISTENTE COM ESSE INTERVALO DE CHEQUES.</font>";

if($passo == 1) {
    switch($opt_opcao) {
            case 1:
                $sql = "SELECT cc.id_contacorrente, cc.conta_corrente, a.cod_agencia, b.banco 
                        FROM `contas_correntes` cc 
                        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                        INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                        WHERE cc.`conta_corrente` LIKE '$txt_consultar%' 
                        AND cc.`ativo` = '1' ORDER BY cc.conta_corrente ";
            break;
            default:
                $sql = "SELECT cc.id_contacorrente, cc.conta_corrente, a.cod_agencia, b.banco 
                        FROM `contas_correntes` cc 
                        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                        INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                        WHERE cc.`ativo` = '1' ORDER BY cc.conta_corrente ";
            break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
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
<title>.:: Incluir Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border=0 align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align='center'>
        <td colspan='4'>
            Incluir Talão(ões) - Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            N.º da Conta Corrente
        </td>
        <td>
            Código Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?passo=2&id_conta_corrente='.$campos[$i]['id_contacorrente'];
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <a href='<?=$url;?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['conta_corrente'];?>
            </a>
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
    <tr class="linhacabecalho" align='center'>
        <td colspan='4'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location='incluir.php'" class='botao'>
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
    //Busco dados da Conta Corrente ...
    $sql = "SELECT conta_corrente 
            FROM `contas_correntes` 
            WHERE `id_contacorrente` = '$_GET[id_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Incluir Talão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Número Inicial
    if(!texto('form', 'txt_num_inicial', '1', '0123456789', 'NÚMERO INICIAL', '2')) {
        return false
    }
//Número Final
    if(!texto('form', 'txt_num_final', '1', '0123456789', 'NÚMERO FINAL', '2')) {
        return false
    }
//Bloqueado
    if(!combo('form', 'cmb_bloqueado', '', 'SELECIONE O TIPO DE BLOQUEADO !')) {
        return false
    }
    
    if(eval(document.form.txt_num_inicial.value) > eval(document.form.txt_num_final.value)) {
        alert('NÚMERO FINAL MENOR QUE O NÚMERO INICIAL !')
        document.form.txt_num_inicial.focus()
        document.form.txt_num_inicial.select()
        return false
    }
    var diferenca = eval(document.form.txt_num_final.value) - eval(document.form.txt_num_inicial.value)
    if(diferenca > 1000) {
        alert('INTERVALO DE CHEQUES MUITO ELEVADO !')
        document.form.txt_num_final.focus()
        document.form.txt_num_final.select()
        return false
    }
}
</Script>
<body onload="document.form.txt_num_inicial.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name='hdd_conta_corrente' value="<?=$_GET['id_conta_corrente'];?>">
<table width='60%' border="0" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Incluir Talão p/ a Conta Corrente => 
            <font color='yellow'>
                <?=$campos[0]['conta_corrente'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Número Inicial:</b>
        </td>
        <td>
            <input type="text" name="txt_num_inicial" maxlength="20" size="21" title="Digite o Número Inicial" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Número Final:</b>
        </td>
        <td>
            <input type="text" name="txt_num_final" maxlength="20" size="21" title="Digite o Número Final" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Tipo de Bloqueado:</b>
        </td>
        <td>
            <select name="cmb_bloqueado" title='Selecione o Tipo Bloqueado' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S'>Sim</option>
                <option value='N' selected>Não</option>
            </select>
            &nbsp;
            <input type="checkbox" name='chkt_gerar' id="gerar" value="1" class="checkbox" checked>
            <label for="gerar">Gerar N.º de Cheque Automático</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class="botao">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_num_inicial.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Aqui verifico se existe um Talão com essa Numeração dentro dessa Conta Corrente ...
    $sql = "SELECT id_talao 
            FROM `taloes` 
            WHERE (`num_inicial` BETWEEN '$_POST[txt_num_inicial]' AND '$_POST[txt_num_final]') 
            AND `id_contacorrente` = '$_POST[hdd_conta_corrente]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `taloes` (`id_talao`, `id_contacorrente`, `num_inicial`, `num_final`, `bloqueado`, `ativo`) VALUES (NULL, '$_POST[hdd_conta_corrente]', '$_POST[txt_num_inicial]', '$_POST[txt_num_final]', '$_POST[cmb_bloqueado]', '1') ";
        bancos::sql($sql);
        if($_POST['chkt_gerar'] == 1) {//Se esta opção estiver marcada, então insiro o intervalo de Cheques dentro desse Talão ...
            $id_talao = bancos::id_registro();
            for($i = $_POST['txt_num_inicial']; $i <= $_POST['txt_num_final']; $i++) {
                $sql = "INSERT INTO `cheques` (`id_cheque`, `id_talao`, `id_funcionario`, `num_cheque`) VALUES (NULL, '$id_talao', '$_SESSION[id_funcionario]', '$i')";
                bancos::sql($sql);
            }
        }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=2'
    </Script>
<?
    }else {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?passo=2&id_talao=<?=$id_talao;?>&valor=3'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Incluir Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled = false
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
            Incluir Talão(ões) - Consultar Conta(s) Corrente(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input id="opt1" type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar talão por: Conta Corrente" checked>
            <label for="opt1">Conta Corrente</label>
        </td>
        <td width="20%">
            <input id="todos" type='checkbox' name='opcao' onclick='limpar()' value='4' title="Consultar todos os talões" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" style="color:#ff9900;" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>