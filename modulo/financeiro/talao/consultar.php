<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
        window.location = 'consultar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Talão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Talão(ões)
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
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = 'alterar.php?passo=2&id_talao=<?=$campos[$i]['id_talao'];?>&pop_up=1'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href = 'alterar.php?passo=2&id_talao=<?=$campos[$i]['id_talao'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['num_inicial'];?>
            </a>
        </td>
        <td>
            <a href = '../conta_corrente/alterar.php?passo=2&id_conta_corrente=<?=$campos[$i]['id_contacorrente'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['conta_corrente'];?>
            </a>
        </td>
        <td>
            <a href = '../agencia/alterar.php?passo=2&id_agencia=<?=$campos[$i]['id_agencia'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['cod_agencia'];?>
            </a>
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
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar Talão(ões) ::.</title>
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
            Consultar Talão(ões)
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