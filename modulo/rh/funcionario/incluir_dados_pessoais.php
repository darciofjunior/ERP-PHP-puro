<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/cascates.php');
require('../../../lib/variaveis/dp.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/incluir.php', '../../../');

if($passo == 1) {
/****************************CÛdigo p/ Buscar Id********************************/
//Busca o id_uf atravÈs do campo Estado
	$sql = "SELECT id_uf 
                FROM `ufs` 
                WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
	$campos_uf 	= bancos::sql($sql);
	$id_uf 		= $campos_uf[0]['id_uf'];
/*******************************************************************************/
	//Aqui eu verifico se o funcion·rio que acabou de ser cadastrado j· existe no BD e n„o est· como "Demitido" ...
	$sql = "SELECT id_funcionario 
                FROM `funcionarios` 
                WHERE `cpf` = '$_POST[txt_cpf]' 
                AND `status` < '3' LIMIT 1 ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas == 0) {
		require('../../../lib/mda.php');
//Fazendo Upload da Imagem para o Servidor ...
		switch ($txt_foto_type) {
			case 'image/gif':
				$foto = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $txt_foto, $txt_foto_name, $txt_foto_size, $txt_foto_type, '2');
			break;
			case 'image/pjpeg':
				$foto = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $txt_foto, $txt_foto_name, $txt_foto_size, $txt_foto_type, '2');
			break;
			case 'image/jpeg':
				$foto = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $txt_foto, $txt_foto_name, $txt_foto_size, $txt_foto_type, '2');
			break;
			case 'image/x-png':
				$foto = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $txt_foto, $txt_foto_name, $txt_foto_size, $txt_foto_type, '2');
			break;
			case 'image/bmp':
				$foto = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $txt_foto, $txt_foto_name, $txt_foto_size, $txt_foto_type, '2');
			break;
			default:
				//echo "N„o È possivel copiar a imagem";
			break;
		}
		$data_sys = date('Y-m-d H:i:s');
//Tratamento com os campos de Data p/ poder gravar no Banco de Dados ...
		$data_emissao       = data::datatodate($_POST['txt_data_emissao'], '-');
		$data_nascimento    = data::datatodate($_POST['txt_data_nascimento'], '-');
/******************************************************************************************/
                $sql = "INSERT INTO `funcionarios` (`id_funcionario`, `nome`, `cpf`, `rg`, `data_emissao`, `orgao_expedidor`, `carteira_profissional`, `serie_profissional`, `pis`, `titulo_eleitor`, `habilitacao`, `cod_categoria`, `endereco`, `complemento`, `bairro`, `cep`, `cidade`, `id_uf`, `telefone_residencial`, `telefone_celular`, `email_particular`, `cod_academico`, `cod_civil`, `id_nacionalidade`, `data_nascimento`, `sexo`, `cod_sangue`, `ddd_residencial`, `ddd_celular`, `id_pais`, `naturalidade`, `descricao`, `numero`, `data_registro`, `path_foto`) VALUES (NULL, '$_POST[txt_nome]', '$_POST[txt_cpf]', '$_POST[txt_rg]', '$data_emissao', '$_POST[txt_orgao_expedidor]', '$_POST[txt_carteira]', '$_POST[txt_serie]', '$_POST[txt_pis]', '$_POST[txt_titulo]', '$_POST[txt_habilitacao]', '$_POST[cmb_categoria]', '$_POST[txt_endereco]', '$_POST[txt_complemento]', '$_POST[txt_bairro]', '$_POST[txt_cep]', '$_POST[txt_cidade]', '$id_uf', '$_POST[txt_telefone_residencial]', '$_POST[txt_telefone_celular]', '$_POST[txt_email_particular]', '$_POST[cmb_nivel_academico]', '$_POST[cmb_estado_civil]', '$_POST[cmb_nacionalidade]', '$data_nascimento', '$rad_sexo', '$_POST[cmb_tipo_sangue]', '$_POST[txt_ddd_residencial]', '$_POST[txt_ddd_celular]', '$_POST[cmb_pais]', '$_POST[txt_naturalidade]', '$_POST[txt_descricao]', '$_POST[txt_numero]', '$data_sys', '$foto') ";
		bancos::sql($sql);
                //Coloquei esse nome de $id_funcionario_loop, p/ n„o dar conflito com a vari·vel "id_funcion·rio" da sess„o
		$id_funcionario_loop = bancos::id_registro();
?>
	<Script Language = 'JavaScript'>
            alert('FUNCION¡RIO INCLUIDO COM SUCESSO !')
            //Vai estar acessando a Tela de Alterar Dados Profissionais diretamente por causa desse par‚metro de tela = 2
            window.parent.location = 'alterar2.php?passo=1&id_funcionario_loop=<?=$id_funcionario_loop;?>&tela=2'
	</Script>
<?
//Funcion·rio j· existente no cadastro ...
	}else {
?>
	<Script Language = 'JavaScript'>
            alert('FUNCION¡RIO J¡ EXISTENTE !')
            window.location = 'incluir_dados_pessoais.php'
	</Script>
<?
	}
}else {
?>
<html>
<head>
<title>.:: Dados Pessoais ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
//Habilita a Unidade Federal
function pais_abilita() {
	if (document.form.cmb_pais.value == '31') {
//Trocando a Cor da Letra para Desabilitado
		document.form.txt_endereco.style.color = 'gray'
		document.form.txt_bairro.style.color = 'gray'
		document.form.txt_cidade.style.color = 'gray'
		document.form.txt_estado.style.color = 'gray'
//Trocando a Cor do Fundo para Desabilitado
		document.form.txt_endereco.style.background = '#FFFFE1'
		document.form.txt_bairro.style.background = '#FFFFE1'
		document.form.txt_cidade.style.background = '#FFFFE1'
		document.form.txt_estado.style.background = '#FFFFE1'
//Desabilitando
		document.form.txt_endereco.disabled = true
		document.form.txt_bairro.disabled = true
		document.form.txt_cidade.disabled = true
		document.form.txt_estado.disabled = true
//Cep
		document.form.txt_cep.focus()
	}else {
//Trocando a Cor da Letra para Habilitado
		document.form.txt_endereco.style.color = 'Brown'
		document.form.txt_bairro.style.color = 'Brown'
		document.form.txt_cidade.style.color = 'Brown'
		document.form.txt_estado.style.color = 'Brown'
//Trocando a Cor do Fundo para Habilitado
		document.form.txt_endereco.style.background = '#FFFFFF'
		document.form.txt_bairro.style.background = '#FFFFFF'
		document.form.txt_cidade.style.background = '#FFFFFF'
		document.form.txt_estado.style.background = '#FFFFFF'
//Habilitando
		document.form.txt_endereco.disabled = false
		document.form.txt_bairro.disabled = false
		document.form.txt_cidade.disabled = false
		document.form.txt_estado.disabled = false
//Estado
		document.form.txt_endereco.focus()
	}
//Limpando os Dados
	document.form.txt_cep.value = ''
	document.form.txt_endereco.value = ''
	document.form.txt_numero.value = ''
	document.form.txt_complemento.value = ''
	document.form.txt_bairro.value = ''
	document.form.txt_cidade.value = ''
	document.form.txt_estado.value = ''
}

function validar() {
//Nome
	if(!texto('form', 'txt_nome', '3', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’ ', 'NOME', '2')) {
		return false
	}
//Nacionalidade
	if(!combo('form', 'cmb_nacionalidade', '', 'SELECIONE A NACIONALIDADE !')) {
		return false
	}
//Estado Civil
	if(!combo('form', 'cmb_estado_civil', '', 'SELECIONE O ESTADO CIVIL !')) {
		return false
	}
//Naturalidade
	if(!texto('form', 'txt_naturalidade', '3', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’ ', 'NATURALIDADE', '1')) {
		return false
	}
//NÌvel AcadÍmico
	if(!combo('form', 'cmb_nivel_academico', '', 'SELECIONE O NÕVEL ACAD MICO !')) {
		return false
	}
//Data de Nascimento
	if(!data('form', 'txt_data_nascimento', '4000', 'NASCIMENTO')) {
		return false
	}
//RG
	if(!texto('form', 'txt_rg', '3', '-.0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ', 'RG', '2')) {
		return false
	}
//”rg„o
	if(document.form.txt_orgao_expedidor.value != '') {
		if(!texto('form', 'txt_orgao_expedidor', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ- ', '”RG√O EXPEDIDOR', '2')) {
			return false
		}
	}
//Data de Emiss„o
	if(document.form.txt_data_emissao.value != '') {
		if(!data('form', 'txt_data_emissao', "4000", 'EMISS√O')) {
			return false
		}
	}
//Se o PaÌs for Brasil, ent„o forÁa o preenchimento de CEP
	if(document.form.cmb_pais.value == 31) {
//Cep
		if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
			return false
		}
//N˙mero
		if(!texto('form', 'txt_numero', '1', '0123456789', 'N⁄MERO', '2')) {
			return false
		}
//Complemento
		if(document.form.txt_complemento.value != '') {
			if(!texto('form', 'txt_complemento', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-. ', 'COMPLEMENTO', '2')) {
				return false
			}
		}
//PaÌs Internacional
	}else {
//EndereÁo
		if(!texto('form', 'txt_endereco', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'ENDERE«O', '2')) {
			return false
		}
//N˙mero
		if(!texto('form', 'txt_numero', '1', '0123456789', 'N⁄MERO', '2')) {
			return false
		}
//Complemento
		if(document.form.txt_complemento.value != '') {
			if(!texto('form', 'txt_complemento', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-. ', 'COMPLEMENTO', '2')) {
				return false
			}
		}
//Bairro
		if(document.form.txt_bairro.value != '') {
			if(!texto('form', 'txt_bairro', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'BAIRRO', '2')) {
				return false
			}
		}
//Cidade
		if(document.form.txt_cidade.value != '') {
			if(!texto('form', 'txt_cidade', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CIDADE', '1')) {
				return false
			}
		}
//Estado
		if(document.form.txt_estado.value != '') {
			if(!texto('form', 'txt_estado', '2', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'ESTADO', '2')) {
				return false
			}
		}
	}
//DDD Residencial
	if(document.form.txt_ddd_residencial.value != '') {
		if(!texto('form', 'txt_ddd_residencial', '1', '1234567890', 'DDD RESIDENCIAL', '2')) {
			return false
		}
	}
//Telefone Residencial
	if(!texto('form', 'txt_telefone_residencial', '7', '1234567890', 'TELEFONE RESIDENCIAL', '2')) {
		return false
	}
//DDD Celular
	if(document.form.txt_ddd_celular.value != '') {
		if(!texto('form', 'txt_ddd_celular', '1', '1234567890', 'DDD CELULAR', '2')) {
			return false
		}
	}
//Telefone Celular
	if(document.form.txt_telefone_celular.value != '') {
		if(!texto('form', 'txt_telefone_celular', '7', '1234567890', 'TELEFONE CELULAR', '2')) {
			return false
		}
	}
//E-mail Particular
	if(document.form.txt_email_particular.value != '') {
		if (!new_email('form', 'txt_email_particular')) {
			return false
		}
	}
//Aqui serve para n„o submeter
	if(document.form.controle.value == 0) {
		return false
	}
//Desabilita para poder gravar no Banco de Dados
//EndereÁo
	document.form.txt_endereco.disabled = false
	document.form.txt_bairro.disabled = false
	document.form.txt_cidade.disabled = false
	document.form.txt_estado.disabled = false
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
	document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
	document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
	document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
}

function atualizar_cep() {
	var id_pais = document.form.cmb_pais.value
	var txt_cep = document.form.txt_cep.value
	if(txt_cep == '') {//… v·zio
            document.form.txt_endereco.value    = ''
            document.form.txt_bairro.value      = ''
            document.form.txt_cidade.value      = ''
            document.form.txt_estado.value      = ''
	}else {//N„o È v·zio
//Verifica se o CEP È v·lido
            if(txt_cep.length < 9) {
                alert('CEP INV¡LIDO !')
                document.form.txt_cep.focus()
                document.form.txt_cep.select()
                return false
            }
            if(id_pais == 31) {//SÛ buscar· o CEP se for Brasil
                window.parent.parent.cep.location = 'buscar_cep.php?txt_cep='+txt_cep
            }
	}
}

//FunÁ„o que controla para n„o submeter
function controlar(valor) {
	document.form.controle.value = valor
}
</Script>
</head>
<body onload='pais_abilita();document.form.txt_nome.focus()'>
<form name='form' method='post' onsubmit='return validar()' action='<?=$PHP_SELF.'?passo=1'?>' enctype="multipart/form-data">
<!--Coloquei esse nome de $id_funcionario_loop, p/ n„o dar conflito com a vari·vel "id_funcion·rio" da sess„o-->
<input type="hidden" name="id_funcionario_loop" value="<?=$id_funcionario_loop;?>">
<!--Caixa que faz controle para submeter a tela de Cliente-->
<input type='hidden' name="controle" value="1">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FFFFFF'>
                            Dados Pessoais
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
            <td width='40%'><b>Nome:</b></td>
            <td width='40%'><b>Sexo:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_nome' class="caixadetexto" size='30' maxlength='50' title='Digite o Nome'>
		</td>
		<td>
			<input type='radio' name='rad_sexo' value='M' title='Selecione o Sexo' checked>Masculino
			<input type='radio' name='rad_sexo' value='F' title='Selecione o Sexo'>Feminino
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2"><b>Foto:</b></td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2">
			<input type="file" name="txt_foto" title="Digite o Caminho da Foto" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Nacionalidade:</b></td>
		<td><b>Estado Civil:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<select name='cmb_nacionalidade' title="Selecione a Nacionalidade" class="combo">
			<?
				$sql = "SELECT id_nacionalidade, nacionalidade 
					FROM `nacionalidades` 
					WHERE ativo = '1' ORDER BY nacionalidade ";
				echo combos::combo($sql, 31);
			?>
			</select>
		</td>
		<td>
        		<select name='cmb_estado_civil' title="Selecione o Estado Civil" class="combo">
				<?=combos::combo_array($estado_civil);?>
			</select>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Naturalidade:</b></td>
		<td><b>NÌvel AcadÍmico:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_naturalidade' title="Selecione a Naturalidade" size='20' maxlength='20' title='Digite a Naturalidade' class="caixadetexto">
		</td>
		<td>
			<select name='cmb_nivel_academico' title="Selecione a NÌvel AcadÍmico" class="combo">
				<?=combos::combo_array($nivel_academico);?>
			</select>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Tipo de Sangue:</td>
		<td><b>Data de Nascimento:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<select name='cmb_tipo_sangue' title="Selecione o Tipo de Sangue" class="combo">
				<?=combos::combo_array($tipo_sangue);?>
			</select>
		</td>
		<td>
			<input type='text' name='txt_data_nascimento' size='20' maxlength='10' title='Digite a Data de Nascimento' onkeyup="verifica(this, 'data', '', '',event)" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>RG:</b></td>
		<td>Org&atilde;o Expedidor:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_rg' size='20' maxlength='15' title='Digite o RG' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_orgao_expedidor' size='20' maxlength='40' title='Digite o ”rgao Expedidor' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Data de Emiss„o:</td>
		<td>N˙mero da Carteira Profissional:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_data_emissao' size='20' maxlength='10' title='Digite a data de Emiss„o do RG' onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_carteira' size='20' maxlength='15' title='Digite a Carteira de Trabalho' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>SÈrie:</td>
		<td>PIS:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_serie' size='20' maxlength='15' title='Digite o N˙mero de SÈrie de Sua Carteira' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_pis' size='20' maxlength='15' title='Digite o PIS' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>TÌtulo de Eleitor:</td>
		<td>CPF:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_titulo' size='20' maxlength='15' title='Digite o TÌtulo de Eleitor' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_cpf' size='20' maxlength='11' title='Digite o CPF' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
            <td>Carteira de HabilitaÁ„o:</td>
            <td>Categoria da Carteira de HabilitaÁ„o:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_habilitacao' size='20' maxlength='20' title='Digite a Carteira de HabilitaÁ„o' class="caixadetexto">
		</td>
		<td>
			<select name='cmb_categoria' title='Selecione a Categoria' class="combo">
				<?=combos::combo_array($carteira_habilitacao);?>
			</select>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>PaÌs:</b></td>
		<td><b>CEP:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<select name='cmb_pais' title='Selecione o PaÌs' onchange='pais_abilita()' class="combo">
			<?
				$sql = "SELECT id_pais, pais 
					FROM `paises` 
                                        ORDER BY pais ";
				echo combos::combo($sql, 31);
			?>
			</select>
		</td>
		<td>
			<input type="text" name="txt_cep" size="20" maxlength="9" class="caixadetexto" title="Digite o Cep" onkeyup="verifica(this, 'cep', '', '', event)" onfocus="controlar(0)" onblur="atualizar_cep();controlar(1)">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>EndereÁo / N.∫ / Comp.:</b>
		</td>
		<td>
			<b>Bairro:</b>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_endereco' size='30' maxlength='50' title='EndereÁo' class="textdisabled" disabled>&nbsp;
			<input type="text" name="txt_numero" size="4" maxlength="15" class="caixadetexto" title="Digite o N˙mero" onkeyup="verifica(this, 'aceita', 'numeros', '', event)">&nbsp;
			<input type="text" name="txt_complemento" size="9" maxlength="10" class="caixadetexto" title="Digite o Complemento">
		</td>
		<td>
			<input type='text' name='txt_bairro' size='30' maxlength='20' title='Bairro' class="textdisabled" disabled>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Cidade:</b></td>
		<td><b>Estado:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_cidade' size='20' title='Cidade' class="textdisabled" disabled>
		</td>
		<td>
			<input type="text" name="txt_estado" size="35" title="Estado" class="caixadetexto" disabled>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>DDD Residencial:</td>
		<td><b>Telefone Residencial:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_ddd_residencial' maxlength='3' size='10' title='Digite o DDD Residencial' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_telefone_residencial' maxlength='9' size='20' title='Digite o Telefone Residencial' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>DDD Celular:</td>
		<td>Telefone Celular:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_ddd_celular' maxlength='3' size='10' title='Digite o DDD Celular' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_telefone_celular' maxlength='9' size='20' title='Digite o Telefone Celular' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2">Email Particular:</td>
	</tr>
	<tr class="linhanormal" >
		<td colspan="2">
			<input type="text" name="txt_email_particular" size="35" maxlength="50" title="Digite o Email Particular" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>DescriÁ„o:</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<textarea name='txt_descricao' cols='67' title='Digite a DescriÁ„o' class="caixadetexto"></textarea>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');pais_abilita();document.form.txt_nome.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>