<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>CONSÓRCIO BLOQUEADO !!! JÁ FOI GERADO VALE PARA ESTE CONSÓRCIO.</font>";
$mensagem[3] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>ITEM(NS) JÁ EXISTENTE.</font>";

if($passo == 1) {
/*Só não exibo os funcionários cargo AUTONÔMO, Default (1,2) e o DIRETO BR 114 porque estes não são 
funcionários, simplesmente só possuem cadastrado no Sistema p/ poder acessar algumas telas ...
Observação: Essa é a única tela do Sistema em que eu mostro os funcionários demitidos, pois existem
pessoas que não são da Empresa mas que tiveram que ser cadastradas no Sistema como funcionários, 
p/ poder participar do consórcio, e sendo assim o Status delas = 'demitido' p/ que não apareçam em outros 
lugares do Sistema, exemplo: Pai do Roberto*/
    switch($opt_opcao) {
        case 1:	
            $sql = "SELECT c.`cargo`, d.`departamento`, e.`nomefantasia`, f.`id_funcionario`, f.`id_funcionario_superior`, f.`nome`, f.`codigo_barra` 
                    FROM `funcionarios` f 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' 
                    AND f.`id_funcionario` NOT IN (1, 2, 114) ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT c.`cargo`, d.`departamento`, e.`nomefantasia`, f.`id_funcionario`, f.`id_funcionario_superior`, f.`nome`, f.`codigo_barra` 
                    FROM `funcionarios` f 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    WHERE f.`status` < '3' 
                    AND f.`id_funcionario` NOT IN (1, 2, 114) ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?id_consorcio=<?=$id_consorcio;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)";>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            Depto.
        </td>
        <td>
            Cargo
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_funcionario[]' value="<?=$campos[$i]['id_funcionario'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='center'>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php?id_consorcio=<?=$id_consorcio;?>'" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_consorcio' value='<?=$id_consorcio;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
/*Aqui eu verifico se já foi gerado Vale p/ este Consórcio, caso foi gerado, então eu não posso incluir
mais nenhum funcionário no Consórcio ...*/
	$sql = "Select gerado_vale 
			from consorcios 
			where `id_consorcio` = '$id_consorcio' limit 1 ";
	$campos = bancos::sql($sql);
	$gerado_vale = $campos[0]['gerado_vale'];

	if(strtoupper($gerado_vale) == 'S') {//Não posso estar + incluir funcs pq já foi gerado vale ...
		$valor = 2;
	}else {//Ainda não foi gerado vale, sendo assim posso alterar os dados normalmente ...
/*Disparo de Loop dos Funcionário(s) Selecionado(s) ...
Aqui eu renomeio essa variável $chkt_funcionario para $id_funcionario_loop para não dar conflito com 
a variável da Sessão "$id_funcionario"*/
		foreach($chkt_funcionario as $id_funcionario_loop) {
//Verifica se já foi incluido aquele item no consórcio
			$sql = "Select id_consorcio_vs_funcionario from `consorcios_vs_funcionarios` where `id_consorcio` = '$id_consorcio' and `id_funcionario` = '$id_funcionario_loop' limit 1";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {
				$sql = "Insert into `consorcios_vs_funcionarios` (`id_consorcio_vs_funcionario`, `id_consorcio`, `id_funcionario`) values ('', '$id_consorcio', '$id_funcionario_loop') ";
				bancos::sql($sql);
				$valor = 3;
			}else {
				$valor = 4;
			}
		}
	}
?>
	<Script Language = 'JavaScript'>
		window.location = 'incluir.php?id_consorcio=<?=$id_consorcio;?>&valor=<?=$valor;?>'
		//window.opener.parent.itens.document.form.valor.value = 1
		window.opener.parent.itens.document.form.submit()
		window.opener.parent.rodape.document.form.submit()
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
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
<input type='hidden' name='id_consorcio' value='<?=$id_consorcio;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Funcionário' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Funcionário por: Nome" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='2' title="Consultar todos os funcionários" onclick='limpar()' id='label2' class='checkbox'>
            <label for="label2">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>