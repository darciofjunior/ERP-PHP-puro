<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_acabado = $_POST['id_produto_acabado'];
    $id_op              = $_POST['id_op'];
    $hdd_observacao     = $_POST['hdd_observacao'];
}else {
    $id_produto_acabado = $_GET['id_produto_acabado'];
    $id_op              = $_GET['id_op'];
    $hdd_observacao     = $_GET['hdd_observacao'];
}

/************************Toda vez que eu quiser Estornar Baixa do PA no Estoque************************/
if(!empty($hdd_observacao)) {
//1)
/************************Busca de Dados************************/
	$data_ocorrencia = date('Y-m-d H:i:s');
//Aqui eu trago alguns dados do PA p/ passar por e-mail via parâmetro ...
	$sql = "SELECT bp.qtde_baixa, pa.discriminacao 
                FROM `produtos_acabados` pa 
                INNER JOIN `baixas_ops_vs_pas` bp on bp.id_op = '$id_op' AND bp.id_produto_acabado = pa.id_produto_acabado 
                WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
	$campos_produto = bancos::sql($sql);
	$qtde_baixa     = $campos_produto[0]['qtde_baixa'];
	$discriminacao  = $campos_produto[0]['discriminacao'];
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver estornando Baixa do PA no Estoque, então o Sistema dispara um e-mail 
informando qual Produto Acabado que está sendo estornado a Baixa do PA no Estoque ...
//-Aqui eu busco o login de quem está estornado a Baixa do PA no Estoque ...*/
	$sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
	$campos_login       = bancos::sql($sql);
	$login_estornando   = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
	$complemento_justificativa = '<br><b>Produto Acabado: </b>'.$discriminacao.' <br><b>Qtde da Baixa: </b>'.number_format($qtde_baixa, 2, ',', '.').' <br><b>N.º da OP: </b>'.$id_op.' <br><b>Login: </b>'.$login_estornando;
	$txt_justificativa = $complemento_justificativa.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_observacao;
//Aqui eu mando um e-mail informando quem e porque que estornou a Baixa do PA no Estoque ...
	$destino        = $estornar_baixa_pi_estoque;
	$mensagem_email = $txt_justificativa;
	comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Estornar Baixa do PA no Estoque', $mensagem_email);
//3)
/************************Alteração************************/
//Junto com a Observação, eu também acrescento o login de quem está estornando ...
	$hdd_observacao.= ' - '.$login;
//Inserindo os Dados no BD ...
	$sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `observacao`, `acao`, `data_sys`) VALUES (NULL, '$id_produto_acabado', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '$qtde_baixa', '', '$hdd_observacao', 'S', '$data_ocorrencia') ";
	bancos::sql($sql);
	$id_baixa_manipulacao_pa = bancos::id_registro();
	estoque_acabado::manipular($id_produto_acabado, $qtde_baixa);
	estoque_acabado::qtde_estoque($id_produto_acabado, 1);
//Se existir Baixa, então eu estorno essa Baixa dessa OP e desse PA no Banco de Dados ...
	$sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '$id_produto_acabado', '$id_op', '$id_baixa_manipulacao_pa', '-$qtde_baixa', '$hdd_observacao', '$data_ocorrencia', '3') ";
	bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('BAIXA DE PA ESTORNADA COM SUCESSO !')
        parent.document.form.passo.value = 2
        parent.document.form.submit()
    </Script>
<?}?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
//Estornar Baixa do PA no Estoque ...
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ESTORNAR A BAIXA DO PA NO ESTOQUE ?')
    if(resposta == false) {
        return false
    }else {
        var observacao = prompt('DIGITE UMA OBSERVAÇÃO P/ ESTORNAR A BAIXA DO PA NO ESTOQUE: ')
    }
    document.form.hdd_observacao.value = observacao
//Controle com a Observação ...
    if(document.form.hdd_observacao.value == '' || document.form.hdd_observacao.value == 'null' || document.form.hdd_observacao.value == 'undefined') {
        alert('OBSERVAÇÃO INVÁLIDA !!!\nDIGITE UMA OBSERVAÇÃO P/ ESTORNAR A BAIXA DO PA NO ESTOQUE !')
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body onload="alert('FUNÇÃO TEMPORARIAMENTE INDISPONÍVEL !!!')">
<form name='form' method='post' action=''>
<!--Controle de Tela-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='id_op' value='<?=$id_op;?>'>
<input type='hidden' name='hdd_observacao'>
</form>
<font color='red'>
    <center><b>FUNÇÃO TEMPORARIAMENTE INDISPONÍVEL !!!</b></center>
</font>
</body>
</html>