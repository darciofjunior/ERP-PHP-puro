<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relat�rio de Vendas por Ano / Classifica��o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
	if(!data('form', 'txt_data_inicial', '4000', 'IN�CIO')) {
			return false
	}
//Data Final
	if(!data('form', 'txt_data_final', '4000', 'FIM')) {
		return false
	}
	var data_inicial = document.form.txt_data_inicial.value
	var data_final = document.form.txt_data_final.value
	data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
	data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
	data_inicial = eval(data_inicial)
	data_final = eval(data_final)
	
	if(data_final < data_inicial) {
		alert('DATA FINAL INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
/**Verifico se o intervalo entre Datas � > do que 1 ano. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
	var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
	if(dias > 365) {
		alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
	document.form.submit()
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='POST' action='' onsubmit="return validar()">
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Relat�rio de Vendas por Ano / Classifica��o
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
            <td colspan='3'> 
                Data Inicial:
                <?
//Sugest�o de Per�odo na Primeira vez em que carregar a Tela ...
                    if(empty($txt_data_inicial)) {
                        $txt_data_inicial = '01/01/'.(date('Y') - 1);
                        $txt_data_final = date('31/12/').(date('Y') - 1);
                    }
                    if(!empty($chkt_deduzir_devolucoes))    $checked_devolucoes  = 'checked';
                    if(!empty($chkt_deduzir_exportacoes))   $checked_exportacoes = 'checked';
                ?>
                <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
                <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
                <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
                <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
                -
                <input type="checkbox" name="chkt_deduzir_devolucoes" id="chkt_deduzir_devolucoes" value="S" class="checkbox" <?=$checked_devolucoes;?>>
                <label for='chkt_deduzir_devolucoes'>
                    Deduzir Devolu��es
                </label>
                -
                <input type="checkbox" name="chkt_deduzir_exportacoes" id="chkt_deduzir_exportacoes" value="S" class="checkbox" <?=$checked_exportacoes;?>>
                <label for='chkt_deduzir_exportacoes'>
                    Deduzir Exporta��es
                </label>
                <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
            </td>
	</tr>
<?
//Se foram digitadas as Datas acima, ent�o realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
	$data_inicial = data::datatodate($txt_data_inicial, '-');
	$data_final = data::datatodate($txt_data_final, '-');
        
        $campo_total_por_ano_classific_fiscal = (!empty($chkt_deduzir_devolucoes))  ? 'SUM((nfsi.qtde - nfsi.qtde_devolvida) * nfsi.valor_unitario) AS total_por_ano_classific_fiscal' : 'SUM(nfsi.qtde * nfsi.valor_unitario) AS total_por_ano_classific_fiscal ';
        if(!empty($chkt_deduzir_exportacoes)) $condicao_exportacao = " AND c.id_pais = '31' ";//Significa q o usu�rio s� quer verificar as NF�s do Brasil ...
        
//Busca das NFs que estejam no Per�odo digitado pelo Usu�rio, que n�o estejam Canceladas ...
	$sql = "SELECT cf.id_classific_fiscal, cf.classific_fiscal, nfs.id_empresa, $campo_total_por_ano_classific_fiscal 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                INNER JOIN `paises` p ON p.id_pais = c.id_pais $condicao_exportacao 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = nfsi.id_classific_fiscal 
                WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND nfs.`status` <> '5' 
                AND nfs.`id_empresa` IN (1, 2) GROUP BY nfs.id_empresa, nfsi.id_classific_fiscal ORDER BY nfs.id_empresa, cf.classific_fiscal ";
			
	$sql_extra = "SELECT COUNT(nfs.id_nf) AS total_registro 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                    INNER JOIN `paises` p ON p.id_pais = c.id_pais $condicao_exportacao 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                    INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = nfsi.id_classific_fiscal 
                    WHERE nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                    AND nfs.`status` <> '5' 
                    AND nfs.`id_empresa` IN (1, 2) GROUP BY nfs.id_empresa, nfsi.id_classific_fiscal ";
	$campos = bancos::sql($sql, $inicio, 2000, 'sim', $pagina);
        $linhas = count($campos);
	if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
		$id_empresa_anterior = '';
		for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior � Diferente da Empresa Atual que est� sendo listada no loop, se for 
ent�o eu atribuo a Empresa Atual p/ a Empresa Anterior ...*/
			if($id_empresa_anterior != $campos[$i]['id_empresa']) {
				$id_empresa_anterior = $campos[$i]['id_empresa'];
//S� n�o mostro essa linha quando acaba de Entrar no Loop ...
				if($i > 0) {
?>
	<tr class="linhacabecalho" align="right">
		<td colspan='2'>
			<font color='yellow' size='-1'>
				Total por Empresa => 
			</font>
		</td>
		<td>
			<?='R$ '.number_format($total_por_empresa, 2, ',', '.');?>
		</td>
	</tr>
<?
					$total_por_empresa = 0;//Zero p/ n�o ficar herdando valores do Loop Anterior ...
				}
?>
	<tr class="linhadestaque">
		<td colspan='3'>
			<font color="yellow">
				<b>Empresa: </b>
			</font>
			<?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td bgcolor='#CECECE'><b>Classific Fiscal</b></td>
		<td bgcolor='#CECECE'><b>Fam�lia</b></td>
		<td bgcolor='#CECECE'><b>Valor R$</b></td>
	</tr>
<?
			}
?>
	<tr class='linhanormal' align="center">
		<td>
			<?=$campos[$i]['classific_fiscal'];?>
		</td>
		<td align="left">
		<?
			$sql = "SELECT nome 
                                FROM `familias` 
                                WHERE `id_classific_fiscal` = ".$campos[$i]['id_classific_fiscal']." LIMIT 1 ";
			$campos_familia = bancos::sql($sql);
			echo $campos_familia[0]['nome'];
		?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['total_por_ano_classific_fiscal'], 2, ',', '.');?>
		</td>
	</tr>
<?
			$total_por_empresa+= $campos[$i]['total_por_ano_classific_fiscal'];
			$total_geral+= $campos[$i]['total_por_ano_classific_fiscal'];
		}
?>
<!--Apresenta fora do Loop o Total Geral da �ltima Empresa-->
	<tr class="linhacabecalho" align="right">
		<td colspan='2'>
			<font color='yellow' size='-1'>
				Total por Empresa =>
			</font>
		</td>
		<td>
			<?='R$ '.number_format($total_por_empresa, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="right">
		<td colspan='2'>
			<font color='yellow' size='-1'>
				Total Geral => 
			</font>
		</td>
		<td>
			<?='R$ '.number_format($total_geral, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<input type="submit" name="cmd_atualizar" value="Atualizar Relat�rio" title="Atualizar Relat�rio" id="cmd_atualizar" class="botao">
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
//Se n�o foi passado nenhum representante por par�metro ...
	}else {
?>
	<tr class='atencao' align='center'>
		<td colspan='3'>
			<b><?=$mensagem[1];?></b>
		</td>
	</tr>
	
</table>
<?
	}
}
?>
</form>
</body>
</html>