<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CFOP EXCLUÍDA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `cfops` 
                    WHERE CONCAT(cfop, '.', num_cfop) LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY num_cfop, cfop ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `cfops` 
                    WHERE `ativo` = '1' ORDER BY num_cfop, cfop ";
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
<title>.:: Excluir CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Excluir CFOP
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            CFOP
        </td>
        <td>
            ICMS
        </td>
        <td>
            IPI
        </td>
        <td>
            NF de <br>Vendas
        </td>
        <td>
            Natureza Op. Resumida
        </td>
        <td>
            Texto da Nota
        </td>
        <td>
            Natureza de Operação
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
            if(!empty($campos[$i]['cfop'])) {
                echo $campos[$i]['cfop'].'.'.$campos[$i]['num_cfop'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if (empty($campos[$i]['icms'])) {
                echo '&nbsp;-&nbsp;';
            }else {
                if($campos[$i]['icms']  == 1) {
                    echo 'TRI';
                }elseif($campos[$i]['icms']  == 2) {
                    echo 'ISE';
                }else{
                    echo 'DIG';
                }
            }
        ?>
        </td>
        <td align="center">
        <?
            if (empty($campos[$i]['ipi'])) {
                echo '&nbsp;-&nbsp;';
            }else {
                if($campos[$i]['ipi']  == 1) {
                    echo 'TRI';
                }elseif($campos[$i]['ipi']  == 2){
                    echo 'ISE';
                }else{
                    echo 'DIG';
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['cfop_nf_venda'] == 'S') {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>		
        </td>
        <td align='left'>
            <?=$campos[$i]['natureza_operacao_resumida'];?>
        </td>
        </td>
        <td align='left'>
            <?=$campos[$i]['descricao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['natureza_operacao'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_cfop[]" value="<?=$campos[$i]['id_cfop'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
		}
?>
    <tr class='linhacabecalho' align="center">
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<pre>
<font color='red'><b>Legenda dos Tipos ICMS / IPI:</b></font>

<font color='blue'><b>TRI</b></font> -> TRIBUTAÇÃO NORMAL
<font color='blue'><b>ISE</b></font> -> ISENTO
<font color='blue'><b>DIG</b></font> -> DIGITAR NA NF
</pre>
</form>
</body>
</html>
<?
	}
}else if($passo == 2) {
    foreach ($_POST['chkt_cfop'] as $id_cfop) {
        $sql = "UPDATE `cfops` SET `ativo` = '0' WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
        bancos::sql($sql);
    }
 ?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir CFOP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
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
<body onLoad='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir CFOP
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" onclick='document.form.txt_consultar.focus()' id='cfop' title="Consultar cfop por: CFOP" checked>
            <label for='cfop'>CFOP</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' id="todos" value='1' title="Consultar todas as CFOPs" class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>