<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>OPERA��O(�ES) EXCLU�DA(S) COM SUCESSO.</font>";

if($passo == 1) {
	if(!empty($_GET['id_maquina_operacao'])) {//Exclus�o da Opera��o da M�quina ...
		$sql = "DELETE FROM `maquinas_vs_operacoes` WHERE `id_maquina_operacao` = '$_GET[id_maquina_operacao]' LIMIT 1 ";
		bancos::sql($sql);
		$valor = 1;
	}
?>
<html>
<head>
<title>.:: M�quina(s) para Gerenciar Opera��o(�es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_maquina_operacao) {
	var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
	if(mensagem == false) {
		return false
	}else {
		window.location = '<?=$PHP_SELF;?>?passo=1&id_maquina=<?=$_GET['id_maquina'];?>&id_maquina_operacao='+id_maquina_operacao
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<table width='700' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr class="atencao" align='center'>
		<td colspan='3'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<?=$mensagem[$valor];?>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Opera��o(�es) da M�quina: 
				<font color='yellow'>
				<?
					$sql = "SELECT nome 
							FROM `maquinas` 
							WHERE `id_maquina` = '$_GET[id_maquina]' LIMIT 1 ";
					$campos = bancos::sql($sql);
					echo $campos[0]['nome'];
				?>
				</font>
			</font>
		</td>
	</tr>
<?
	//Aqui vasculha todas as Opera��es atrelado para esta m�quina ...
	$sql = "SELECT * 
			FROM `maquinas_vs_operacoes` 
			WHERE `id_maquina` = '$_GET[id_maquina]' ORDER BY operacao ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
?>
	<tr class="atencao" align="center">
		<td colspan='3'>
			<font size='-1'>
				N�O H� OPERA��O(�ES) CADASTRADA(S) PARA ESTA M�QUINA.
			</font>
		</td>
	</tr>
<?
	}else {
?>
	<tr class="linhanormal" align="center">
		<td bgcolor="#CCCCCC">
			<font size='-1'>
				<b>Opera��o</b>
			</font>
		</td>
		<td width="30" bgcolor="#CCCCCC">&nbsp;</td>
		<td width="30" bgcolor="#CCCCCC">&nbsp;</td>
    </tr>
<?
		for($i = 0; $i < $linhas ; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td>
			<?=$campos[$i]['operacao'];?>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar_operacao.php?id_maquina_operacao=<?=$campos[$i]['id_maquina_operacao'];?>'" alt="Alterar Opera��o" title="Alterar Opera��o">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_maquina_operacao'];?>')" alt="Excluir Opera��o" title="Excluir Opera��o">
		</td>
	</tr>
<?
		}
	}
?>
	<tr class="linhadestaque" align="left">
		<td colspan='3'>
			<a href="incluir_operacao.php?id_maquina=<?=$_GET['id_maquina'];?>" title="Incluir Opera��o(�es)">
				<font color="#FFFF00">
					Incluir Opera��o(�es)
				</font>
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'maquina_vs_operacao.php'" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: M�quina(s) para Gerenciar Opera��o(�es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table border="0" width="900" align="center" cellspacing ='1' cellpadding='1'>
	<tr class="linhacabecalho" align="center">
		<td colspan="8">
			<font color='#FFFFFF' size='-1'>
				M�quina(s) para Gerenciar Opera��o(�es)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='2'>
			<font color='#FFFFFF' size='-1'>
				M�quina
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				M�q. por Func.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Anos p/ Amort.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Porc. Ferr.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Sal. M�dio M�q.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Custo Hora M�q.
			</font>
		</td>
	</tr>
<?
	//Aqui eu fa�o uma listagem de todas as m�quinas da F�brica que est�o cadastradas no ERP ...
	$sql = "SELECT * 
			FROM `maquinas` 
			WHERE `ativo` = '1' ORDER BY nome ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="window.location = 'maquina_vs_operacao.php?passo=1&id_maquina=<?=$campos[$i]['id_maquina'];?>'" align='center'>
		<td width='10'>
                    <a href="#" class="link">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td align='left'>
			<a href="#" class="link">
				<?=$campos[$i]['nome'];?>
			</a>
		</td>
		<td align='right'>
			<?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde_maq_vs_func'], 2, ',', '.');?>
		</td>
		<td>
			<?=number_format($campos[$i]['duracao'], 2, ',', '.');?>
		</td>
		<td>
			<?=number_format($campos[$i]['porc_ferramental'], 2, ',', '.');?>
		</td>
		<td align='right'>
			<?='R$ '.number_format($campos[$i]['salario_medio'], 2, ',', '.');?>
		</td>
		<td align='right'>
			<?='R$ '.number_format($campos[$i]['custo_h_maquina'], 2, ',', '.');?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='8'>
			<font color='#FFFFFF' size='-1'>
				&nbsp;
			</font>
		</td>
	</tr>
</table>
</body>
</html>
<?}?>