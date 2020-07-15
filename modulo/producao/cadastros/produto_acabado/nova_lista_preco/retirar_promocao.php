<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/nova_lista_preco/lista_preco.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>PROMOÇÃO RETIRADA COM SUCESSO.</font>";

//Retira a Promoção dos Produtos p/ as Empresas Divisões selecionadas
if(!empty($_POST['chkt_empresa_divisao'])) {
    foreach($_POST['chkt_empresa_divisao'] as $id_empresa_divisao) {
/*Traz todos os grupos_empresas_divisão p/ poder achar os produtos através
da id_empresa_divisao selecionada*/
        $sql = "SELECT id_gpa_vs_emp_div 
                FROM `gpas_vs_emps_divs` 
                WHERE `id_empresa_divisao` = '$id_empresa_divisao' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
//Aqui já retira promoção de todos os P.A. através do id_grupo_empresa_divisao
            $sql = "UPDATE `produtos_acabados` SET `qtde_promocional_simulativa` = '0', `preco_promocional_simulativa` = '0', `qtde_promocional_simulativa_b` = '0', `preco_promocional_simulativa_b` = '0' WHERE `id_gpa_vs_emp_div` = '".$campos[$i]['id_gpa_vs_emp_div']."' ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Retirar Promoção ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var x, mensagem = '', valor = false, elementos = document.form.elements
    for (x = 0; x < elementos.length; x ++)   {
        if (elementos[x].type == 'checkbox')  {
            if (elementos[x].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        resposta = confirm('VOCÊ TEM CERTEZA QUE DESEJA RETIRAR A PROMOÇÃO ?')
        if(resposta == true) {
                return true
        }else {
                return false
        }
    }
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<table border="0" width='70%' align="center" cellspacing ='1' cellpadding='1' onmouseover='total_linhas(this)'>
	<tr align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class='linhacabecalho'>
            <td colspan="2" align='center'>
                Retirar Promoção
            </td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>
			<font color='#FFFFFF' size='-1'>
				Empresa Divisão
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Todos </label><input type="checkbox" name="chkt" onClick="return selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
			</font>
		</td>
	</tr>
<?
//Listagem das Empresas Divisões
	$sql = "SELECT id_empresa_divisao, razaosocial 
                FROM `empresas_divisoes`
                WHERE `ativo` = '1' ORDER BY razaosocial ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
            <td>
                <?=$campos[$i]['razaosocial']?>
            </td>
            <td>
                <input type="checkbox" name="chkt_empresa_divisao[]" value="<?=$campos[$i]['id_empresa_divisao']?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
            </td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
                <input type="reset" name="cmd_redefinir" value="Redefinir" title='Redefinir' style="color:#ff9900;" class="botao">
                <input type="submit" name="cmd_retirar" value="Retirar" title='Retirar' class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>