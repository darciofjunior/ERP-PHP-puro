<?
require '../../../../lib/menu/menu.php';

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ DADO(S) BANCÁRIO(S).</font>";

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
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Dado(s) Bancário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Consultar Dado(s) Bancário(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endereço
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "consultar.php?passo=2&id_fornecedor=".$campos[$i]['id_fornecedor'];
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
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
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
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
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
    //Busca todos os Dados Bancários "Contas Bancárias" do Fornecedor passador por parâmetro ...
    $sql = "SELECT f.razaosocial, fp.* 
            FROM `fornecedores_propriedades` fp 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = fp.id_fornecedor 
            WHERE fp.`id_fornecedor` = '$_GET[id_fornecedor]' ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar.php<?=$parametro;?>&passo=1&valor=2';
    </Script>
<?
    }else {
?>
<html>
<title>.:: Consultar Dado(s) Bancário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table border="0" width='70%' cellspacing ='1' cellpadding='1' onmouseover='total_linhas(this)' align="center">
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            Consultar Dados Bancário(s) do Fornecedor: 
            <font color='yellow'>
                    <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Banco
        </td>
        <td>
            Agência
        </td>
        <td>
            N.º Conta Corrente
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Correntista
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <?=$campos[$i]['agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['num_cc'];?>
        </td>
        <td>
            <?=$campos[$i]['cnpj_cpf'];?>
        </td>
        <td>
            <?=$campos[$i]['correntista'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php<?=$parametro;?>&passo=1'" class='botao'>
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
<title>.:: Consultar Dado(s) Bancário(s) ::.</title>
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
                Consultar Dado(s) Bancário(s)
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Fornecedor por: Razão Social" onclick="document.form.txt_consultar.focus()" id='label1' checked>
			<label for='label1'>Razão Social</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar Fornecedor por: CNPJ ou CPF" onclick="document.form.txt_consultar.focus()" id='label2'>
			<label for='label2'>CNPJ / CPF</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="3" title="Consultar Fornecedor por: Produto" onclick="document.form.txt_consultar.focus()" id='label3'>
			<label for='label3'>Produto</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="4" title="Consultar Fornecedor por: Código" onclick="document.form.txt_consultar.focus()" id='label4'>
			<label for='label4'>Código</label>
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