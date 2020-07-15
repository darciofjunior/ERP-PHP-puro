<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Segurança especial para este relatório ...
if(empty($_SESSION['id_funcionario'])) exit('<center><font face="Verdana, Geneva, Arial, Helvetica, sans-serif" color="red"><b>SESSÃO EXPIRADA !!! FAVOR LOGAR NOVAMENTE !</b></font></center>');

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$txt_data_inicial 	= $_POST['txt_data_inicial'];
	$txt_data_final 	= $_POST['txt_data_final'];
	$cmb_funcionario 	= $_POST['cmb_funcionario'];
	$cmd_consultar		= $_POST['cmd_consultar'];
}else {
	$txt_data_inicial 	= $_GET['txt_data_inicial'];
	$txt_data_final 	= $_GET['txt_data_final'];
	$cmb_funcionario 	= $_GET['cmb_funcionario'];
	$cmd_consultar		= $_GET['cmd_consultar'];
}

/*************************Controle para saber se o usuário terá permissão Total no Relatório*************************/
$usuario_com_acesso = 0;//Valor Padrão ...
/*Usuários que terão acesso a combo no qual poderão fazer qualquer tipo de Manipulação no Relatório 
Funcionários: Roberto 62, Wilson Chefe 68, Dárcio 98 e Arnaldo Netto 147 ...*/
$vetor_usuarios_com_acesso = array('62', '68', '98', '147');

for($i = 0; $i < count($vetor_usuarios_com_acesso); $i++) {
//Se o usuário logado for um dos designados acima, então este terá acesso ao combo ...
	if($vetor_usuarios_com_acesso[$i] == $_SESSION['id_funcionario']) $usuario_com_acesso = 1;
}

//Se o usuário não tem acesso, vem tudo travado ...
if($usuario_com_acesso == 0) {
	$disabled 			= 'disabled';
	$class				= 'textdisabled';
	$cmb_funcionario 	= $_SESSION['id_funcionario'];//A combo virá com a Sugestão do Funcionário logado ...
}else {
	$class				= 'combo';
}
/********************************************************************************************************************/
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Atendimento Di&aacute;rio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
			return false
	}
	if(!data('form', 'txt_data_final', '4000', 'FIM')) {
		return false
	}
	var data_inicial 	= document.form.txt_data_inicial.value
	var data_final 		= document.form.txt_data_final.value
	data_inicial 		= data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
	data_final 			= data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
	data_inicial 		= eval(data_inicial)
	data_final 			= eval(data_final)
	
	if(data_final < data_inicial) {
		alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
}
</Script>
</head>
<body>
<form name="form" action='' method="POST" onsubmit="return validar()">
<table border="0" width="80%" align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan='11'>
			Relat&oacute;rio de Atendimento Di&aacute;rio
			<br>Data Inicial: 
			<?
				if(empty($txt_data_inicial)) {//Sugestão p/ a primeira vez que se carrega a Tela ...
					$txt_data_inicial 	= date('d/m/Y');
					$txt_data_final 	= date('d/m/Y');
				}
				$data_inicial = data::datatodate($txt_data_inicial, '-');
				$data_final = data::datatodate($txt_data_final, '-');
			?>
			<input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
			<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
			&nbsp;Data Final:
			<input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
			<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
			&nbsp;
			<select name='cmb_funcionario' title='Selecione o Funcionário' class='<?=$class;?>' <?=$disabled;?>>
			<?
                            //Aqui eu listo todos os Funcs que ainda trabalham na Empresa, do Depto. de Vendas ...
                            $sql = "SELECT id_funcionario, nome 
                                    FROM `funcionarios` 
                                    WHERE `id_departamento` = '3' 
                                    AND `status` <= '1' ORDER BY nome ";
                            echo combos::combo($sql, $cmb_funcionario);
			?>
			</select>
			&nbsp;
		 	<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
		</td>
	</tr>
<?
/*****************Se já submeteu então*****************/
if(!empty($cmd_consultar)) {
//Aqui eu busco todas as Ocorrências de Atendimento Diário na Respectiva Data e do Respectivo Funcionário selecionado ...
	if(!empty($cmb_funcionario)) $condicao_funcionario = " AND (ad.`id_funcionario_registrou` = '$cmb_funcionario' OR ad.id_funcionario_responder = '$cmb_funcionario') ";
	
	$sql = "SELECT ad.`id_atendimento_diario`, IF(ad.id_cliente = 0, ad.pessoa_atendida, c.`razaosocial`) AS cliente, r.`nome_fantasia`, ad.`contato` , ad.`procedimento`, ad.`observacao`, ad.`feedback`, f.`nome`, DATE_FORMAT(ad.`data_sys_registrou`, '%d/%m/%Y') AS data_registro, TIME_FORMAT(ad.`data_sys_registrou`, '%H:%i:%s') AS hora_registro, DATE_FORMAT(ad.`data_sys_resposta`, '%d/%m/%Y') AS data_feedback, TIME_FORMAT(ad.`data_sys_resposta`, '%H:%i:%s') AS hora_feedback, ad.`numero` 
			FROM `atendimentos_diarios` ad 
			LEFT JOIN `clientes` c ON c.`id_cliente` = ad.`id_cliente` 
			INNER JOIN `representantes` r ON r.`id_representante` = ad.`id_representante` 
			INNER JOIN `funcionarios` f ON f.`id_funcionario` = ad.`id_funcionario_registrou` 
			WHERE SUBSTRING(ad.`data_sys_registrou`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
			$condicao_funcionario ";
	$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
</table>
<table border="0" width="80%" align="center">
    <tr class='atencao' align="center">
        <td>
            <?=$mensagem[1];?>
        <td>
    </tr>
</table>
<?
        exit;
    }
?>
	<tr class="linhadestaque" align="center">
		<td>
			Cliente
		</td>
		<td>
			Contato
		</td>
		<td>
			Representante
		</td>
		<td>
			Procedimento
		</td>
		<td>
			Funcionário que Registrou
		</td>
		<td>
			Data e Hora de Registro
		</td>
		<td>
			Observação
		</td>
		<td>
			Data e Hora de FeedBack
		</td>
		<td>
			FeedBack
		</td>
	</tr>
<?
	$vetor_procedimentos = array('C' => 'Ocorrência', 'O' => 'Orçamento', 'P' => 'Pedido', 'OC' => 'OC');
	for ($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td align="left">
			<?=$campos[$i]['cliente'];?>
		</td>
		<td>
			<?=$campos[$i]['contato'];?>
		</td>
		<td>
			<?=$campos[$i]['nome_fantasia'];?>
		</td>
		<td>
			<?
				if($campos[$i]['procedimento'] == 'O' || $campos[$i]['procedimento'] == 'P' || $campos[$i]['procedimento'] == 'OC') {
					if($campos[$i]['procedimento'] == 'O') {
						$url = "../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos[$i]['numero'];
					}else if($campos[$i]['procedimento'] == 'P') {
						$url = "../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[$i]['numero'];
					}else if($campos[$i]['procedimento'] == 'OC') {
						$url = "../../vendas/ocs/itens/itens.php?id_oc=".$campos[$i]['numero'];
					}
					echo $vetor_procedimentos[$campos[$i]['procedimento']].' / ';
			?>
				<a href="javascript:nova_janela('<?=$url;?>', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">	
					<?=$campos[$i]['numero'];?>
				</a>
			<?		
				}else {
					echo $vetor_procedimentos[$campos[$i]['procedimento']];
				}
			?>
		</td>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
			<?=$campos[$i]['data_registro'].' '. $campos[$i]['hora_registro'];?>
		</td>
		<td bgcolor='#D8D8D8'>
			<?=$campos[$i]['observacao'];?>
		</td>
		<td>
			<?if($campos[$i]['data_feedback'] != '00/00/0000') echo $campos[$i]['data_feedback'].' '. $campos[$i]['hora_feedback'];?>
		</td>
		<td bgcolor='#D8D8D8'>
			<?=$campos[$i]['feedback'];?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='9'>
			<input type="button" name="cmd_imprimir" value="Imprimir" title="Imprimir" style="purple" onclick="window.print()" class="botao">
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?}?>