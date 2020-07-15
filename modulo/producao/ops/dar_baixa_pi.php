<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/estoque_new.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_insumo  = $_POST['id_produto_insumo'];
    $id_op              = $_POST['id_op'];
    $opt_opcao          = $_POST['opt_opcao'];
}else {
    $id_produto_insumo  = $_GET['id_produto_insumo'];
    $id_op              = $_GET['id_op'];
    $opt_opcao          = $_GET['opt_opcao'];
}

/************************Toda vez que eu quiser Estornar Baixa do PI no Estoque************************/
if(!empty($opt_opcao)) {
//1)
/************************Busca de Dados************************/
    $data_ocorrencia = date('Y-m-d H:i:s');
//Aqui eu trago alguns dados do PI p/ passar por e-mail via parâmetro ...
    $sql = "SELECT bop.`qtde_baixa`, pi.`discriminacao` 
            FROM `produtos_insumos` pi 
            INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_produto_insumo` = pi.`id_produto_insumo` AND bop.`id_op` = '$id_op' 
            WHERE pi.`id_produto_insumo` = '$id_produto_insumo' ORDER BY bop.`id_baixa_op_vs_pi` DESC LIMIT 1 ";
    $campos_produto = bancos::sql($sql);
    $qtde_baixa     = $campos_produto[0]['qtde_baixa'];
    $discriminacao  = $campos_produto[0]['discriminacao'];
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver estornando Baixa do PI no Estoque, então o Sistema dispara um e-mail 
informando qual Produto Insumo que está sendo estornado a Baixa do PI no Estoque ...
//-Aqui eu busco o login de quem está estornado a Baixa do PI no Estoque ...*/
    $sql = "SELECT `login` 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login       = bancos::sql($sql);
    $login_estornando 	= $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
    $complemento_justificativa = '<br><b>Produto Insumo: </b>'.$discriminacao.' <br><b>Qtde da Baixa: </b>'.number_format($qtde_baixa, 2, ',', '.').' <br><b>N.º da OP: </b>'.$id_op.' <br><b>Login: </b>'.$login_estornando;
    $txt_justificativa  = $complemento_justificativa.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_observacao;
//Aqui eu mando um e-mail informando quem e porque que estornou a Baixa do PI no Estoque ...
    $destino            = $estornar_baixa_pi_estoque;
    $mensagem_email     = $txt_justificativa;
    comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Estornar Baixa do PI no Estoque', $mensagem_email);
//3)
/************************Alteração************************/
//Junto com a Observação, eu também acrescento o login de quem está estornando ...
    $hdd_observacao.= ' - '.$login_estornando;
    
    /*A primeira opção nunca mexe com a Qtde de Estoque do PI, porque se matamos as peças na Produção também perdemos este aço; 
    já na segunda opção estamos repondo o que foi retirado do Estoque, por isso que utilizo o absoluto, a OP não entrou em execução ...*/
    $qtde_baixa_manipulacao = ($opt_opcao == 1) ? 0 : abs($qtde_baixa);

//Busca a Qtde em Estoque do Produto Insumo ...
    $sql = "SELECT `qtde` 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $qtde_estoque   = (count($campos) == 0) ? 0 : $campos[0]['qtde'];
    $estoque_final  = $qtde_estoque + $qtde_baixa_manipulacao;

//Inserindo os Dados no BD ...
    $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$id_produto_insumo', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '$qtde_baixa_manipulacao', '', '$estoque_final', '$hdd_observacao', 'E', 'S', '$data_ocorrencia') ";
    bancos::sql($sql);
    $id_baixa_manipulacao = bancos::id_registro();
    estoque_ic::atualizar($id_produto_insumo, 0);

//Se existir Baixa, então eu estorno esta dessa OP e desse PI no Banco de Dados por isso que utilizo o sinal negativo ...
    $sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `observacao`, `data_sys`, `status`) VALUES (NULL, '$id_produto_insumo', '$id_op', '$id_baixa_manipulacao', '-$qtde_baixa', '$hdd_observacao', '$data_ocorrencia', '3') ";
    bancos::sql($sql);
//Verificação extra - significa que se deseja creditar o aço no Estoque ...
    /*if($opt_opcao == 2) {
//Aqui eu verifico se já existe Estoque desse PI no estoque ...
        $sql = "SELECT id_estoque_insumo 
                FROM `estoques_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Já existe esse PI no estoque, sendo assim crio esse Registro ...
            $id_estoque_insumo = $campos[0]['id_estoque_insumo'];
            $sql = "UPDATE `estoques_insumos` SET `qtde`= `qtde` + '$qtde_baixa' where `id_estoque_insumo` = '$id_estoque_insumo' LIMIT 1 ";
        }else {//Ainda não existe esse PI no estoque, sendo assim crio esse Registro ...
            $sql = "INSERT INTO `estoques_insumos` (`id_estoque_insumo`, `id_produto_insumo`, `qtde`, `data_atualizacao`) values (NULL, '$id_produto_insumo', '$qtde_baixa', '$data_ocorrencia') ";
        }
        bancos::sql($sql);
    }*/
?>
    <Script Language = 'JavaScript'>
        alert('BAIXA DE PI ESTORNADA COM SUCESSO !')
        parent.document.form.passo.value = 2
        parent.document.form.submit()
    </Script>
<?
}
?>
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
function validar() {
	if(document.form.opt_opcao[0].checked == false && document.form.opt_opcao[1].checked == false) {
		alert('SELECIONE UMA OPÇÃO ! ')
		document.form.opt_opcao[0].focus()
		return false
	}
//Estornar Baixa do PI no Estoque ...
	var resposta = confirm('TEM CERTEZA DE QUE DESEJA ESTORNAR A BAIXA DO PI NO ESTOQUE ?')
	if(resposta == false) {
		return false
	}else {
		var observacao = prompt('DIGITE UMA OBSERVAÇÃO P/ ESTORNAR A BAIXA DO PI NO ESTOQUE: ')
	}
	document.form.hdd_observacao.value = observacao
//Controle com a Observação ...
	if(document.form.hdd_observacao.value == '' || document.form.hdd_observacao.value == 'null' || document.form.hdd_observacao.value == 'undefined') {
		alert('OBSERVAÇÃO INVÁLIDA !!!\nDIGITE UMA OBSERVAÇÃO P/ ESTORNAR A BAIXA DO PI NO ESTOQUE !')
		return false
	}
	document.form.cmd_salvar.disabled = true//Desabilita o botão aqui, p/ o user não ficar clicando 1 milhão de vezes
	document.form.submit()
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF;?>" onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
<input type='hidden' name='id_op' value='<?=$id_op;?>'>
<input type='hidden' name='hdd_observacao'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align="center">
        <td>Dar Baixa de PI</td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" id='label'>
            <label for='label'>A OP já foi processada, o PI não será creditado no Estoque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" id='label2'>
            <label for='label2'>A OP não foi processada, creditar o PI no Estoque</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>