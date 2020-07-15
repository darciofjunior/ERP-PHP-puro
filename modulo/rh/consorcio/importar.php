<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales/itens/consultar.php', '../../../../');
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>CONS�RCIO IMPORTADO COM SUCESSO.</font>";

//Fun��o que se encarrega de Importar toda a parte de Cons�rcio do(s) Func(s) p/ Vale(s) ...
function gerar_vales($id_consorcio) {
/*******************************************************************************************/
//Vou utilizar essas vari�veis pra na hora em que eu gravar o Vale ...
	$data_emissao = date('Y-m-d');
	$data_sys = date('Y-m-d H:i:s');
/*******************************************************************************************/
//Busca de Dados do Cons�rcio ...
	$sql = "SELECT valor, juros, data_inicial, meses 
			FROM `consorcios` 
			WHERE `id_consorcio` = '$id_consorcio' LIMIT 1 ";
	$campos = bancos::sql($sql);
	$valor = $campos[0]['valor'];
	$juros = $campos[0]['juros'];
	$data_holerith_inicial = $campos[0]['data_inicial'];
	$meses = $campos[0]['meses'];
//J� travo o cons�rcio p/ que este n�o tenha como ser importado novamente ...
	$sql = "Update `consorcios` set `gerado_vale` = 'S' where `id_consorcio` = '$id_consorcio' limit 1 ";
	bancos::sql($sql);
/*Aqui eu verifico a qtde de Datas de Holerith que existem cadastradas no Sistema a partir da Data de Holerith 
Inicial do Cons�rcio ...*/
	$sql = "SELECT data 
			FROM `vales_datas` 
			WHERE `data` >= '$data_holerith_inicial' LIMIT $meses ";
	$campos_data_holerith = bancos::sql($sql);
//Aqui eu trago os Funcion�rios que est�o participando do Cons�rcio ...
	$sql = "SELECT cf.*, c.nome_grupo, valor  
			FROM `consorcios_vs_funcionarios` cf 
			INNER JOIN `consorcios` c ON c.id_consorcio = cf.id_consorcio 
			WHERE c.`id_consorcio` = '$id_consorcio' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
//Disparo do loop de Meses ...
	for($j = 0; $j < $meses; $j++) {
/****************************Preparando as vari�veis p/ gravar no Banco****************************/
		$parcelamento = ($j + 1).'/'.$meses;
		$data_holerith = $campos_data_holerith[$j]['data'];
/**************************************************************************************************/
//Disparo do loop do funcion�rio em rela��o ao m�s corrente ...
		for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
			$id_funcionario_loop = $campos[$i]['id_funcionario'];
//Pra cada m�s de cons�rcio do Funcion�rio, eu gero um Vale ...
			$sql = "INSERT INTO `vales` (`id_vale`, `id_funcionario`, `tipo_vale`, `parcelamento`, `valor_fatura`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `observacao`, `data_sys`) VALUES ('', '$id_funcionario_loop', '4', '$parcelamento', '', '$valor', '$data_holerith', '$data_emissao', 'PF', 'GRUPO = ".$campos[$i]['nome_grupo']." - R$ ".number_format($campos[$i]['valor'], 2, ',', '.')."', '$data_sys') ";
			bancos::sql($sql);
		}
		$valor*= (1 + $juros / 100);//Aqui eu j� c�lculo o juros em cima do valor p/ o pr�x. m�s ...
	}
}

if($passo == 1) {
//Fun��o que se encarrega de controlar toda a parte de Vale ...
	gerar_vales($id_consorcio);
?>
	<Script Language = 'JavaScript'>
		window.location = 'importar.php?valor=1'
	</Script>
<?
}else {
//Aqui eu s� listo os cons�rcios em que ainda n�o foi gerado nenhum Vale ...
	$sql = "SELECT * 
			FROM `consorcios` 
			WHERE `gerado_vale` = 'N' ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {//N�o encontrou nenhum cons�rcio p/ ser importado, ent�o ...
?>
		<Script Language = 'Javascript'>
			window.location = '../itens/incluir.php?valor=1'
		</Script>
<?
		exit;
	}
?>
<html>
<head>
<title>.:: Cons�rcio - Importar Vale(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_consorcio) {
	var resposta = confirm('TEM CERTEZA DE QUE DESEJA GERAR VALE PARA ESTE CONS�RCIO ?')
	if(resposta == false) {
		return false
	}else {
//Aqui � para n�o atualizar os frames abaixo desse Pop-UP
		document.form.nao_atualizar.value = 1
//Redirecionamento normal da Tela ...
		window.location = 'importar.php?passo=1&id_consorcio='+id_consorcio
	}
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.recarregar_tela()
	}
}
</Script>
</head>
<body onunload="atualizar_abaixo()">
<form name='form'>
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='780' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='7'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='7'>
			<font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='-1'>
				Cons�rcio - Importar Vale(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				N.&ordm; Cons�rcio
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Nome do Grupo
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Juros
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data de Holerith Inicial
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Meses
			</font>
		</td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
/*****************************Controle com a Parte de Datas do Cons�rcio*****************************/
		$data_holerith_inicial = $campos[$i]['data_inicial'];
//Aqui eu verifico a qtde de Datas de Holerith que existem cadastradas no Sistema a partir da Data Atual ...
		$sql = "SELECT COUNT(id_vale_data) AS total_data_holerith_cadast 
				FROM `vales_datas` 
				WHERE `data` >= '$data_holerith_inicial' ";
		$campos2 = bancos::sql($sql);
		$total_data_holerith_cadast = $campos2[0]['total_data_holerith_cadast'];
/****************************************************************************************************/
/*Se a Qtde de Meses do Cons�rcio for > que a Qtde de Cadastro de Datas de Holerith, ent�o o link
aparece em vermelho p/ dizer q esse Cons�rcio est� em inadipl�ncia ...*/
		if($campos[$i]['meses'] > $total_data_holerith_cadast) {
			$color = 'red';
			$url = "javascript:alert('A QTDE DE MESES DO CONS�RCIO � SUPERIOR A QTDE DE DATA(S) DE HOLERITH(S) CADASTRADA(S) NO SISTEMA !!!') ";
		}else {
			$url = "javascript:avancar('".$campos[$i]['id_consorcio']."') ";
			$color = '';
		}
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
		<td width='10' onclick="<?=$url;?>">
                    <a href="#" class="link">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>" align="center">
			<a href="#" class="link">
				<font color="<?=$color;?>">
					<?=$campos[$i]['id_consorcio'];?>
				</font>
			</a>
		</td>
		<td>
			<?=$campos[$i]['nome_grupo'];?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['valor'], 2, ',', '.');?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['juros'], 2, ',', '.').' %';?>
		</td>
		<td align="center">
			<?=data::datetodata($campos[$i]['data_inicial'], '/');?>
		</td>
		<td align="center">
			<?=$campos[$i]['meses'];?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='7'>
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>