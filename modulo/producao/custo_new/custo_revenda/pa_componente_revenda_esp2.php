<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/custos_new.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_revenda/pa_componente_revenda_esp.php', '../../../../');

$mensagem[1] = '<font class="confirmacao">FORNECEDOR DESATRELADO COM SUCESSO PARA ESTE P.A.</font>';

//Aqui significa que está desatrelando um fornecedor do P.A.
if(!empty($id_fornecedor_prod_insumo)) {
	intermodular::excluir_varios_pi_fornecedor($id_fornecedor_prod_insumo);
	$valor = 1;
}

$sql = "Select discriminacao from produtos_acabados where id_produto_acabado = '$id_produto_acabado' limit 1";
$campos = bancos::sql($sql);
$discriminacao = $campos[0]['discriminacao'];

//Verifico se existem Fornecedores atrelados p/ o Produto Acabado passado por parâmetro ...
$sql = "SELECT f.id_fornecedor, f.razaosocial, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
        FROM `produtos_acabados` pa 
        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.`ativo` = '1' 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
        AND pa.`ativo` = '1' ORDER BY f.razaosocial ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function desatrelar_item(id) {
	var mensagem = confirm('DESEJA REALMENTE DESATRELAR ESTE ITEM ?')
	if(mensagem == false) {
		return false
	}else {
		document.form.id_fornecedor_prod_insumo.value = id
		document.form.submit()
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='post'>
<table width='780' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='4'> <b>
			<?=$mensagem[$valor];?>
		</b> </td>
	</tr>
<?
if($linhas == 0) {
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='4'>
			<font size='-1'>
				Não há fornecedor(es) atrelado(s)
			</font>
		</td>
	</tr>
<?
}else {
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='4'>
			<font color='#FFFFFF' size='-1'>
				Fornecedor(es) Atrelado(s) do Produto: <b><?=$discriminacao;?></b>
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='4'>
			<font color='#FFFFFF' size='-1'>
				Fornecedor
			</font>
		</td>
	</tr>
<?
	$id_fornecedor_setado=custos::procurar_fornecedor_default_revenda($id_produto_acabado, "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal">
            <td width="15">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </td>
		<td>
<?
/*Aqui eu passo a variável chkt_fornecedor_prod_insumo[], como vetor para não
furar o loop no foreach, e levo o parâmetro atalho para quando clicar em consultar
novamente voltar exatamente nessa tela*/
?>
			<a href="index.php?id_fornecedor=<?=$campos[$i]['id_fornecedor']?>&razaosocial=<?=$campos[$i]['razaosocial']?>&chkt_fornecedor_prod_insumo[]=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>&atalho=1" class="link">
<?
				if($campos[$i]['id_fornecedor']==$id_fornecedor_setado) {
					echo "<b>".$campos[$i]['razaosocial']."<a/> <= DEFAULT</b>";
				} else {
					echo $campos[$i]['razaosocial']."</a>";
				}
?>
		</td>
		<td width="25" align="center">
			<img src="../../../../imagem/filtro.jpg" border='0' title="Filtro por Fornecedor" onClick="window.location = 'consultar_produtos.php?id_fornecedor=<?=$campos[$i]['id_fornecedor']?>&razaosocial=<?=$campos[$i]['razaosocial']?>&id_produto_acabado=<?=$id_produto_acabado;?>&tipo=ESP'">
		</td>
		<td width="25" align="center">
			<img src="../../../../imagem/menu/excluir.png" border='0' title="Desatrelar Item" onClick="desatrelar_item('<?=$campos[$i]['id_fornecedor_prod_insumo'];?>')">
		</td>
	</tr>
<?
	}
}
?>
	<tr class="linhadestaque" align="left">
		<td colspan='4'>
			<a href="javascript:nova_janela('atrelar_fornecedor.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'ATRELAR', '', '', '', '', 400, 700, 'c', 'c', '', '', 's', 's', '', '', '')">
				<font color="yellow">
					<b>Atrelar Fornecedor</b>
				</font>
			</a>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='4'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'pa_componente_revenda_esp.php<?=$parametro;?>'" class='botao'>
		</td>
	</tr>
</table>
<input type="hidden" name="id_fornecedor_prod_insumo">
<input type="hidden" name="id_produto_acabado" value="<?=$id_produto_acabado;?>">
</form>
</body>
</html>
