<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
require('../../../lib/genericas.php');
require('../../../lib/vendas.php');
//Essa biblioteca não é usada aqui, mas dentro do arquivo visualizar_estoque que está no fim da página ...
session_start('funcionarios');
//Significa que foi acessado do Mód. de Compras, sendo assim eu posso verificar a Sessão normalmente ...
if($atualizar_iframe != 1) {
    segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../');
}
$mensagem[1] = "<font class='confirmacao'>PRAZO DE ENTREGA ALTERADO COM SUCESSO.</font>";

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
//Busca de alguns dados p/ passar por e-mail mais abaixo ...
    $sql = "SELECT `prazo_entrega` 
            FROM `estoques_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $produto_acabado        = intermodular::pa_discriminacao($id_produto_acabado, 0);
    $prazo_entrega_antigo   = strtok($campos[0]['prazo_entrega'], '=');
    $prazo_entrega_novo     = $txt_prazo_entrega;
/**********************************Atualizando os Campos na Base de Dados**********************************/
//Aki registra a Data e Hora em q foi feita a alteração
    $data_sys = date('Y-m-d H:i:s');
//Se não há prazo de entrega digitado ...
    if(empty($txt_prazo_entrega)) $txt_prazo_entrega = ' ';
//Verifica quem é o responsável pela alteração do prazo de entrega
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login = $campos[0]['login'];
//Junta o que o usuário digitou com o login do responsável que está fazendo a manipulação
    $txt_prazo_entrega.= '=> '.$login.' | '.$data_sys;
    $sql = "Update estoques_acabados set `prazo_entrega` = '$txt_prazo_entrega', `data_atualizacao_prazo_ent` = '$data_sys' where id_produto_acabado = '$id_produto_acabado' limit 1 ";
    bancos::sql($sql);
/**********************************************************************************************************/
/*Se esse checkbox estiver habilitado, então eu envio e-mail informando aos usuários os 
novos prazos das OP(s) ...*/
    if($chkt_enviar_email == 1) {
/************************E-mail************************/
/***Todo esse procedimento é para verificar se existem importações atreladas***/
//Aqui eu verifico se o PA é um PI também ...
        $sql = "SELECT id_produto_insumo 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_produto_insumo  = $campos[0]['id_produto_insumo'];
//Aqui eu verifico as Importações que estão pendentes desse PI, só trago duas p/ passar no e-mail ...
        $sql = "SELECT i.nome 
                FROM `itens_pedidos` ip 
                INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido AND (p.status = '1' OR p.status = '2') AND p.ativo = '1' 
                INNER JOIN `importacoes` i ON i.id_importacao = p.id_importacao 
                WHERE ip.`id_produto_insumo` = '$id_produto_insumo' 
                AND ip.`status` < '2' ORDER BY p.prazo_entrega LIMIT 2 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrar ...
            $importacoes = ' <font color="blue"><b> (Constam Importações)</b></font>';
            $importacoes_rotulo = ' <font color="blue"><b> (Importação)</b></font>';
        }
//Dados p/ enviar por e-mail ...
        $complemento_justificativa.= '<br><b>Produto</b>'.$produto_acabado.$importacoes.' <br><b>Prazo de Entrega Antigo</b>'.$importacoes_rotulo.': '.$prazo_entrega_antigo.' <br><b>Novo Prazo de Entrega</b>'.$importacoes_rotulo.': '.$prazo_entrega_novo.'<br>';
/******************************************************************************/
/*Se o Usuário estiver alterando a Conta à Receber do Financeiro, então o Sistema dispara um e-mail 
informando qual a Conta à Receber que está sendo alterada ...
//Aqui eu trago alguns dados de Conta à Receber p/ passar por e-mail via parâmetro ...
//Aqui eu busco o login de quem está alterando a Conta à Receber ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_alterando    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $txt_justificativa_email.= $complemento_justificativa.'<br><b>Login: </b>'.$login_alterando.' - <b>Data e Hora de Alteração: </b> '.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$txt_justificativa.'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
        $destino = $alterar_prazo_entrega_op;
        $mensagem_email = $txt_justificativa_email;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Novo Prazo de Entrega de OP - Revenda', $mensagem_email);
    }
    $valor = 1;
}

$sql = "Select referencia, discriminacao 
	from produtos_acabados 
	where id_produto_acabado = '$id_produto_acabado' limit 1 ";
$campos = bancos::sql($sql);
$referencia = $campos[0]['referencia'];
$discriminacao = $campos[0]['discriminacao'];

$sql = "Select prazo_entrega, data_atualizacao_prazo_ent 
	from estoques_acabados 
	where id_produto_acabado = '$id_produto_acabado' limit 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {
	$prazo_entrega = '';
	$responsavel = '';
	$data = '';
	$hora = '';
	$data_atualizacao_prazo_ent = '';
}else {
	$prazo_entrega = strtok($campos[0]['prazo_entrega'], '=');
	$prazo_entrega = trim($prazo_entrega);
	$responsavel = strtok($campos[0]['prazo_entrega'], '|');
	$responsavel = substr(strchr($responsavel, '> '), 1, strlen($responsavel));

	$data_hora = strchr($campos[0]['prazo_entrega'], '|');
	$data_hora = substr($data_hora, 2, strlen($data_hora));
	$data = data::datetodata(substr($data_hora, 0, 10), '/');
	$hora = substr($data_hora, 11, 8);

	$data_atualizacao_prazo_ent = data::datetodata(substr($campos[0]['data_atualizacao_prazo_ent'], 0, 10), '/').' '.substr($campos[0]['data_atualizacao_prazo_ent'], 11, 8);
//Faz esse tratamento para o caso de não encontrar o responsável
	if(empty($responsavel)) {
		$string_apresentar = '&nbsp;';
	}else {
		$string_apresentar = $responsavel.' - '.$data.' '.$hora.'<br><b>Data de Atualização: </b>'.$data_atualizacao_prazo_ent;
	}
}
?>
<html>
<title>.:: Alterar Prazo de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript'>
function validar() {
	if(!texto('form', 'txt_prazo_entrega', '1', '1234567890,.=-+*<>/abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZãõÃÕáéíóúÁÉÍÓÚçÇ!_ ', 'PRAZO DE ENTREGA', '2')) {
		return false
	}
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function fecha_e_atualiza() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.getElementById('nao_atualizar').value == 0) {
/*Tenho que fazer esses controles de variáveis porque existem outras Telas que puxam esse arquivo e daí
pode dar problema se não tratar corretamente desses parâmetros*/
		var atualizar_iframe = '<?=$atualizar_iframe;?>'
//Significa que tem que atualizar o Iframe da Tela de Consultar Estoque ...
		if(atualizar_iframe == 1) {
			window.opener.document.location = '../produtos_acabados/prazo_entrega.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$operacao_custo;?>&operacao_custo_sub=<?=$operacao_custo_sub;?>'
		}else {
//Controle para atualização da tela de baixo, caso seja frame ou uma tela normal
			var tela1 = '<?=$tela1;?>'
			var tela2 = '<?=$tela2;?>'
//Se existir esse parâmetro - que com certeza sempre terá
			if(tela1 != '') {
				tela1 = eval(tela1)
				if(typeof(tela1) == 'object') {
					tela1.document.form.submit()
				}
			}
//Se existir esse parâmetro - nem sempre terá
			if(tela2 != '') {
				tela2 = eval(tela2)
				if(typeof(tela2) == 'object') {
					tela2.document.form.submit()
				}
			}
		}
	}
	window.close()
}

function controlar_justificativa() {
    if(document.form.chkt_enviar_email.checked == true) {//Se tiver checado ...
//Habilita a Justificativa
        document.form.txt_justificativa.disabled = false
//Aqui joga o Designer de Habilitado
        document.form.txt_justificativa.className = 'caixadetexto'
    }else {//Se não tiver checado
//Desabilita a Justificativa
        document.form.txt_justificativa.disabled = true
//Aqui joga o Designer de Desabilitado
        document.form.txt_justificativa.className = 'textdisabled'
    }
}
</Script>
</head>
<?
    if($valor == 1) {//Quando o usuário acabou de atualizar o Prazo, o Sistema fecha a Tela automaticamente ...
        $onload = 'top.fecha_e_atualiza();top.fecha_e_atualiza()';//Chamo a função 2 vezes porque chamando uma vez só não fecha ???
    }else {
        $onload = 'document.form.txt_prazo_entrega.focus()';
    }
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='' onSubmit="return validar()">
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='operacao_custo' value='<?=$operacao_custo;?>'>
<input type='hidden' name='atualizar_iframe' value='<?=$atualizar_iframe;?>'>
<!--Parâmetros que servem para estar atualizando a tela de baixo-->
<input type='hidden' name='tela1' value='<?=$tela1;?>'>
<input type='hidden' name='tela2' value='<?=$tela2;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar' id='nao_atualizar'>
<!--************************************************************-->
<table border="0" width='80%' align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Alterar Prazo de Entrega
        </td>
    </tr>
    <tr class="linhadestaque">
        <td colspan="2">
            <font color='yellow' size='-1'>
                <b>Ref: </b>
                <font color='#FFFFFF'>
                    <?=$referencia;?>
                </font>
            </font>-
            <font color='yellow' size='-1'>
                <b>Discriminação: </b>
                <font color='#FFFFFF'>
                    <?=$discriminacao;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Prazo de Entrega: </b>
            <input type='text' name='txt_prazo_entrega' value='<?=$prazo_entrega;?>' title='Prazo de Entrega' size='45' maxlength='255' class='caixadetexto'>
        </td>
        <td>
            <b>Responsável: </b><?=$string_apresentar;?>
        </td>
    </tr>
	<tr class="linhanormal" valign="center">
		<td colspan="2">
			<b>Justificativa:</b>
			<textarea name='txt_justificativa' cols='60' rows='4' maxlength='255' class='caixadetexto'></textarea>
			&nbsp;
			<input type="checkbox" name="chkt_enviar_email" value="1" title="Enviar E-mail" id="enviar_email" onclick="controlar_justificativa()" class="checkbox" checked>
			<label for="enviar_email">Enviar E-mail</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
		<?
/*Quando for acessado pela tela de Gerenciar ou Consultar estoque, com esse parâmetro eu nem exibo
esse botões p/ que não venha sobrecarregar a Tela com Tanto Botão*/
			if($atualizar_iframe == 1) {
				echo '&nbsp;';
			}else {
		?>
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red"  class="botao">
			<input type="button" name="cmd_fechar_atualizar" value="Fechar e Atualizar" title="Fechar e Atualizar" onclick="fecha_e_atualiza()" style="color:black" class="botao">
		<?
			}
		?>
		</td>
	</tr>
</table>
<table border='0' width='100%' align="center" cellspacing ='1' cellpadding='1'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2">
            <iframe src="compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>&atualizar_iframe=<?=$atualizar_iframe;?>" marginwidth="0" marginheight="0" frameborder="0" height="300" width="100%" scrolling="auto"></iframe>
        </td>
    </tr>
<?
//Aqui nessa parte eu chamo a função referente ao Estoque do P.A.
    $nao_chamar_biblioteca  = 1;
    $nivel_reduzido         = 1;
    require('visualizar_estoque.php');
?>
</table>
</form>
</body>
</html>