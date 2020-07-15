<?
require('../../../../../lib/data.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>CONFER�NCIA DE ENTREGA DO PI REALIZADA COM SUCESSO.</font>";

if($passo == 1) {
    $data_atual = date('d/m/Y H:i:s');
    //Busco o login de que est� fazendo a Altera��o dos Itens da Nota ...
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login 	= $campos[0]['login'];

    $responsavel_medidas = '�ltima altera��o: '.$login.' - '.$data_atual;
    for ($i = 0; $i < count($chkt_nfe_historico); $i++) {
        $sql = "UPDATE `nfe_historicos` SET `medida1_mm` = '$txt_medida1_mm[$i]', `medida2_mm` = '$txt_medida2_mm[$i]', `qtde_metros` = '$txt_qtde_metros[$i]', `peso_2_porc` = '$txt_peso_2[$i]', `num_corrida` = '$txt_num_corrida[$i]', `responsavel_medidas` = '$responsavel_medidas' WHERE `id_nfe_historico` = '$chkt_nfe_historico[$i]' LIMIT 1 ";
        bancos::sql($sql);
    }

//Atualizo a Observa��o de Confer�ncia ...
    $sql = "UPDATE `nfe` SET `obs_conf_pi` = '$txt_obs_conf_pi' WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'conferencia_entrega_pi.php?id_nfe=<?=$id_nfe;?>&valor=1'
    </Script>
<?
}else {
//Busca o nome do Fornecedor com + detalhes alguns detalhes de dados da Nota Fiscal
	$sql = "SELECT f.razaosocial, nfe.num_nota, nfe.tipo, nfe.obs_conf_pi, nfe.situacao 
                FROM `nfe` 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
	$campos         = bancos::sql($sql);
	$razao_social 	= $campos[0]['razaosocial'];
	$num_nota       = $campos[0]['num_nota'];
	//Tratamento para o Tipo de Nota
	$tipo           = ($campos[0]['tipo'] == 1) ? 'NF' : 'SGD';
	$obs_conf_pi 	= $campos[0]['obs_conf_pi'];
	$situacao       = $campos[0]['situacao'];//Situa��o da Nota Fiscal
//Busca todos os Itens da NF em que os Itens s�o do Tipo A�O para fazer a Confer�ncia
	$sql = "SELECT ip.qtde, pia.geometria_aco, nfeh.id_nfe_historico, nfeh.id_nfe, nfeh.id_item_pedido, nfeh.qtde_entregue, nfeh.valor_entregue, IF(nfeh.medida1_mm = '0.00', pia.bitola1_aco, nfeh.medida1_mm) AS medida1_mm, IF(nfeh.medida2_mm = '0.00', pia.bitola2_aco, nfeh.medida2_mm) AS medida2_mm, nfeh.qtde_metros, nfeh.num_corrida, nfeh.responsavel_medidas, qa.nome, qa.valor_perc 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `itens_pedidos` ip ON ip.id_item_pedido = nfeh.id_item_pedido 
                INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = pi.id_produto_insumo 
                INNER JOIN `qualidades_acos` qa ON qa.id_qualidade_aco = pia.id_qualidade_aco 
                WHERE nfeh.`id_nfe` = '$id_nfe' ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
?>
<html>
<head>
<title>.:: Confer�ncia de Entrega do PI ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = 'conferencia_entrega_pi.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var mensagem = '', valor = false
	var elementos 	= document.form.elements
	for (var i = 0; i < elementos.length; i++) {
		if (elementos[i].type == 'checkbox') {
			if (elementos[i].checked == true) valor = true
		}
	}
	if(valor == false) {
		window.alert('SELECIONE UMA OP��O !')
		return false
	}else {
		if(typeof(elementos['chkt_nfe_historico[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 �nico elemento ...
		}else {
			var linhas = (elementos['chkt_nfe_historico[]'].length)
		}
		for (i = 0; i < linhas; i++) {
			//Se tiver 1 checkbox selecionado
			if(document.getElementById('chkt_nfe_historico'+i).checked == true) {
				//Medida 1 - Tem que ter pelo menos essa medida digitada
				if(document.getElementById('txt_medida1_mm'+i).value == '') {
					alert('DIGITE A MEDIDADE 1 !')
					document.getElementById('txt_medida1_mm'+i).focus()
					return false
				}
				//Quantidade em Metros
				if(document.getElementById('txt_qtde_metros'+i).value == '') {
					alert('DIGITE A QTDE EM METROS !')
					document.getElementById('txt_qtde_metros'+i).focus()
					return false
				}
			}
		}
//Prepara no formato moeda antes de submeter para o BD
		for (i = 0; i < linhas; i++) {
			if(document.getElementById('chkt_nfe_historico'+i).checked == true) {
				document.getElementById('txt_medida1_mm'+i).value 	= strtofloat(document.getElementById('txt_medida1_mm'+i).value)
				document.getElementById('txt_medida2_mm'+i).value 	= strtofloat(document.getElementById('txt_medida2_mm'+i).value)
				document.getElementById('txt_qtde_metros'+i).value 	= strtofloat(document.getElementById('txt_qtde_metros'+i).value)
				document.getElementById('txt_peso_2'+i).value 		= strtofloat(document.getElementById('txt_peso_2'+i).value)
				
				document.getElementById('txt_medida1_mm'+i).disabled	= false
				document.getElementById('txt_medida2_mm'+i).disabled	= false
				document.getElementById('txt_qtde_metros'+i).disabled	= false
				document.getElementById('txt_peso_2'+i).disabled		= false
			}
		}
	}
}

function controlar_cor(indice) {
/************Controle para a Troca de Cores************/
//Compara��o de Valores
	var qtde_nota 		= eval(strtofloat(document.getElementById('txt_qtde_nota'+indice).value))
	var peso_2_adm_kg 	= eval(strtofloat(document.getElementById('txt_peso_2'+indice).value))
//Enquanto o Peso + 2% Adm. Kg For Menor do Valor da Nota, est� mant�m a caixa na cor Vermelha
	if(peso_2_adm_kg < qtde_nota) {
		document.getElementById('txt_qtde_nota'+indice).style.background = 'red'
		document.getElementById('txt_qtde_nota'+indice).style.color = 'white'
	}else {//Se = ou maior ent�o ...
		document.getElementById('txt_qtde_nota'+indice).style.background = '#FFFFE1'
		document.getElementById('txt_qtde_nota'+indice).style.color = 'gray'
	}
/******************************************************/
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<input type='hidden' name='nao_exibir_voltar' value='<?=$nao_exibir_voltar;?>'>
<table width='98%' border='0' cellspacing='1' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Confer�ncia de Entrega do PI
            <font color='yellow'>N.� </font><?=$num_nota.' / '.$tipo;?>
            <font color='yellow'>Fornecedor: </font><?=$razao_social;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Respons�vel pela �ltima Altera��o' style='cursor:help'>
                Resp
            </font>
        </td>
        <td>
            A&ccedil;o
        </td>
        <td>
            <font title='Geometria A�o' style='cursor:help'>
                G.A
            </font>
        </td>
        <td>
            Medida 1 <br/>MM
        </td>
        <td>
            Medida 2 <br/>MM
        </td>
        <td>
            Qtde <br/>Mts.
        </td>
        <td>
            Dens <br/>Kg/M
        </td>
        <td>
            Peso <br/>Cor. Kg
        </td>
        <td>
            Peso + 2% <br/>Adm. Kg
        </td>
        <td>
            Qtde NF
        </td>
        <td>
            N.� Corrida
        </td>
    </tr>
<?
	for ($i = 0;  $i < $linhas; $i++) {
		$qtde_pedida = $campos[$i]['qtde'];

		$sql = "SELECT SUM(qtde_entregue) AS total_entregue 
				FROM `nfe_historicos` 
				WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
		$campos_total_entregue 	= bancos::sql($sql);
		$total_entregue 		= $campos_total_entregue[0]['total_entregue'];
		$restante_entregar 		= $qtde_pedida - $total_entregue;
?>
	<tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<input type='checkbox' name='chkt_nfe_historico[]' id='chkt_nfe_historico<?=$i;?>' value="<?=$campos[$i]['id_nfe_historico'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
		</td>
		<?
//Busca de alguns dados do PI
			$sql = "SELECT g.referencia, pi.id_produto_insumo, pi.discriminacao 
					FROM `itens_pedidos` ip 
					INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
					INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
					WHERE ip.`id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
			$campos_itens_pedidos 	= bancos::sql($sql);
			$id_produto_insumo 		= $campos_itens_pedidos[0]['id_produto_insumo'];
		?>
		<td align='left'>
		<?
			echo genericas::buscar_referencia($id_produto_insumo, $campos_itens_pedidos[0]['referencia']).' * ';
			echo $campos_itens_pedidos[0]['discriminacao'];
		?>
		</td>
		<td>
		<?
			if(!empty($campos[$i]['responsavel_medidas'])) echo "<img width='28' height='23' title='".$campos[$i]['responsavel_medidas']."' src='../../../../../imagem/olho.jpg'>";
		?>
		</td>
		<?
			$densidade 		= compras_new::calcular_densidade($id_produto_insumo);
			$qtde_metros 	= $campos[$i]['qtde_metros'];
			$peso_correto 	= $qtde_metros * $densidade;
			$peso_2 		= $peso_correto * 1.02;
		?>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
			<?=$campos[$i]['geometria_aco'];?>
		</td>
		<td>
			<input type='text' name='txt_medida1_mm[]' id='txt_medida1_mm<?=$i;?>' value="<?=number_format($campos[$i]['medida1_mm'], 2, ',', '.');?>" size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event); if(this.value == '-') this.value = ''; calcular('form', '<?=$i;?>', '<?=$campos[$i]['valor_perc'];?>', '<?=$campos[$i]['geometria_aco'];?>');controlar_cor('<?=$i;?>')" align='right' class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_medida2_mm[]' id='txt_medida2_mm<?=$i;?>' value="<?=number_format($campos[$i]['medida2_mm'], 2, ',', '.');?>" size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event); if(this.value == '-') this.value = ''; calcular('form', '<?=$i;?>', '<?=$campos[$i]['valor_perc'];?>', '<?=$campos[$i]['geometria_aco'];?>');controlar_cor('<?=$i;?>')" align='right' class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_qtde_metros[]' id='txt_qtde_metros<?=$i;?>' value="<?=number_format($qtde_metros, 3, ',', '.');?>" size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '3', '1', event); if(this.value == '-') this.value = ''; calcular('form', '<?=$i;?>', '<?=$campos[$i]['valor_perc'];?>', '<?=$campos[$i]['geometria_aco'];?>');controlar_cor('<?=$i;?>')" align='right' class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_densidade[]' id='txt_densidade<?=$i;?>' value="<?=number_format($densidade, 3, ',', '.');?>" size='8' align='right' class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_peso_correto_kg[]' id='txt_peso_correto_kg<?=$i;?>' value="<?=number_format($peso_correto, 3, ',', '.');?>" size='8' align='right' class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_peso_2[]' id='txt_peso_2<?=$i;?>' value="<?=number_format($peso_2, 2, ',', '.');?>" size='8' align='right' class='textdisabled' disabled>
		</td>
		<?
/************Controle para a Troca de Cores************/
//Compara��o de Valores
//Enquanto o Peso + 2% Adm. Kg For Menor do Valor da Nota, est� mant�m a caixa na cor Vermelha
			if($peso_2 < $campos[$i]['qtde_entregue']) {
				$backcolor = 'background:red';
				$color = 'color:white';
			}else {//Se = ou maior ent�o ...
				$backcolor = 'background:#FFFFE1';
				$color = 'color:gray';
			}
/******************************************************/
		?>
		<td>
			<input type='text' name='txt_qtde_nota[]' id='txt_qtde_nota<?=$i;?>' value="<?=number_format($campos[$i]['qtde_entregue'], 2, ',', '.');?>" size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event); if(this.value == '-') this.value = ''" align='right' style="<?=$backcolor.';'.$color;?>" class='textdisabled' disabled>
		</td>
		<td>
			<input type='text' name='txt_num_corrida[]' id='txt_num_corrida<?=$i;?>' value="<?=$campos[$i]['num_corrida'];?>" size='19' maxlength='15' align='right' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhanormal">
		<td bgcolor="#E8E8E8">Observa��o:</td>
		<td colspan='12' bgcolor="#E8E8E8">
			<textarea name='txt_obs_conf_pi' cols='107' rows='3' maxlength='255' class='caixadetexto'><?=$obs_conf_pi;?></textarea>
		</td>
	</tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
<?
/*Eu tenho esse controle porque essa Tela � chamada de outros locais, e nem sempre conv�m que apare�a
esse bot�o de << Voltar <<*/
	if($nao_exibir_voltar == 1) {
/*Da� substitui o bot�o com esse hidden p/ n�o dar problema com o JavaScript, por causa de contagem 
errada de objetos ...*/
?>
            <input type='hidden' name='cmd_voltar'>
<?
	}else {
?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
<?
	}
?>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="window.print()" class='botao'>
            <?
//Se a Nota Fiscal estiver fechada, ent�o n�o posso + fazer modifica��es referentes as Confer�ncias de Entr.
                if($situacao == 2) {
                    $disabled_botao = 'disabled';
                    $class_botao    = 'textdisabled';
                    $aviso          = 1;
                }else {
                    $disabled_botao = '';
                    $class_botao    = 'botao';
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class_botao;?>' <?=$disabled_botao;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
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
//Se a Nota Fiscal, estiver liberada, ent�o eu dou essa mensagem comunicando o usu�rio ...
	if($situacao == 2) {
?>
<pre>
<b><font color="red">Observa��o:</font></b>
<pre>

* EST� NOTA FISCAL EST� LIBERADA ! PORTANTO N�O SE PODE MAIS ALTERAR AS CONFER�NCIAS DE ENTREGA !!!
</pre>
<?
	}
?>
