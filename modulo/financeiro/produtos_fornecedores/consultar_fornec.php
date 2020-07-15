<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/financeiro/produtos_fornecedores/index.php', '../../../');
$mensagem[1] = "<font class='erro'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 2:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1'                  
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_fornec.php?valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Fornecedor ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language='JavaScript'>
function enviar() {
	var elementos = document.form.elements
	var id_fornecedor = ''
	for (i = 0; i < elementos.length; i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j++) {
				if(document.form.elements[i][j].selected == true) {
					id_fornecedor = id_fornecedor + document.form.elements[i][j].value+','
				}
			}
		}
	}
	parent.juncao.document.form.id_fornecedor2.value = id_fornecedor.substr(0, id_fornecedor.length - 1)
	parent.juncao.document.form.submit()
}

function selecionar_todos() {
	var i, elementos = document.form.elements
	var selecionados = ''
	for (i=0;i<elementos.length;i++) {
		if(document.form.elements[i].type == 'select-multiple') {
			for(j = 1; j < document.form.elements[i].length; j++) {
				document.form.elements[i][j].selected = true
			}
		}
	}
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border='0' width='70%' align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
                    Consultar Fornecedor
		</td>
	</tr>
	<tr class='linhanormal' align="center">
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size=45 maxlength=45 class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Razão Social" id='label' checked>
			<label for='label'>Razão Social</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: CNPJ ou CPF" id='label2'>
			<label for='label2'>CNPJ / CPF</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type='checkbox' name='opt_internacional' value='1' tabindex='3' title="Consultar fornecedores internacionais" class="checkbox" id='label3'>
			<label for='label3'>Internacionais</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' onclick='limpar()'  value='3' tabindex='4' title="Consultar todos os fornecedores" class="checkbox" id='label4'>
			<label for='label4'>Todos os registros</label>
		</td>
	</tr>
<?
	if($passo == 1) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2">
			<select name="cmb_fornecedor[]" class="combo" size="5" multiple>
				<option value='' style='color:red'>
				SELECIONE
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</option>
<?
			for ($i = 0; $i < $linhas; $i++) {
?>
				<option value="<?=$campos[$i]['id_fornecedor'];?>"><?=$campos[$i]['razaosocial']?></option>
<?
			}
?>
			</select>
		</td>
	</tr>
<?
	}
?>
	<tr>
		<td colspan="2" class="linhacabecalho" align="center">
<?
	if($passo == 1) {
?>
			<input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos()" class="botao">
			<input type="button" name="cmd_adicionar" value="Adicionar" title="Adicionar" class="botao" onclick="enviar()">
<?
	}
?>
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
<?
	if(!empty($valor)) {
?>
		<Script Language = 'JavaScript'>
			alert('<?=$mensagem[$valor];?>')
		</Script>
<?
	}
?>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
            for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
            document.form.txt_consultar.disabled    = true
            document.form.txt_consultar.value       = ''
	}else {
            for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
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
</html>