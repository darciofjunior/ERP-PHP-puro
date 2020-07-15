<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/custos.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/migrar_lista/migrar_lista.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>NOVA PROMOÇÃO MIGRADA COM SUCESSO.</font>";

if(!empty($_POST['chkt_gpa_vs_emp_div'])) {
    foreach($_POST['chkt_gpa_vs_emp_div'] as $id_gpa_vs_emp_div) {
        //Antes eu retiro a Promoção de todos os PA(s) "Grupo vs Empresa Divisão" selecionado(s)
        $sql = "UPDATE `produtos_acabados` SET `qtde_promocional` = '0', `preco_promocional` = '0', `qtde_promocional_b` = '0', `preco_promocional_b` = '0' WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' ";
        bancos::sql($sql);
        //Atribui a Promoção p/ todos os Produtos do "Grupo vs Empresa Divisão" selecionada(s) ...
        $sql = "UPDATE `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                SET pa.`qtde_promocional` = pa.`qtde_promocional_simulativa`, 
                pa.`preco_promocional` = pa.`preco_promocional_simulativa`, 
                pa.`qtde_promocional_b` = pa.`qtde_promocional_simulativa_b`, 
                pa.`preco_promocional_b` = pa.`preco_promocional_simulativa_b` 
                WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Seleção de todos os Grupos, só não traz nada referente a Componentes
$sql = "SELECT ged.id_gpa_vs_emp_div, ged.id_func_migrador_lista, ged.data_migrador_lista, CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS rotulo 
        FROM `grupos_pas` gpa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_grupo_pa = gpa.id_grupo_pa 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        WHERE gpa.ativo = '1' 
        AND gpa.id_familia <> '23' ORDER BY gpa.nome, ed.razaosocial ";
$campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Migrar Promoção ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var x, mensagem = '', valor = false, elementos = document.form.elements
    for (x = 0; x < elementos.length; x ++)   {
        if (elementos[x].type == 'checkbox') {
            if (elementos[x].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        resposta = confirm('VOCÊ TEM CERTEZA QUE DESEJA MIGRAR A PROMOÇÃO ?')
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
<form name="form" method="post" action='' onsubmit="return validar()">
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr align='center'>
		<td colspan='3'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Migrar Promoção
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Grupo P.A. (Empresa Divisão)
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Migrador * Data - Hora
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Todos </label>
                                <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
			</font>
		</td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td align="left">
			<?=$campos[$i]['rotulo'];?>
		</td>
		<td align="left">
			??? - Precisa criar um campo p/ esse caso ...
		</td>
		<td>
			<input type="checkbox" name="chkt_gpa_vs_emp_div[]" value="<?=$campos[$i]['id_gpa_vs_emp_div']?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align='center'>
            <td colspan="3">
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'migrar_lista.php'" class='botao'>
                <input type="reset" name="cmd_redefinir" value="Redefinir" title='Redefinir' style="color:#ff9900;" class="botao">
                <input type="submit" name="cmd_migrar" value="Migrar" title='Migrar' class="botao">
            </td>
	</tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>