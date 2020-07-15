<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up']) && empty($_GET['nao_exibir_menu']))   require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../lib/data.php');
require('../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

if($passo == 1) {
?>
<html>
<head>
<title>.:: Alterar Funcionários ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function gerenciar_telas(tela) {
    if(tela == 1) {//Dados Pessoais ...
        parent.corpo.tela.location = 'alterar_dados_pessoais.php?id_funcionario_loop='+document.form.id_funcionario_loop.value+'&pop_up=<?=$_GET['pop_up'];?>'
    }else if(tela == 2) {//Dados Profissionais ...
        parent.corpo.tela.location = 'alterar_dados_profissionais.php?id_funcionario_loop='+document.form.id_funcionario_loop.value+'&pop_up=<?=$_GET['pop_up'];?>'
    }else if(tela == 3) {//Acompanhamento ...
        parent.corpo.tela.location = 'acompanhamento.php?id_funcionario_loop='+document.form.id_funcionario_loop.value+'&pop_up=<?=$_GET['pop_up'];?>'
    }
}
</Script>
</head>
<?
//Aqui significa que esta tela está sendo acessada do próprio alterar mesmo
if(empty($tela)) $tela = 1;
//Caso essa variável venha com o valor 2 diretamente, então significa que está foi acessada de lá da Tela de incluir Funcionário ...
?>
<body onload="gerenciar_telas('<?=$tela;?>')">
<form name='form' method='post'>
<!--Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão-->
<input type="hidden" name="id_funcionario_loop" value="<?=$_GET['id_funcionario_loop'];?>">
<table width="55%" border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align="center">
		<td id="aba0" onclick="gerenciar_telas(1);aba(this, 3, 650)" width="33%" class="aba_ativa">
			Dados Pessoais
		</td>
		<td id="aba1" onclick="gerenciar_telas(2);aba(this, 3, 650)" width="33%" class="aba_inativa">
			Dados Profissionais
		</td>
		<td id="aba2" onclick="gerenciar_telas(3);aba(this, 3, 650)" width="33%" class="aba_inativa">
			Acompanhamento
		</td>
	</tr>
</table>
<table border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align="center">
		<td colspan="2">
			<iframe name="tela" id="iframe_tela" marginwidth="0" marginheight="0" frameborder="0" height="1050" width="1000"></iframe>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Funcionários ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='8'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='8'>
            Alterar Funcionário(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            CPF
        </td>
        <td>
            Telefone
        </td>
        <td>
            Depto.
        </td>
        <td>
            Cargo
        </td>
        <td>
            Chefe
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar2.php?passo=1&id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>'" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td align="center">
			<?=$campos[$i]['codigo_barra'];?>
		</td>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td align="center">
			<?=substr($campos[$i]['cpf'], 0, 3).'.'.substr($campos[$i]['cpf'], 3, 3).'.'.substr($campos[$i]['cpf'], 6, 3).'-'.substr($campos[$i]['cpf'], 9, 2);?>
		</td>
		<td align="center">
			<?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>
		</td>
		<td>
			<?=$campos[$i]['departamento'];?>
		</td>
		<td>
			<?=$campos[$i]['cargo'];?>
		</td>
		<td>
		<?
                    //Só o busco o nome de Chefe se o "Funcionário do Loop" possuir ...
                    if(!empty($campos[$i]['id_funcionario_superior'])) {
			$sql = "SELECT nome 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_superior']."' LIMIT 1 ";
			$campos_funcionario = bancos::sql($sql);
			echo $campos_funcionario[0]['nome'];
                    }
		?>
		</td>
		<td>
                    <?=$campos[$i]['nomefantasia'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='8'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'alterar2.php'" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>