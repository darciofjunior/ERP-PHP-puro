<?
require '../../../../lib/menu/menu.php';

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DADO(S) BANC�RIO(S) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>DADO(S) BANC�RIO(S) J� EXISTENTE.</font>";

if($passo == 1) {
    $condicao_pais = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    $condicao_pais 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
        case 2:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    $condicao_pais 
                    AND `razaosocial` <> '' ORDER BY razaosocial ";
        break;
        case 3:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `produto` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    $condicao_pais 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
        case 4:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `codigo` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    $condicao_pais 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    $condicao_pais 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
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
<title>.:: Incluir Dado(s) Banc�rio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Incluir Dado(s) Banc�rio(s) - Consultar Fornecedor
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            Raz�o Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endere�o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?passo=2&id_fornecedor='.$campos[$i]['id_fornecedor'];
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td onclick="window.location = '<?=$url;?>'"width='10'>
            <a href='<?=$url;?>' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'" align="left">
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo est� preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td align="left">
        <?
            if(!empty($campos[$i]['endereco'])) echo $campos[$i]['endereco'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
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
/*Busca do CNPJ ou CPF no cadastro de Fornecedor atrav�s do $id_fornecedor, 
vou utilizar + abaixo p/ o JavaScript*/
    $sql = "SELECT `razaosocial`, `cnpj_cpf` 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    
    if(!empty($campos[0]['cnpj_cpf'])) {//Campo est� preenchido ...
        if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
        }else {//CNPJ ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
        }
    }
?>
<html>
<head>
<title>.:: Incluir Dado(s) Banc�rio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Banco
    if(!texto('form', 'txt_banco', '2', "qwertyuiop�lkjhgfdsazxcvbnm QWERTYUIOP�LKJHGFDSAZXCVBNM@!#$%�������������������������'���,.1234567890-/", 'BANCO', '2')) {
        return false
    }
//Ag�ncia
    if(document.form.txt_agencia.value != '') {
        if(!texto('form', 'txt_agencia', '1', '-.1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ', 'AG�NCIA', '1')) {
            return false
        }
    }
//N�mero da Conta Corrente
    if(document.form.txt_conta_corrente.value != '') {
        if(!texto('form', 'txt_conta_corrente', '2', '-.1234567890abc�defghijklmnopqrstuvwxyzABC�DEFGHIJKLMNOPQRSTUVWXYZ() ', 'N�MERO DA CONTA CORRENTE', '2')) {
            return false
        }
    }
//Correntista
    if(document.form.txt_correntista.value != '') {
        if(!texto('form', 'txt_correntista', '2', "-.qwertyuiop�lkjhgfdsazxcvbnm QWERTYUIOP�LKJHGFDSAZXCVBNM@!#$%����������������������������',.1234567890", 'CORRENTISTA', '2')) {
            return false
        }
    }
//CNPJ / CPF
    if(!texto('form', 'txt_cnpj_cpf', '2', '0123456789-/.', 'CNPJ / CPF', '2')) {
        return false
    }
    if(document.form.txt_cnpj_cpf.value.length > 11) {//S� verifica CNPJ
        if (!cnpj('form', 'txt_cnpj_cpf')) {
            document.form.txt_cnpj_cpf.focus()
            document.form.txt_cnpj_cpf.select()
            return false
        }
    }else {//S� verifica CPF
        if (!cpf('form', 'txt_cnpj_cpf')) {
            document.form.txt_cnpj_cpf.focus()
            document.form.txt_cnpj_cpf.select()
            return false
        }
    }
}

function herdar_cnpj_cpf() {
    var cnpj_cpf = '<?=$cnpj_cpf;?>'
    if(cnpj_cpf == '') {//N�o existe "CNPJ ou CPF", sendo assim eu s� dou um aviso ao usu�rio p/ alert�-lo ...
        alert('ESTE FORNECEDOR N�O POSSUI CNPJ OU CPF !')
        document.form.chkt_cpnj_cpf.checked = false
        document.form.txt_cnpj_cpf.focus()
    }else {//Se existir CNPJ, ent�o ...
        if(document.form.chkt_cpnj_cpf.checked == true) {//Quando checado atribui o CNPJ do Fornecedor ...
            document.form.txt_cnpj_cpf.value = '<?=$cnpj_cpf;?>'
        }else {//Quando deschecado, ele limpa o CNPJ do Fornecedor ...
            document.form.txt_cnpj_cpf.value = ''
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_banco.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type="hidden" name="id_fornecedor" value="<?=$_GET['id_fornecedor'];?>">
<table border="0" width='60%' align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Dado(s) Banc�rio(s) p/ o Fornecedor => 
            <font color="yellow">
                <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Banco:</b>
        </td>
        <td>
            <input type="text" name="txt_banco" title="Digite o Banco" maxlength="30" size="32" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Ag�ncia:
        </td>
        <td>
            <input type="text" name="txt_agencia" title="Digite a Ag�ncia" maxlength="30" size="32" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            N�mero da Conta Corrente:
        </td>
        <td>
            <input type="text" name="txt_conta_corrente" title="Digite o N�mero da Conta Corrente" maxlength="30" size="32" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Correntista:
        </td>
        <td>
            <input type="text" name="txt_correntista" title="Digite o Correntista" maxlength="40" size="45" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>CNPJ / CPF:</b>
        </td>
        <td>
            <input type="text" name="txt_cnpj_cpf" title="Digite o CNPJ ou CPF" maxlength="20" size="25" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            &nbsp;
        </td>
        <td>
            <input type="checkbox" name="chkt_cpnj_cpf" value="1" title="Assumir CNPJ / CPF do Cadastro" onclick="herdar_cnpj_cpf()" id="assumir_cnpj_cpf" class="checkbox">
            <label for="assumir_cnpj_cpf">
                Assumir CNPJ / CPF do Cadastro
            </label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form','LIMPAR');document.form.txt_banco.focus();" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $sql = "SELECT id_fornecedor_propriedade 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor` = '$_POST[id_fornecedor]' 
            AND `banco` = '$_POST[txt_banco]' 
            AND `agencia` = '$_POST[txt_agencia]' 
            AND `num_cc` = '$_POST[txt_conta_corrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `fornecedores_propriedades` (`id_fornecedor_propriedade`, `id_fornecedor`, `banco`, `agencia`, `num_cc`, `correntista`, `cnpj_cpf`, `ativo`) VALUES (NULL, '$_POST[id_fornecedor]', '$_POST[txt_banco]', '$_POST[txt_agencia]', '$_POST[txt_conta_corrente]', '$_POST[txt_correntista]', '$_POST[txt_cnpj_cpf]', '1') ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Dado(s) Banc�rio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = false
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
            <td colspan='2'>
                <?=$mensagem[$valor];?>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
                Incluir Dado(s) Banc�rio(s) - Consultar Fornecedor
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Fornecedor por: Raz�o Social" onclick="document.form.txt_consultar.focus()" id='label1' checked>
			<label for='label1'>Raz�o Social</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar Fornecedor por: CNPJ ou CPF" onclick="document.form.txt_consultar.focus()" id='label2'>
			<label for='label2'>CNPJ ou CPF</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="3" title="Consultar Fornecedor por: Produto" onclick="document.form.txt_consultar.focus()" id='label3'>
			<label for='label3'>Produto</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="4" title="Consultar Fornecedor por: C�digo" onclick="document.form.txt_consultar.focus()" id='label4'>
			<label for='label4'>C�digo</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type='checkbox' name='opt_internacional' value='1' title="Consultar fornecedores internacionais" id='label5' class="checkbox">
			<label for='label5'>Internacionais</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' value='1' title="Consultar todos os fornecedores" onclick='limpar()' id='label6' class="checkbox">
			<label for='label6'>Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>