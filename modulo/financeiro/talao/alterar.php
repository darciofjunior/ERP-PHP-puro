<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TALÃO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT t.`id_talao`, t.`num_inicial`, cc.`conta_corrente`, cc.`id_contacorrente`, a.`id_agencia`, a.`cod_agencia`, b.`banco` 
                    FROM `taloes` t 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE t.`num_inicial` LIKE '$txt_consultar%' 
                    AND t.`ativo` = '1' ORDER BY t.`num_inicial` ";
        break;
        default:
            $sql = "SELECT t.`id_talao`, t.`num_inicial`, cc.`conta_corrente`, cc.`id_contacorrente`, a.`id_agencia`, a.`cod_agencia`, b.`banco` 
                    FROM `taloes` t 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE t.`ativo` = '1' ORDER BY t.`num_inicial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar Talão(ões)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
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
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = 'alterar.php?passo=2&id_talao=<?=$campos[$i]['id_talao'];?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='alterar.php?passo=2&id_talao=<?=$campos[$i]['id_talao'];?>' class='link'>
                <?=$campos[$i]['num_inicial'];?>
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
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
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
    //Busco dados do Talão passado por parâmetro ...
    $sql = "SELECT * 
            FROM `taloes` 
            WHERE `id_talao` = '$_GET[id_talao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Bloqueado
    if(!combo('form', 'cmb_bloqueado', '', 'SELECIONE O TIPO DE BLOQUEADO !')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_bloqueado.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_talao' value='<?=$_GET[id_talao];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Talão(ões)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número Inicial: 
        </td>
        <td>
            <input type='text' name='txt_num_inicial' value='<?=$campos[0]['num_inicial'];?>' maxlength='20' size='21' title='Número Inicial' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número Final:
        </td>
        <td>
            <input type='text' name='txt_num_final' value='<?=$campos[0]['num_final'];?>' maxlength='20' size='21' title='Número Final' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Bloqueado:</b>
        </td>
        <td>
            <select name='cmb_bloqueado' title='Tipo Bloqueado' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['bloqueado'] == 'S') {
                ?>
                <option value='S' selected>Sim</option>
                <option value='N'>Não</option>
                <?
                    }else {
                ?>
                <option value='S'>Sim</option>
                <option value='N' selected>Não</option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if(empty($_GET['pop_up'])) {
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form','REDEFINIR');document.form.cmb_bloqueado.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?
                }
            ?>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $sql = "UPDATE `taloes` SET `bloqueado` = '$_POST[cmb_bloqueado]' WHERE `id_talao` = '$_POST[hdd_talao]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Talão(ões)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt_opcao' value='1' title='Consultar Talão por: Número Inicial' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt1'>Número Inicial</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todos os Talões' onclick='limpar()' class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>