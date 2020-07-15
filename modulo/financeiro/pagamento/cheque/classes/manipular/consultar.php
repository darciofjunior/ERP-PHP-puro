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

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`num_cheque` LIKE '$txt_consultar%' 
                    AND c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
        default:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Cheque ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Cheque(s)
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
            Cód. Agência
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
            <a href='detalhes.php?id_cheque=<?=$campos[$i]['id_cheque'];?>' class='html5lightbox'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <a href='../../../../talao/alterar.php?id_talao=<?=$campos[$i]['id_talao'];?>&passo=2&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['num_inicial'];?>
            </a>
        </td>
        <td>
            <a href='../../../../conta_corrente/alterar.php?id_conta_corrente=<?=$campos[$i]['id_contacorrente'];?>&passo=2&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['conta_corrente'];?>
            </a>
        </td>
        <td>
            <a href='../../../../agencia/alterar.php?id_agencia=<?=$campos[$i]['id_agencia'];?>&passo=2&pop_up=1' class='html5lightbox'>
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
<title>.:: Consultar Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type='text/css' rel='stylesheet'>
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
</script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque 
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
            <input type='radio' name="opt_opcao" id='opt1' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Cheque por Número do Cheque' checked>
            <label for='opt1'>Número do Cheque</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='opcao' value='1' title="Consultar todos os Cheques" onclick='limpar()' class='checkbox'>
            <label for='opcao'>Todos os registros</label>
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