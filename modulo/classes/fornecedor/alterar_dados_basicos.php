<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
session_start('funcionarios');

$mensagem[1] = "<font class = 'erro'>J&Aacute; EXISTE UM FORNECEDOR COM ESSE CNPJ OU CPF !</font>";
$mensagem[2] = "<font class = 'erro'>N√O PODE SER ALTERADA A UNIDADE FEDERAL DESTE FORNECEDOR DEVIDO O MESMO POSSUIR NF !!!\nA ALTERA«√O DA UNIDADE FEDERAL SER FEITA APRESENTANDO A ALTERA«√O CONTRATUAL ONDE CONSTA A MUDAN«A DE ENDERE«O ENTRE ESTADOS.</font>";
$mensagem[3] = "<font class = 'confirmacao'>FORNECEDOR ALTERADO COM SUCESSO.</font>";
$mensagem[4] = "<font class = 'erro'>J¡ EXISTE OUTRO FORNECEDOR COM O MESMO \"NOME FANTASIA\" OU \"RAZ√O SOCIAL\" NA MESMA UNIDADE FEDERAL.</font>";

$id_fornecedor 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_fornecedor'] : $_GET['id_fornecedor'];
$pop_up         = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
$detalhes       = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['detalhes'] : $_GET['detalhes'];

if(isset($_POST['txt_razao_social'])) {//AlteraÁ„o de Fornecedor ...
    $executar_update = 1;
    //Busca da UF do Fornecedor antes da AlteraÁ„o de cadastro ...
    $sql = "SELECT `id_pais`, `id_uf` 
            FROM `fornecedores` 
            WHERE id_fornecedor = '$id_fornecedor' LIMIT 1 ";
    $campos_fornecedor 	= bancos::sql($sql);
    $id_pais                = $campos_fornecedor[0]['id_pais'];
    $id_uf_cadastrado	= $campos_fornecedor[0]['id_uf'];

    //Busca o id_uf atravÈs do campo Estado ...
    $sql = "SELECT `id_uf` 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
    $campos_uf = bancos::sql($sql);

    if(!empty($_POST['txt_cnpj_cpf'])) {//Se essa vari·vel existir, ent„o atravÈs do CNPJ ou CPF verifico se o Fornecedor j· existe no BD ...
        $sql = "SELECT `id_fornecedor`, IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS fornecedor, `ativo` 
                FROM `fornecedores` 
                WHERE `cnpj_cpf` = '$_POST[txt_cnpj_cpf]' LIMIT 1 ";
        $campos_fornecedor = bancos::sql($sql);
        if(count($campos_fornecedor) == 1) {
            echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_fornecedor[0]['id_fornecedor'].' - '.utf8_encode($campos_fornecedor[0]['fornecedor'])."</center></b></font>";
            $executar_update = 0;
            $valor = 1;
        }else {
            //Atualiza o CPF ou CNPJ no cadastro do Fornecedor ...
            $campo_cnpj_cpf = " `cnpf_cpf` = '$_POST[txt_cnpj_cpf]', ";
        }
    }

    if($id_pais == 31) {
        if($campos_uf[0]['id_uf'] != $id_uf_cadastrado) {//Significa que houve mudanÁa na UF do Cliente ...
            //Verifico se esse Fornecedor possui pelo menos 1 NF que esteja Faturada ...
            $sql = "SELECT id_nfe 
                    FROM `nfe` 
                    WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
            $campos_nf = bancos::sql($sql);
            if(count($campos_nf) == 1) {//Significa que j· existe 1 NF anterior ...
                $executar_update = 0;
                $valor = 2;
            }
        }
    }
    if($executar_update == 1) {//Teoricamente j· posso atualizar o Fornecedor ...
        /*************************Controle para saber se o Fornecedor j· Existe sÛ que pela Raz„o Social ou Nome Fantasia*************************/
        //Verifico se esse Fornecedor existe pelo Nome Fantasia ...
        for($i = 0; $i < strlen($_POST['txt_nome_fantasia']); $i++) {
            if(substr($_POST['txt_nome_fantasia'], $i, 1) == ' ') {//Se o caractÈr for EspaÁo ...
                //Se tiver apenas 1 caracter, a String atÈ o momento n„o serve, pois È muito curta, ent„o continua varrendo o Loop ...
                if($caracteres_sem_espaco == 1) {//Se tiver apenas um caractÈr armazenado na String continua muito curto ...
                    $caracteres_sem_espaco = 0;
                }else {//Cai fora do Loop ...
                    break;
                }
            }else {//Se o caractÈr n„o for EspaÁo ...
                if(substr($_POST['txt_nome_fantasia'], $i, 1) != '.') {//E nem Ponto ...
                    $nome_fantasia.= substr($_POST['txt_nome_fantasia'], $i, 1);
                    $caracteres_sem_espaco++;
                    if($caracteres_sem_espaco == 1 && substr($_POST['txt_nome_fantasia'], ($i + 1), 1) != ' ') {
                        $nome_fantasia = substr($nome_fantasia, 0, strlen($nome_fantasia) - 1);
                        $nome_fantasia.= substr($_POST['txt_nome_fantasia'], $i, strlen($_POST['txt_nome_fantasia']));
                        $nome_fantasia = trim($nome_fantasia);
                        break;
                    }
                }
            }
        }
        //Verifico se esse Fornecedor existe pela Raz„o Social ...
        for($i = 0; $i < strlen($_POST['txt_razao_social']); $i++) {
            if(substr($_POST['txt_razao_social'], $i, 1) == ' ') {//Se o caractÈr for EspaÁo ...
                $razao_social.= '%';
            }else {//Se o caractÈr n„o for EspaÁo ...
                if(substr($_POST['txt_razao_social'], $i, 1) != '.') {//E nem Ponto ...
                    $razao_social.= substr($_POST['txt_razao_social'], $i, 1);
                }
            }
        }
        $razao_social = trim(addSlashes($razao_social));
        /**************************************************************************************************************************************/
        //Verifico se existe um outro Fornecedor com o nesmo Nome Fantasia ou Raz„o Social na mesma UF ...
        $condicao = (!empty($nome_fantasia)) ? " (`nomefantasia` LIKE '$nome_fantasia%' AND `razaosocial` LIKE '$razao_social') " : " `razaosocial` LIKE '$razao_social%' ";
        $sql = "SELECT id_fornecedor, IF(razaosocial = '', nomefantasia, razaosocial) fornecedor, ativo 
                FROM `fornecedores` 
                WHERE $condicao 
                AND `id_uf` = '".$campos_uf[0]['id_uf']."' 
                AND `id_fornecedor` <> '$id_fornecedor' 
                AND `ativo`	= '1' LIMIT 1 ";
        $campos_fornecedor = bancos::sql($sql);
        if(count($campos_fornecedor) == 1) {
            echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_fornecedor[0]['id_fornecedor'].' - '.utf8_encode($campos_fornecedor[0]['fornecedor'])."</center></b></font>";
            $executar_update = 0;
            $valor = 4;
        }else {
            $razao_social 	= strtoupper(str_replace('%', ' ', $razao_social));
            $nome_fantasia 	= strtoupper($nome_fantasia);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
            $id_uf          = (!empty($campos_uf[0]['id_uf'])) ? "'".$campos_uf[0]['id_uf']."'" : 'NULL';
            $sql = "UPDATE `fornecedores` SET `id_uf` = $id_uf, `nomefantasia` = '$_POST[txt_nome_fantasia]', `razaosocial` = '$_POST[txt_razao_social]', $campo_cnpj_cpf `endereco` = '$_POST[txt_endereco]', `num_complemento` = '$_POST[txt_num_complemento]', `bairro` = '$_POST[txt_bairro]', `cep` = '$_POST[txt_cep]', `cidade` = '$_POST[txt_cidade]', `ddd_fone1` = '$_POST[txt_ddd_comercial]', `fone1` = '$_POST[txt_tel_comercial]', `ddd_fone2` = '$_POST[txt_ddd_comercial2]', `fone2` = '$_POST[txt_tel_comercial2]', `ddd_fax` = '$_POST[txt_ddd_fax]', `fax` = '$_POST[txt_tel_fax]', `email` = '$_POST[txt_email]', `site` = '$_POST[txt_pagina_web]' WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 3;
        }
    }
}
/*************************************************************************************************************************/

$sql = "SELECT f.*, p.`pais`, ufs.`sigla` 
        FROM `fornecedores` f 
        LEFT JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
        INNER JOIN `paises` p ON p.`id_pais` = f.`id_pais` 
        WHERE f.`id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
$campos_fornecedores = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var id_pais = eval('<?=$campos_fornecedores[0]['id_pais'];?>')
//Raz„o Social
    if(!texto('form','txt_razao_social','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'RAZ√O SOCIAL', '1')) {
        return false
    }
//Nome Fantasia
    if(document.form.txt_nome_fantasia.value != '') {
        if(!texto('form', 'txt_nome_fantasia', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'NOME FANTASIA', '2')) {
            return false
        }
    }
//Se o PaÌs for Brasil, ent„o forÁa o preenchimento de CEP
    if(id_pais == 31) {
        if(typeof(document.form.txt_cnpj_cpf) == 'object') {//Se existir esse objeto no Formul·rio ent„o faÁo a ValidaÁ„o ...
            if(document.form.txt_cnpj_cpf.value != '') {
                if(document.form.txt_cnpj_cpf.value.length > 11) {//SÛ verifica CNPJ
                    if (!cnpj('form', 'txt_cnpj_cpf')) {
                        document.form.txt_cnpj_cpf.focus()
                        document.form.txt_cnpj_cpf.select()
                        return false
                    }
                }else {//SÛ verifica CPF
                    if (!cpf('form', 'txt_cnpj_cpf')) {
                        document.form.txt_cnpj_cpf.focus()
                        document.form.txt_cnpj_cpf.select()
                        return false
                    }
                }
            }
        }
//Cep
        if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
            return false
        }
//PaÌs Internacional
    }else {
//EndereÁo
        if(!texto('form', 'txt_endereco', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'ENDERE«O', '2')) {
            return false
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
    }
//N˙mero / Complemento
    if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO', '2')) {
        return false
    }
//DDD Comercial
    if(document.form.txt_ddd_comercial.value != '') {
        if(!texto('form', 'txt_ddd_comercial', '1', '1234567890', 'DDD COMERCIAL', '2')) {
            return false
        }
    }
//Telefone Comercial
    if(!texto('form', 'txt_tel_comercial', '7', '1234567890', 'TELEFONE COMERCIAL', '2')) {
        return false
    }
//DDD Comercial 2
    if(document.form.txt_ddd_comercial2.value != '') {
        if(!texto('form', 'txt_ddd_comercial2', '1', '1234567890', 'DDD COMERCIAL 2', '2')) {
            return false
        }
    }
//Telefone Comercial 2
    if(document.form.txt_tel_comercial2.value != '') {
        if(!texto('form', 'txt_tel_comercial2', '7', '1234567890', 'TELEFONE COMERCIAL 2', '2')) {
            return false
        }
    }
//DDD Fax
    if(document.form.txt_ddd_fax.value != '') {
        if(!texto('form', 'txt_ddd_fax', '1', '1234567890', 'DDD FAX', '2')) {
            return false
        }
    }
//Telefone FAX
    if(document.form.txt_tel_fax.value != '') {
        if(!texto('form', 'txt_tel_fax', '7', '()1234567890/ ', 'TELEFONE FAX', '2')) {
            return false
        }
    }
//E-mail
    if(document.form.txt_email.value != '') {
        if (!new_email('form', 'txt_email')) {
            return false
        }
    }
//Aqui È para n„o reler a Tela de Baixo quando Clicar no Bot„o Salvar, a idÈia È apenas reler pelo Bot„o X do Pop-UP ...
    document.form.nao_atualizar.value = 1
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
    document.form.txt_razao_social.value    = document.form.txt_razao_social.value.toUpperCase()
    document.form.txt_nome_fantasia.value   = document.form.txt_nome_fantasia.value.toUpperCase()
    document.form.txt_endereco.value        = document.form.txt_endereco.value.toUpperCase()
    document.form.txt_bairro.value          = document.form.txt_bairro.value.toUpperCase()
}

function copiar_telefone() {
    document.form.txt_ddd_comercial2.value  = document.form.txt_ddd_comercial.value
    document.form.txt_tel_comercial2.value  = document.form.txt_tel_comercial.value
    document.form.txt_ddd_fax.value         = document.form.txt_ddd_comercial.value
    document.form.txt_tel_fax.value         = document.form.txt_tel_comercial.value
    document.form.txt_email.focus()
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep() {
    var id_pais = eval('<?=$campos_fornecedores[0]['id_pais'];?>')
    if(id_pais == 31) {//SÛ buscar· o CEP se for Brasil
        if(document.form.txt_cep.value == '') {//Verifico se o CEP È v·lido ...
            document.form.txt_endereco.value = ''
            document.form.txt_bairro.value = ''
            document.form.txt_cidade.value = ''
            document.form.txt_estado.value = ''
        }else {
            if(document.form.txt_cep.value.length < 9) {//Verifico se o CEP È v·lido ...
                alert('CEP INV¡LIDO !')
                document.form.txt_cep.focus()
                document.form.txt_cep.select()
                return false
            }else {
                cep.location = '../../classes/cep/buscar_cep.php?txt_cep='+document.form.txt_cep.value
            }
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_razao_social.focus()">
<form name="form" method="post" action='' onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar' value='0'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='2'>
			<?=$mensagem[$valor];?>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan='2'>
                    Alterar Fornecedor
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width='50%'>
			<b>PaÌs:</b>
		</td>
		<td width='50%'>
		<?
			if(strlen($campos_fornecedores[0]['cnpj_cpf']) == 14) {//Se essa vari·vel for passada por par‚metro atravÈs do CNPJ ou CPF verifico se o Cliente j· existe no BD ...
				echo 'CNPJ:';
				$cnpj_cpf				= substr($campos_fornecedores[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_fornecedores[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_fornecedores[0]['cnpj_cpf'], 12, 2);
				$class_dados_endereco 	= 'textdisabled';
				$class_cep 				= 'caixadetexto';
			}else if(strlen($campos_fornecedores[0]['cnpj_cpf']) == 11) {//Se essa vari·vel for passada por par‚metro atravÈs do CNPJ ou CPF verifico se o Cliente j· existe no BD ...
				echo 'CPF:';
				$cnpj_cpf				= substr($campos_fornecedores[0]['cnpj_cpf'], 0, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 3, 3).'.'.substr($campos_fornecedores[0]['cnpj_cpf'], 6, 3).'-'.substr($campos_fornecedores[0]['cnpj_cpf'], 9, 2);
				$class_dados_endereco 	= 'caixadetexto';
				$class_cep 				= 'textdisabled';
			}else {
				echo 'CNPJ ou CPF:';
				$exibir_caixa_cnpj		= 1;
				$class_dados_endereco 	= 'textdisabled';
				$class_cep 				= 'caixadetexto';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<font color="darkblue" size="-1">
				<b><?=strtoupper($campos_fornecedores[0]['pais']);?></b>
			</font>
		</td>
		<td>
		<?
			if($exibir_caixa_cnpj == 1) {//Significa que n„o existia nenhum CPF / CNPJ cadastrado anteriormente p/ o Cliente, sendo assim exibe CNPJ 
		?>
			<input type='text' name="txt_cnpj_cpf" title="Digite CNPJ" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="20" maxlength="18" class='caixadetexto'>
		<?	
			}else {//Aqui significa que j· existia alguma InformaÁ„o do Tipo ...
		?>
			<font color="darkblue" size="-1">
				<b><?=$cnpj_cpf;?></b>
			</font>
		<?
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="50%">
			<b>Raz&atilde;o Social:</b>
		</td>
		<td width="50%">
			Nome Fantasia:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_razao_social" value="<?=$campos_fornecedores[0]['razaosocial'];?>" title="Digite a Raz&atilde;o Social" size="50" class='caixadetexto' maxlength="80">
		</td>
		<td>
			<input type='text' name="txt_nome_fantasia" value="<?=$campos_fornecedores[0]['nomefantasia'];?>" title="Digite o Nome Fantasia" size="35" class='caixadetexto' maxlength="50">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<b>CEP:</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='text' name="txt_cep" value="<?=$campos_fornecedores[0]['cep'];?>" size="20" maxlength="9" title="Digite o Cep" onfocus="if(this.className == 'textdisabled') document.form.txt_endereco.focus()" onkeyup="verifica(this, 'cep', '', '', event)" onblur="buscar_cep()" class="<?=$class_cep;?>">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Endere&ccedil;o:
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<b>N.&#176; / Complemento</b>
		</td>
		<td>
			Bairro:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_endereco" value="<?=$campos_fornecedores[0]['endereco'];?>" size="45" maxlength="50" title="Endere&ccedil;o" onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento.focus()" class="<?=$class_dados_endereco;?>">
			&nbsp;
			<input type='text' name="txt_num_complemento" value="<?=$campos_fornecedores[0]['num_complemento'];?>" title="Digite o N&uacute;mero, Complemento, ..." size="10" maxlength="50" class='caixadetexto'>
		</td>
		<td>
			<input type='text' name="txt_bairro" value="<?=$campos_fornecedores[0]['bairro'];?>" size="35" title="Bairro" onfocus="if(this.className == 'textdisabled') document.form.txt_ddd_comercial.focus()" class="<?=$class_dados_endereco;?>">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Cidade:
		</td>
		<td>
			Estado:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_cidade" value="<?=$campos_fornecedores[0]['cidade'];?>" size="35" title="Cidade" onfocus="if(this.className == 'textdisabled') document.form.txt_ddd_comercial.focus()" class="<?=$class_dados_endereco;?>">
		</td>
		<td>
			<input type='text' name="txt_estado" value="<?=$campos_fornecedores[0]['sigla'];?>" size="35" title="Estado" onfocus="document.form.txt_ddd_comercial.focus()" class="textdisabled">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			&nbsp;
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
			<b>Tel. Comercial 1:</b>
		</td>
                <td>
			DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
			Tel. Comercial 2:
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_ddd_comercial" value="<?=$campos_fornecedores[0]['ddd_fone1'];?>" title="Digite o DDD Comercial" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
			&nbsp;&nbsp;&nbsp;
			<input type='text' name="txt_tel_comercial" value="<?=$campos_fornecedores[0]['fone1'];?>" title="Digite o Telefone Comercial" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
			&nbsp;
			<input type='button' name="cmd_copiar" value="Copiar Telefone =>" title="Copiar Telefone =>" onclick="copiar_telefone()" class='caixadetexto'>
		</td>
		<td>
			<input type='text' name="txt_ddd_comercial2" value="<?=$campos_fornecedores[0]['ddd_fone2'];?>" title="Digite o DDD Comercial 2" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
			&nbsp;&nbsp;&nbsp;
			<input type='text' name="txt_tel_comercial2" value="<?=$campos_fornecedores[0]['fone2'];?>" title="Digite o Telefone Comercial 2" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
			Tel. Fax:
		</td>
		<td>
			E-Mail:
		</td>
	</tr>
	<tr class='linhanormal'>
                <td>
			<input type='text' name="txt_ddd_fax" value="<?=$campos_fornecedores[0]['ddd_fax'];?>" title="Digite o DDD Fax" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
			&nbsp;&nbsp;&nbsp;
			<input type='text' name="txt_tel_fax" value="<?=$campos_fornecedores[0]['fax'];?>" title="Digite o Telefone Fax" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
		</td>
		<td>
			<input type='text' name="txt_email" value="<?=$campos_fornecedores[0]['email'];?>" size="50" maxlength="85" title="Digite o E-mail" class='caixadetexto'>
			&nbsp;
			<a href="mailto:<?=$campos_fornecedores[0]['email'];?>"><img src="../../../imagem/cobrou0vez.png" title="Enviar E-mail" style="cursor:help" height="25"></a>
		</td>
	</tr>
	<tr class='linhanormal'>
            <td colspan='2'>
                P&aacute;gina Web:
            </td>
	</tr>
	<tr class='linhanormal'>
            <td colspan='2'>
                <input type='text' name='txt_pagina_web' value='<?=$campos_fornecedores[0]['site'];?>' size='35' title='Digite a P&aacute;gina Web' class='caixadetexto'>
            </td>
	</tr>
        <!--****************************Follow-UPs***************************-->
        <tr align='center'>
            <td colspan='2'>
                <!--*********Passo o par‚metro cmb_origem=15 para que no inÌcio sÛ carregue nessa parte 
                de Follow-Ups dados que s„o pertinentes a parte de cadastro*********-->
                <iframe name='detalhes' id='detalhes' src='../follow_ups/detalhes.php?id_fornecedor=<?=$id_fornecedor;?>&origem=15&cmb_origem=15' marginwidth='0' marginheight='0' frameborder='0' height='260' width='100%'></iframe>
            </td>
        </tr>
        <!--*****************************************************************-->
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
            <?
                //Quando essa tela, for aberta como Detalhes, n„o exibo esses botıes ...
                if($pop_up != 1) {
            ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.parent.location = 'alterar.php<?=$parametro;?>'" class='botao'>
                <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');" style="color:#ff9900;" class='botao'>
                <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <?
                }else {
            ?>
                <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.parent.close()" class='botao'>
            <?		
                }
            ?>
            </td>
	</tr>
	<!--Aqui busco o EndereÁo atravÈs do Cep do Cliente ...-->
	<iframe name="cep" id="cep" marginwidth="0" marginheight="0" frameborder="0" height="0" width="0"></iframe>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observa&ccedil;&atilde;o:</b></font>

<b>* Os campos em Negrito s&atilde;o obrigat&oacute;rios.</b>
</pre>