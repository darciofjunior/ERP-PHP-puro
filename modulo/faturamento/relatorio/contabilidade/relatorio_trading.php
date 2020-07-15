<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$txt_data_inicial = $_POST['txt_data_inicial'];
	$txt_data_final = $_POST['txt_data_final'];
	$cmd_consultar = $_POST['cmd_consultar'];
}else {
	$txt_data_inicial = $_GET['txt_data_inicial'];
	$txt_data_final = $_GET['txt_data_final'];
	$cmd_consultar = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relatório de Trading ::.</title>
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
	if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
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
		alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
	var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
	if(dias > 365) {
		alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
	document.form.submit()
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='POST' action='<?=$PHP_SELF;?>' onsubmit="return validar()">
<table width='780' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Relatório de Trading
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='3'> 
			Data Inicial:
			<?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
				if(empty($txt_data_inicial)) {
					$txt_data_inicial = '01/01/'.(date('Y') - 1);
					$txt_data_final = date('31/12/').(date('Y') - 1);
				}
			?>
			<input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
			<input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
			<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
		</td>
	</tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
	$data_inicial = data::datatodate($txt_data_inicial, '-');
	$data_final = data::datatodate($txt_data_final, '-');
/*Busca das NFs que estejam no Período digitado pelo Usuário, que não estejam Canceladas ou Devolvidas e com Itens em que 
a Operação de Faturamento sejam igual a Industrial ...*/
	$sql = "Select if(c.razaosocial = '', c.nomefantasia, c.razaosocial) as cliente, c.`cnpj_cpf`, cf.classific_fiscal, nfs.id_empresa, sum(nfsi.qtde * nfsi.valor_unitario) as total_por_classific_fiscal 
			from nfs 
			inner join clientes_contatos cc on cc.id_cliente_contato = nfs.id_cliente_contato 
			inner join clientes c on c.id_cliente = cc.id_cliente 
			inner join nfs_itens nfsi on nfsi.id_nf = nfs.id_nf and c.id_pais = 31
			inner join classific_fiscais cf on cf.id_classific_fiscal = nfsi.id_classific_fiscal 
			where nfs.data_emissao between '$data_inicial' and '$data_final' 
			and nfs.trading = '1' 
			and nfs.status NOT IN (5, 6) 
			and nfs.id_empresa in (1, 2) group by nfs.id_empresa, c.razaosocial, nfsi.id_classific_fiscal order by nfs.id_empresa, cliente, cf.classific_fiscal ";
			
	$sql_extra = "Select if(c.razaosocial = '', c.nomefantasia, c.razaosocial) as cliente, c.`cnpj_cpf`, cf.classific_fiscal, nfs.id_empresa, sum(nfsi.qtde * nfsi.valor_unitario) as total_por_classific_fiscal 
			from nfs 
			inner join clientes_contatos cc on cc.id_cliente_contato = nfs.id_cliente_contato 
			inner join clientes c on c.id_cliente = cc.id_cliente 
			inner join nfs_itens nfsi on nfsi.id_nf = nfs.id_nf and c.id_pais = 31
			inner join classific_fiscais cf on cf.id_classific_fiscal = nfsi.id_classific_fiscal 
			where nfs.data_emissao between '$data_inicial' and '$data_final' 
			and nfs.trading = '1' 
			and nfs.status NOT IN (5, 6) 
			and nfs.id_empresa in (1, 2) group by nfs.id_empresa, c.razaosocial, nfsi.id_classific_fiscal order by nfs.id_empresa, cliente, cf.classific_fiscal ";
	$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
		$id_empresa_anterior = '';
		for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada no loop, se for 
então eu atribuo a Empresa Atual p/ a Empresa Anterior ...*/
			if($id_empresa_anterior != $campos[$i]['id_empresa']) {
				$id_empresa_anterior = $campos[$i]['id_empresa'];
//Só não mostro essa linha quando acaba de Entrar no Loop ...
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
					$total_por_empresa = 0;//Zero p/ não ficar herdando valores do Loop Anterior ...
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
		<td bgcolor='#CECECE'><b>Cliente / CNPJ / CPF</b></td>
		<td bgcolor='#CECECE'><b>Classific Fiscal</b></td>
		<td bgcolor='#CECECE'><b>Valor R$</b></td>
	</tr>
<?
			}
?>
	<tr class='linhanormal' align="center">
		<td align="left">
                <?
                    echo $campos[$i]['cliente'].' - ';
                    if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                        if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                            echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                        }else {//CNPJ ...
                            echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                        }
                    }
                ?>
		</td>
		<td>
			<?=$campos[$i]['classific_fiscal'];?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['total_por_classific_fiscal'], 2, ',', '.');?>
		</td>
	</tr>
<?
			$total_por_empresa+= $campos[$i]['total_por_classific_fiscal'];
			$total_geral+= $campos[$i]['total_por_classific_fiscal'];
		}
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
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
			<input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title="Atualizar Relatório" id="cmd_atualizar" class="botao">
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
//Se não foi passado nenhum representante por parâmetro ...
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