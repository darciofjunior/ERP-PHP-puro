<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/comunicacao.php');
session_start('funcionarios');

/**************************Script para enviar email de Cobrança**************************/
if(!empty($_POST['mensagem'])) {
	//Aqui eu busco o Cliente para quem irei enviar e-mail ...
	$sql = "SELECT c.email, cr.id_nf 
                FROM `contas_receberes` cr 
                INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                WHERE cr.id_conta_receber = '$_POST[id_conta_receber]' LIMIT 1 ";
	$campos_email_cliente = bancos::sql($sql);
	
	//Agora aqui eu busco o Representante do Pedido a quem irei enviar e-mail também ...
	$sql = "SELECT DISTINCT(pvi.id_representante) 
                FROM `nfs_itens` nfsi 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
                WHERE nfsi.id_nf = '".$campos_email_cliente[0]['id_nf']."' LIMIT 1 ";
	$campos_representante = bancos::sql($sql);
	
	/*Aqui eu verifico se o Representante possui um supervisor, se sim, será para o Supervisor 
	a quem irei enviar o e-mail ...*/
	$sql = "SELECT id_representante_supervisor 
                FROM `representantes_vs_supervisores` 
                WHERE `id_representante` = '".$campos_representante[0]['id_representante']."' LIMIT 1 ";
	$campos_representante_supervisor = bancos::sql($sql);
	if(count($campos_representante_supervisor) == 1) {//Existe supervisor ...
		$id_representante = $campos_representante_supervisor[0]['id_representante_supervisor'];
	}else {//Não existe supervisor ...
		$id_representante = $campos_representante[0]['id_representante'];
	}
	
	//Aqui eu vejo se o Representante é um funcionário ...
	$sql = "SELECT f.email_externo 
                FROM `representantes_vs_funcionarios` rf 
                INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                WHERE rf.id_representante = '$id_representante' LIMIT 1 ";
	$campos_email_representante = bancos::sql($sql);
	
	//Aqui eu busco o e-mail do Usuário logado que realizou a Cobrança ...
	$sql = "SELECT email_externo 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
	$campos_email_usuario_logado = bancos::sql($sql);
	comunicacao::email($campos_email_usuario_logado[0]['email_externo'], $campos_email_cliente[0]['email'], $campos_email_representante[0]['email_externo'], 'Cobrança', $_POST['mensagem']);
?>
	<Script Language = 'JavaScript'>
            alert('EMAIL DE COBRANÇA ENVIADO COM SUCESSO !')
            opener.parent.itens.recarregar_tela()
            window.close()
	</Script>
<?
	exit;
}
/****************************************************************************************/
$vetor_meses = array('', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro');
//Busca de alguns dados p/ apresentar na Carta de Cobrança do Cliente ...
$sql = "SELECT IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, cr.num_conta, DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada, cr.valor 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
        WHERE cr.id_conta_receber = '$_GET[id_conta_receber]' LIMIT 1 ";
$campos = bancos::sql($sql);

//Aqui eu verifico se foi feito pelo menos uma cobrança p/ uma determinada duplicata ...
$sql = "SELECT COUNT(id_carta_cobranca) AS total_carta_cobranca 
        FROM `cartas_cobrancas` 
        WHERE `id_conta_receber` = '$_GET[id_conta_receber]' 
        GROUP BY id_conta_receber ";
$campos_cartas_cobrancas 	= bancos::sql($sql);
$total_carta_cobranca 		= $campos_cartas_cobrancas[0]['total_carta_cobranca'] + 1;
if($total_carta_cobranca >= 3) {//A partir da 3ª via, será a carta mais agressiva ...
	$calculos_conta_receber = financeiros::calculos_conta_receber($_GET['id_conta_receber']);
	$sql = "INSERT INTO `cartas_cobrancas` (`id_carta_cobranca`, `id_conta_receber`, `id_funcionario`, `carta_numero`, `valor`, `valor_principal`, `data_sys`) VALUES (NULL, '$_GET[id_conta_receber]', '$_SESSION[id_funcionario]', '$total_carta_cobranca', '".$calculos_conta_receber['valor_reajustado']."', '".$campos[0]['valor']."', '".date('Y-m-d H:i:s')."') ";
}else {//Primeira e Segunda Via, ainda são normais ...
	$sql = "INSERT INTO `cartas_cobrancas` (`id_carta_cobranca`, `id_conta_receber`, `id_funcionario`, `carta_numero`, `valor`, `data_sys`) VALUES (NULL, '$_GET[id_conta_receber]', '$_SESSION[id_funcionario]', '$total_carta_cobranca', '".$campos[0]['valor']."', '".date('Y-m-d H:i:s')."') ";
}
bancos::sql($sql);

$mensagem = '<br/><img src="../../../../../imagem/marcas/Logo Grupo Albafer.jpg" width="80">';
$mensagem.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$mensagem.= '<img src="../../../../../imagem/marcas/Logo Cabri.jpg" width="60" height="60">';
$mensagem.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$mensagem.= '<img src="../../../../../imagem/marcas/Logo Heinz.jpg" width="150">';
$mensagem.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$mensagem.= '<img src="../../../../../imagem/marcas/Logo NVO.jpg" width="150">';
$mensagem.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$mensagem.= '<img src="../../../../../imagem/marcas/Logo Tool.jpg" width="150">';
$mensagem.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$mensagem.= '<img src="../../../../../imagem/marcas/Logo Warrior.jpg" width="150">';
$mensagem.='_________________________________________________________________________________________________________________________';
$mensagem.= '<p align="center">';
$mensagem.= '<b>CARTA DE COBRANCA N.º '.$total_carta_cobranca.'</b>';
$mensagem.= '<p align="right">';
$mensagem.= 'São Paulo, '.date('d').' de '.$vetor_meses[intval(date('m'))].' de '.date('Y').'.';
$mensagem.= '<p align="left">';
$mensagem.= 'A';
$mensagem.= '<br/><br/>';
$mensagem.= $campos[0]['cliente'];
$mensagem.= '<br/><br/>';

if($total_carta_cobranca >= 3) {//Cartas Agressivas ...
    $mensagem.= 'A/C:     Departamento de Contas a Pagar';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= '<b>Referente: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Titulos Vencidos</b>';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Duplicata nº &nbsp;&nbsp;&nbsp;'.$campos[0]['num_conta'].'<br/>';
    $mensagem.= 'Vencimento:&nbsp;&nbsp;&nbsp;&nbsp;'.$campos[0]['data_vencimento_alterada'].'<br/>';
    $mensagem.= 'Valor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');
    $mensagem.= '<br/>';
    $mensagem.= 'TOTAL PRINCIPAL R$ '.number_format($campos[0]['valor'], 2, ',', '.');
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Conforme solicitado anteriormente, pedimos urgente esclarecimento e uma posicao definitiva da liquidacao desta(s) pendencia(s) financeira conosco.';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Favor entrar em contato urgente, para acerto da mesma.';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Caso o pagamento ja tenha sido efetuado, pedimos que aceitem nossas desculpas, desconsiderando este aviso.';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Atenciosamente,';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'GRUPO ALBAFER';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Contas à Receber';
}else {//Cartas Normais ...
    $mensagem.= 'A/C:     Departamento de Contas a Pagar';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Referente:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    $mensagem.= 'Duplicata nº '.$campos[0]['num_conta'].'<br/>';
    $mensagem.= 'Vencimento:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$campos[0]['data_vencimento_alterada'].'<br/>';
    $mensagem.= 'Valor Principal: &nbsp;&nbsp;&nbsp;R$ '.number_format($campos[0]['valor'], 2, ',', '.');
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Informamos que nao localizamos em nosso controle o pagamento da duplicata acima mencionada.';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Pedimos urgente esclarecimento, evitando os transtornos habituais, tais como:';
    $mensagem.= '<br/><br/>';
    $mensagem.= '* Bloqueio de fornecimento<br/>';
    $mensagem.= '* Envio de titulo a cartorio<br/>';
    $mensagem.= '* Negativacao de nome junto ao SERASA<br/>';
    $mensagem.= '* Etc.';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Caso o pagamento ja tenha sido efetuado, pedimos que aceitem nossas desculpas, desconsiderando este aviso.';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Maiores informacoes, favor entrar em contato com nosso departamento financeiro:';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'Srs. Renato / Simone';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Atenciosamente,';
    $mensagem.= '<br/><br/>';
    $mensagem.= 'GRUPO ALBAFER';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Contas a Receber';
    $mensagem.= '<br/><br/><br/><br/>';
    $mensagem.= 'Central de Atendimento:<br/>';
}
$mensagem.='_________________________________________________________________________________________________________________________<br/>';
$mensagem.= 'ALBAFER - Industria e Comercio de Ferramentas Ltda. - TOOL MASTER - Industria Metalurgica Ltda.<br/>';
$mensagem.= 'R. Dias da Silva n° 1.173/1.183 - Vila Maria - Sao Paulo - SP - Brasil - CEP: 02114-002<br/>';
$mensagem.= 'PABX (Fone/Fax): (55 11) 2972-5655 - E-mail: financeiro@grupoalbafer.com.br - Site: www.grupoalbafer.com.br';
echo $mensagem;
?>
<html>
<head>
<title>.:: Carta de Cobrança ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF;?>'>
<input type='hidden' name='mensagem' value='<?=$mensagem;?>'>
<input type='hidden' name='id_conta_receber' value='<?=$_GET['id_conta_receber'];?>'>
<center>
	<input type='submit' name='cmd_enviar_email' value='Enviar E-mail' title='Enviar E-mail' style="color:red" class='botao'>
</center>
</form>
</body>
</html>