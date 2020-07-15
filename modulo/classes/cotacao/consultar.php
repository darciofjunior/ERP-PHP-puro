<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
///Tratamento com as vari�veis que vem por par�metro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_cotacao = $_POST['txt_numero_cotacao'];
        $txt_data_emissao   = $_POST['txt_data_emissao'];
    }else {
        $txt_numero_cotacao = $_GET['txt_numero_cotacao'];
        $txt_data_emissao   = $_GET['txt_data_emissao'];
    }
    if(!empty($txt_data_emissao)) {
        //Aqui verifica se a Data est� no formato Americano p/ n�o ter que fazer o Tratamento Novamente
        if(substr($txt_data_emissao, 4, 1) != '-') $txt_data_emissao = data::datatodate($txt_data_emissao, '-');
        $condicao_data_emissao = "AND SUBSTRING(c.data_sys, 1, 10) = '$txt_data_emissao' ";
    }
    $sql = "SELECT c.id_cotacao, c.fator_mmv, c.qtde_mes_comprar, c.tipo_compra, c.desconto_especial_porc, substring(c.data_sys, 1, 10) as data_emissao, f.nome 
            FROM `cotacoes` c 
            INNER JOIN `funcionarios` f ON f.id_funcionario = c.id_funcionario 
            WHERE c.`id_cotacao` LIKE '%$txt_numero_cotacao%' 
            $condicao_data_emissao 
            ORDER BY id_cotacao DESC ";
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
<title>.:: Cota��o(�es) Gravada(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!option('form', 'opt_cotacao', 'SELECIONE UMA OP��O !')) {
        return false
    }
}
</script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=2';?>" method='post' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='8'>
            Cota��o(�es) Gravada(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            N.� Cota��o
        </td>
        <td>
            Emissor
        </td>
        <td>
            Tipo
        </td>
        <td>
            Fator MMV
        </td>
        <td>
            Qtde de M�s
        </td>
        <td>
            Desconto
        </td>
        <td>
            Data de Emiss�o
        </td>
        <td>
            Op��es
        </td>
    </tr>
<?
		for ($i = 0; $i < count($campos); $i++) {
/*Levo esse par�metro de nao_mostrar = 1, para n�o exibir os bot�es de Manipula��o da Cota��o, no caso o 
que eu realmente quero fazer � apenas visualizar a cota��o*/
			$url = "javascript:nova_janela('imprimir.php?id_cotacao={$campos[$i]['id_cotacao']}', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
	<tr class="linhanormal" onclick="options('form', 'opt_cotacao', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td onclick="<?=$url;?>">
			<a href="<?=$url;?>" title="Imprimir Cota��o" style="cursor:help" class="link">
				<?=$campos[$i]['id_cotacao'];?>
				&nbsp;
				<img src = '../../../imagem/impressora.gif' title='Imprimir Cota��o' border='0' style="cursor:help">
			</a>
		</td>
		<td align="left">
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
			<?if($campos[$i]['tipo_compra'] == 'E') {echo 'Export';}else {echo 'Nacional';}?>
		</td>
		<td>
			<?=number_format($campos[$i]['fator_mmv'], 1, ',', '.');?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde_mes_comprar'], 1, ',', '.');?>
		</td>
		<td>
			<?=number_format($campos[$i]['desconto_especial_porc'], 2, ',', '.');?>
		</td>
		<td>
			<?=data::datetodata($campos[$i]['data_emissao'], '/');?>
		</td>
		<td>
			<input type='radio' name='opt_cotacao' value="<?=$campos[$i]['id_cotacao'];?>" onclick="options('form', 'opt_cotacao', '<?=$i;?>', '#E8E8E8')">
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
            <td colspan='8'>
                <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class='botao'>
                <input type='submit' name="cmd_vincular_fornecedor" value="Vincular Fornecedor" title="Vincular Fornecedor" style="color:darkgreen" class='botao'>
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
}else if($passo == 2) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'vincular_fornecedor.php?id_cotacao=<?=$_POST['opt_cotacao'];?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cota��o(�es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_numero_cotacao.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cota��o(�es)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N�mero da Cota��o
        </td>
        <td>
            <input type="text" name="txt_numero_cotacao" title="N�mero da Cota��o" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emiss�o
        </td>
        <td>
            <input type="text" name="txt_data_emissao" title="Digite a Data de Emiss�o" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">&nbsp;
            <img src = "../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            Calend�rio
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.reset();document.form.txt_numero_cotacao.focus()" style="color:#ff9900" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>