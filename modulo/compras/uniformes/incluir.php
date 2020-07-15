<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>UNIFORME INCLUIDO PARA O FUNCIONÁRIO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>UNIFORME JÁ EXISTENTE PARA ESTE FUNCIONÁRIO.</font>";

if($passo == 1) {
//Significa que o usuário fez seleção de todos os Funcionários q não possuem Uniforme Cadastrado ...
	if(!empty($chkt_func_sem_uniforme)) {
//Aqui eu trago todos os funcionários que possuem uniforme cadastrado ...
            $sql = "SELECT DISTINCT(id_funcionario) 
                    FROM `uniformes` 
                    WHERE `id_funcionario` <> '0' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $id_func_com_uniforme.= $campos[$i]['id_funcionario'].', ';
            $id_func_com_uniforme = substr($id_func_com_uniforme, 0, strlen($id_func_com_uniforme) - 2);
            $condicao = " AND f.`id_funcionario` NOT IN ($id_func_com_uniforme) ";
	}

	$sql = "SELECT f.id_funcionario, f.codigo_barra, f.nome, f.rg, f.ddd_residencial, f.telefone_residencial, d.departamento, c.cargo, e.nomefantasia 
		FROM `funcionarios` f 
		INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
		INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
		INNER JOIN `empresas` e ON f.id_empresa = e.id_empresa 
		WHERE f.`nome` LIKE '%$txt_funcionario%' 
		AND f.`status` < '3' 
		$condicao ORDER BY f.nome ";
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
<title>.:: Incluir Uniforme(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='7'>
            Incluir Uniforme(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Cód.
        </td>
        <td>
            Nome
        </td>
        <td>
            RG
        </td>
        <td>
            Telefone
        </td>
        <td>
            Cargo
        </td>
        <td>
            Depto.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
                    $url = "javascript:window.location = ('incluir.php?passo=2&id_funcionario_loop=".$campos[$i]['id_funcionario']."')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td onclick="<?=$url;?>" align="left">
            <a href="#" class="link">
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
                <?=$campos[$i]['rg'];?>
        </td>
        <td>
                <?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>
        </td>
        <td>
                <?=$campos[$i]['cargo'];?>
        </td>
        <td>
                <?=$campos[$i]['departamento'];?>
        </td>
        <td>
                <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
		}
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='7'>
            <input type="button" name="cmd_consultar" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
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
    //Aqui eu trago dados do Funcionário passado por parâmEtro ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $nome = $campos[0]['nome'];
?>
<html>
<head>
<title>.:: Incluir Uniforme(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Variáveis
    var calcado = document.form.cmb_calcado.value
    var camisa = document.form.cmb_camisa.value
    var calca = document.form.cmb_calca.value
    var avental = document.form.cmb_avental.value

    if(calcado == '' && camisa == '' && calca == '' && avental == '') {
        alert('SELECIONE PELO MENOS UMA OPÇÃO !')
        document.form.cmb_calcado.focus()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=3';?>" method="post" onsubmit="return validar()">
<input type='hidden' name='id_funcionario_loop' value='<?=$_GET[id_funcionario_loop];?>'>
<table border='0' width='60%' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Incluir Uniforme(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Funcionário:</b>
        </td>
        <td width='80%'>
            <?=$nome;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º Calçado:
        </td>
        <td>
            <select name='cmb_calcado' title='Selecione um Calçado' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'> - </option>
                <?
                    for($i = 35; $i < 46; $i++) {
                ?>
                <option value="<?=$i;?>"><?=$i;?></option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Camisa:
        </td>
        <td>
            <select name='cmb_camisa' title='Selecione uma Camisa' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='P'>P</option>
                <option value='M'>M</option>
                <option value='G'>G</option>
                <option value='GG'>GG</option>
                <option value='XG'>XG</option>
                <option value='EXG'>EXG</option>
                <option value='EXGG'>EXGG</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Calça:
        </td>
        <td>
            <select name="cmb_calca" title="Selecione uma Calça" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='P'>P</option>
                <option value='M'>M</option>
                <option value='G'>G</option>
                <option value='GG'>GG</option>
                <option value='XG'>XG</option>
                <option value='EXG'>EXG</option>
                <option value='EXGG'>EXGG</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Avental:
        </td>
        <td>
            <select name="cmb_avental" title="Selecione um Avental" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='P'>P</option>
                <option value='M'>M</option>
                <option value='G'>G</option>
                <option value='GG'>GG</option>
                <option value='XG'>XG</option>
                <option value='EXG'>EXG</option>
                <option value='EXGG'>EXGG</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a Observação" maxlength='85' cols='85' rows='1' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class="botao">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
	$data_sys = date('Y-m-d H:i:s');
	$sql = "SELECT * 
                FROM `uniformes` 
                WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) == 0) {
            $sql = "INSERT INTO `uniformes` (`id_uniforme`, `id_funcionario`, `calcado`, `camisa`, `calca`, `avental`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_funcionario_loop]', '$_POST[cmb_calcado]', '$_POST[cmb_camisa]', '$_POST[cmb_calca]', '$_POST[cmb_avental]', '$_POST[txt_observacao]', '$data_sys') ";
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
<title>.:: Incluir Uniforme(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function travar() {
    if(document.form.chkt_func_sem_uniforme.checked == true) {
//Trava os objetos ...
        document.form.txt_funcionario.disabled = true
//Trocando a Cor para Desabilitado
        document.form.txt_funcionario.className = 'textdisabled'
    }else {
//Habilita os objetos ...
        document.form.txt_funcionario.disabled = false
//Trocando a Cor para Habilitado
        document.form.txt_funcionario.className = 'caixadetexto'
        document.form.txt_funcionario.focus()
    }
}
</Script>
</head>
<body onLoad="document.form.txt_funcionario.focus()">
<form name="form" method="post" action=''>
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
	<tr align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
                Incluir Uniforme(s)
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Funcionário
            </td>
            <td>
                <input type="text" name="txt_funcionario" title="Digite o Funcionário" size="40" class="caixadetexto">
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                &nbsp;
            </td>
            <td>
                <input type='checkbox' name='chkt_func_sem_uniforme' value='1' title="Funcionários s/ Uniforme Cadastrado" onclick="travar()" id='label1' class="checkbox">
                <label for='label1'>Funcionários s/ Uniforme Cadastrado</label>
            </td>
	</tr>
	<tr class="linhacabecalho" align='center'>
            <td colspan="2">
                <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_funcionario.focus()" style="color:#ff9900" class="botao">
                <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>