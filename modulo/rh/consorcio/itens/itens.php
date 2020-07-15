<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');

$mensagem[1] = 'CONSÓRCIO BLOQUEADO !!! JÁ FOI GERADO VALE PARA ESTE CONSÓRCIO !';
$mensagem[2] = 'FUNCIONÁRIO EXCLUIDO COM SUCESSO !';

if($passo == 1) {
//Busco o id_consorcio p/ passar por parâmetro ...
	$sql = "Select id_consorcio from `consorcios_vs_funcionarios` where `id_consorcio_vs_funcionario` = '$id_consorcio_vs_funcionario' limit 1 ";
	$campos = bancos::sql($sql);
	$id_consorcio = $campos[0]['id_consorcio'];
/*Aqui eu verifico se já foi gerado Vale p/ este Consórcio, caso foi gerado, então eu não posso excluir
nenhum funcionário do Consórcio ...*/
	$sql = "Select gerado_vale 
		from consorcios 
		where `id_consorcio` = '$id_consorcio' limit 1 ";
	$campos = bancos::sql($sql);
	$gerado_vale = $campos[0]['gerado_vale'];
	
	if(strtoupper($gerado_vale) == 'S') {//Não posso estar + excluir funcs pq já foi gerado vale ...
		$valor = 1;
	}else {//Ainda não foi gerado vale, sendo assim posso alterar os dados normalmente ...
//Excluindo o Funcionário do Consórcio ...
		$sql = "Delete from `consorcios_vs_funcionarios` where `id_consorcio_vs_funcionario` = '$id_consorcio_vs_funcionario' limit 1";
		bancos::sql($sql);
		$valor = 2;
	}
?>
	<Script Language = 'Javascript'>
            window.parent.itens.document.location = 'itens.php?id_consorcio=<?=$id_consorcio;?>&valor=<?=$valor;?>'
            window.parent.rodape.document.form.submit()
	</Script>
<?
}else {
//Busca o nome do Grupo p/ exibir na Tela de Itens do Iframe com o id_consorcio ...
	$sql = "Select nome_grupo 
			from consorcios 
			where `id_consorcio` = '$id_consorcio' limit 1 ";
	$campos = bancos::sql($sql);
	$nome_grupo = $campos[0]['nome_grupo'];
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></script>
<Script Language = 'JavaScript'>
function relatorio() {
	nova_janela('')
}

function igualar(indice) {
	var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
	var elemento = '', objeto = ''
	for(i = 0; i < parent.itens.document.form.elements.length; i++) {
		if(parent.itens.document.form.elements[i].type == 'radio') {
			cont ++
		}
	}
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item') {
			existe ++
		}
	}
	if(cont > 1) {
		elemento = parent.itens.document.form.opt_item[indice].value
		objeto = parent.itens.document.form.opt_item[indice]
	}else {
		if(existe == 0) {
			elemento = parent.itens.document.form.opt_item.value
			objeto = parent.itens.document.form.opt_item
		}else {
			elemento = parent.itens.document.form.opt_item[indice].value
			objeto = parent.itens.document.form.opt_item[indice]
		}
	}
	if(objeto.type == 'radio') {
		for(i = 0; i < elemento.length; i ++) {
			if(elemento.charAt(i) == '|') {
				controle ++
			}else {
				if(controle == 1) {
					liberado = liberado + elemento.charAt(i)
				}else {
					codigo = codigo + elemento.charAt(i)
				}
			}
		}
		parent.itens.document.form.opt_item_principal.value = codigo
	}else {
		limpar_radio()
	}
}

function limpar_radio() {
	for(i = 0; i < parent.itens.document.form.elements.length; i++) {
		if(parent.itens.document.form.elements[i].type == 'radio') {
			parent.itens.document.form.elements[i].checked = false
		}
	}
}
</Script>
</head>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center'>
	<tr class="linhacabecalho" align="center">
		<td colspan="4">
			<font color='#FFFFFF' size='-1'>
			Funcionário(s) do Consórcio N.º&nbsp;
			<font color="yellow">
				<?=$id_consorcio;?>
			</font></font>
		</td>
	</tr>
	<tr class="iframe" onClick="showHide('dados_consorcio'); return false"  style="cursor:pointer">
		<td height="21" align="left" colspan="2">
			<font color="blue" size="2">Nome Grupo: </font>
			<?=$nome_grupo;?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<iframe src="../dados_consorcio.php?id_consorcio=<?=$id_consorcio;?>" name="dados_consorcio" id="dados_consorcio" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="78" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<?
//Aqui eu exibo os Funcionários que estão participando do Consórcio ...
	$sql = "Select cf.*, f.nome 
		from `consorcios_vs_funcionarios` cf 
		inner join `funcionarios` f on f.id_funcionario = cf.id_funcionario 
		where id_consorcio = '$id_consorcio' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
//Verifica se tem pelo menos um Funcionário no Consórcio ...
	if($linhas > 0) {
?>
<table width='80%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
	<tr></tr>
	<tr></tr>
	<tr class='linhanormal' align='center'>
		<td bgcolor='#CECECE'><b>Itens</b></td>
		<td bgcolor='#CECECE'><b>Nome</b></td>
		<td bgcolor='#CECECE'><b>Contemplado</b></td>
		<td bgcolor='#CECECE'><b>Valor do Prêmio</b></td>
	</tr>
<?
		for ($i = 0; $i < $linhas; $i++) {
			if($campos[$i]['contemplado'] == 0) {
				$contemplado = 'Não';
				$font = "<font color='black'>";
			}else {
				$contemplado = 'Sim';
				$font = "<font color='green'>";
			}
?>
	<tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<input type='radio' name='opt_item' value="<?=$campos[$i]['id_consorcio_vs_funcionario'];?>" onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')">
		</td>
		<td align='left'>
			<?=$font.$campos[$i]['nome'];?>
		</td>
		<td>
		<?
			echo $font.$contemplado;
//Se existir contemplado, apresento a Data e Hora em que ele foi contemplado ...
			if(substr($campos[$i]['data_contemplado'], 0, 10) != '0000-00-00') {
				echo $font.' às ';
				echo $font.data::datetodata(substr($campos[$i]['data_contemplado'], 0, 10), '/').' '.substr($campos[$i]['data_contemplado'], 11, 8);
			}
		?>
		</td>
		<td>
		<?
			echo $font.number_format($campos[$i]['valor_premio'], 2, ',', '.');
		?>
		</td>
	</tr>
<?
		}
?>
	<tr class='linhadestaque' align='center'>
		<td colspan="4">
			&nbsp;
		</td>
	</tr>
</table>
<br><center>
    <font face='verdana, arial, helvetica, sans-serif' class='confirmacao'>
        Total de Registro(s): <?=$linhas;?>
    </font>
</center>
<!--Não me lembro desses hiddens aki (rsrs)-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_consorcio' value='<?=$id_consorcio;?>'>
</form>
</body>
</html>
<?
            if(!empty($valor)) {
?>
            <Script Language = 'Javascript'>
                    alert('<?=$mensagem[$valor];?>')
            </Script>
<?
            }
	}else {
?>
<html>
<body>
<form name='form'>
<table width='80%' border='0' align='center'>
	<tr class="atencao">
		<td align="center">
			<font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
				<b>Consórcio
				<font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="blue"><?=$id_consorcio;?></font>
				n&atilde;o cont&eacute;m funcionários cadastrados.</b>
			</font>
		</td>
	</tr>
</table>
<input type='hidden' name='id_consorcio' value='<?=$id_consorcio;?>'>
</form>
</body>
</html>
<?
		if(!empty($valor)) {
?>
			<Script Language = 'JavaScript'>
				alert('<?=$mensagem[$valor];?>')
			</Script>
<?
		}
	}
}
?>