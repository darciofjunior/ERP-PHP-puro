<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/custos.php');
require('../../../lib/variaveis/dp.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

$mensagem[1] = "FUNCIONÁRIO ALTERADO COM SUCESSO !";
$mensagem[2] = "FUNCIONÁRIO JÁ EXISTENTE !";

if($passo == 1) {
/*****************************Código Provisório*********************************/
//Busca o id_uf através do campo Estado
    $sql = "SELECT `id_uf` 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
    $campos_uf  = bancos::sql($sql);
    $id_uf      = $campos_uf[0]['id_uf'];
/*******************************************************************************/
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
    $sql = "SELECT id_funcionario 
            FROM `funcionarios` 
            WHERE `cpf` = '$txt_cpf' 
            AND `id_funcionario` <> '$_POST[id_funcionario_loop]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
        require('../../../lib/mda.php');
//Fazendo Upload da Imagem para o Servidor ...
        if(!empty($_FILES['txt_foto'])) {
            if($_FILES['txt_foto']['error'] > 0) {
                if($_FILES['txt_foto']['error'] == 1 || $_FILES['txt_foto']['error'] == 2) {
?>
    <Script Language = 'JavaScript'>
        alert('O TAMANHO DESSE ARQUIVO É MUITO GRANDE P/ SER UPADO NO SERVIDOR !!!\n\nO TAMANHO MÁXIMO DE ARQUIVO QUE O SERVIDOR SUPORTA SÃO 2 MB !')
    </Script>
<?
                }
            }else {
                switch ($_FILES['txt_foto']['type']) {
                    case 'image/gif':
                    case 'image/pjpeg':
                    case 'image/jpeg':
                    case 'image/x-png':
                    case 'image/bmp':
                        $foto       = copiar::copiar_arquivo('../../../imagem/fotos_funcionarios/', $_FILES['txt_foto']['tmp_name'], $_FILES['txt_foto']['name'], $_FILES['txt_foto']['size'], $_FILES['txt_foto']['type'], '2');
                        $campo_foto = ", `path_foto` = '$foto' ";
                    break;
                    default:
                    break;
                }
            }
        }
//Tratamento com os campos de Data p/ poder gravar no Banco de Dados ...
        $txt_data_emissao       = data::datatodate($txt_data_emissao, '-');
        $txt_data_nascimento    = data::datatodate($txt_data_nascimento, '-');
//Atualizando os dados na Tabela de Funcionários ...
        $sql = "UPDATE `funcionarios` SET `nome` = '$txt_nome', `cpf` = '$txt_cpf', `rg` = '$txt_rg', `data_emissao` = '$txt_data_emissao', `orgao_expedidor` = '$txt_orgao_expedidor', `carteira_profissional` = '$txt_carteira', `serie_profissional` = '$txt_serie', `pis` = '$txt_pis', `titulo_eleitor` = '$txt_titulo', `habilitacao` = '$txt_habilitacao', `cod_categoria` = '$cmb_categoria', `endereco` = '$txt_endereco', `numero` = '$txt_numero', `complemento` = '$txt_complemento', `bairro` = '$txt_bairro', `cep` = '$txt_cep', `cidade` = '$txt_cidade', `id_uf` = '$id_uf', `telefone_celular` = '$txt_telefone_celular', `telefone_residencial` = '$txt_telefone_residencial', `email_particular` = '$txt_email_particular', `data_nascimento` = '$txt_data_nascimento', `id_nacionalidade` = '$cmb_nacionalidade', `sexo` = '$rad_sexo', `cod_academico` = '$cmb_nivel_academico', `cod_civil` = '$cmb_estado_civil', `cod_sangue` = '$cmb_tipo_sangue', `ddd_residencial` = '$txt_ddd_residencial', `ddd_celular` = '$txt_ddd_celular', `id_pais` = '$cmb_pais', `naturalidade` = '$txt_naturalidade', `descricao` = '$txt_descricao', `data_registro` = '".date('Y-m-d H:i:s')."' $campo_foto WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        bancos::sql($sql);
        custos::localizar_maquina($id_funcionario_loop); //obedecer a ordem primeiro vejo o atrelamento para recalcular, depois apago ele
        $valor = 1;
    }else {
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
        window.location = 'alterar_dados_pessoais.php?id_funcionario_loop=<?=$_POST['id_funcionario_loop'];?>'
    </Script>
<?
}else {
/*****************************Código Provisório*********************************/
//Busca o id_uf através do campo Estado
	$sql = "SELECT id_uf 
                FROM `ufs` 
                WHERE `sigla` = '$txt_estado' LIMIT 1 ";
	$campos_uf  = bancos::sql($sql);
	$id_uf      = $campos_uf[0]['id_uf'];
/*******************************************************************************/
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
	$sql = "SELECT f.*, ufs.id_uf, ufs.sigla 
                FROM `funcionarios` f 
                INNER JOIN `ufs` ON ufs.id_uf = f.id_uf 
                WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
	$campos             = bancos::sql($sql);
	$nome               = $campos[0]['nome'];
	$sexo               = $campos[0]['sexo'];
	$id_nacionalidade   = $campos[0]['id_nacionalidade'];
	$cod_civil          = $campos[0]['cod_civil'];
	$naturalidade       = $campos[0]['naturalidade'];
	$cod_academico      = $campos[0]['cod_academico'];
	$cod_sangue         = $campos[0]['cod_sangue'];
	$data_nascimento    = $campos[0]['data_nascimento'];
	$rg                 = $campos[0]['rg'];
	$orgao_expedidor = $campos[0]['orgao_expedidor'];
	$data_emissao = $campos[0]['data_emissao'];
	if($data_emissao == '0000-00-00') {
		$data_emissao = '';
	}else {
		$data_emissao = data::datetodata($data_emissao, '/');
	}
	$carteira_profissional = $campos[0]['carteira_profissional'];
	$serie_profissional = $campos[0]['serie_profissional'];
	$pis                = $campos[0]['pis'];
	$titulo_eleitor     = $campos[0]['titulo_eleitor'];
	$cpf                = $campos[0]['cpf'];
	$habilitacao        = $campos[0]['habilitacao'];
	$cod_categoria      = $campos[0]['cod_categoria'];
	$id_pais            = $campos[0]['id_pais'];
	$cep                = $campos[0]['cep'];
	$endereco           = $campos[0]['endereco'];
	$numero             = $campos[0]['numero'];
	$complemento        = $campos[0]['complemento'];
	$bairro             = $campos[0]['bairro'];
	$cidade             = $campos[0]['cidade'];
	$id_estado          = $campos[0]['id_uf'];

	$sql = "SELECT sigla 
                FROM `ufs` 
                WHERE `id_uf` = '$id_estado' LIMIT 1 ";
	$campos_uf  = bancos::sql($sql);
	$estado     = $campos_uf[0]['sigla'];

	$ddd_residencial    = $campos[0]['ddd_residencial'];
	$telefone_residencial = $campos[0]['telefone_residencial'];
	$ddd_celular        = $campos[0]['ddd_celular'];
//Validação do DDD Celular
	if($ddd_celular == 0) $ddd_celular = '';
        $telefone_celular = $campos[0]['telefone_celular'];
//Validação do Telefone Celular
	if($telefone_celular == 0) $telefone_celular = '';

        $email_particular = $campos[0]['email_particular'];
	$descricao = $campos[0]['descricao'];

//Aqui é uma verificação para habilitação no JavaScript
//Busca os dados da tabela ceps
	$sql = "SELECT logradouro, bairro 
                FROM `ceps` 
                WHERE `cep` = '$cep' LIMIT 1 ";
	$campos_endereco    = bancos::sql($sql);
	$endereco_verifica  = $campos_endereco[0]['logradouro'];
	$bairro_verifica    = $campos_endereco[0]['bairro'];
/****************************************************************************************/
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
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Habilita a Unidade Federal
function pais_abilita() {
/*Existe essa variável para fazer um controle de quando é que se tem que puxar
os dados para o Brasil ou para países Internacionais, ela verifica da hora em
que se carrega na tela do banco de dados*/
	var id_pais = eval('<?=$id_pais?>')
	var endereco = "<?=$endereco_verifica;?>"
	var bairro = "<?=$bairro_verifica;?>"
//Significa que já veio do Banco de Dados como sendo Brasil
	if(id_pais == 31) {
		if (document.form.cmb_pais.value == '31') {
/*Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro,
caso o cep não retornar os mesmos*/
			if(endereco == '' || bairro == '') {
//Trocando a Cor da Letra para Habilitado
				document.form.txt_endereco.style.color = 'Brown'
				document.form.txt_bairro.style.color = 'Brown'
//Trocando a Cor do Fundo para Habilitado
				document.form.txt_endereco.style.background = '#FFFFFF'
				document.form.txt_bairro.style.background = '#FFFFFF'
//Habilitando
				document.form.txt_endereco.disabled = false
				document.form.txt_bairro.disabled = false
			}else {
//Trocando a Cor da Letra para Desabilitado
				document.form.txt_endereco.style.color = 'gray'
				document.form.txt_bairro.style.color = 'gray'
//Trocando a Cor do Fundo para Desabilitado
				document.form.txt_endereco.style.background = '#FFFFE1'
				document.form.txt_bairro.style.background = '#FFFFE1'
//Desabilitando
				document.form.txt_endereco.disabled = true
				document.form.txt_bairro.disabled = true
			}
//Trocando a Cor da Letra para Desabilitado
			document.form.txt_cidade.style.color = 'gray'
			document.form.txt_estado.style.color = 'gray'
//Trocando a Cor do Fundo para Desabilitado
			document.form.txt_cidade.style.background = '#FFFFE1'
			document.form.txt_estado.style.background = '#FFFFE1'
//Desabilitando
			document.form.txt_cidade.disabled = true
			document.form.txt_estado.disabled = true
//Habilitando
			document.form.txt_cep.disabled = false
//Exibindo os Dados
			document.form.txt_cep.value = "<?=$cep?>"
			document.form.txt_endereco.value = "<?=$endereco?>"
			document.form.txt_numero.value = "<?=$numero?>"
			document.form.txt_complemento.value = "<?=$complemento?>"
			document.form.txt_bairro.value = "<?=$bairro?>"
			document.form.txt_cidade.value = "<?=$cidade?>"
			document.form.txt_estado.value = "<?=$estado?>"
		}else {
//Trocando a Cor da Letra para Habilitado
			document.form.txt_endereco.style.color = 'Brown'
			document.form.txt_bairro.style.color = 'Brown'
			document.form.txt_cidade.style.color = 'Brown'
			document.form.txt_estado.style.color = 'Brown'
//Trocando a Cor do Fundo para Habilitado
			document.form.txt_endereco.style.background = '#FFFFFF'
			document.form.txt_bairro.style.background = '#FFFFFF'
			document.form.txt_cidade.style.color = '#FFFFFF'
			document.form.txt_estado.style.color = '#FFFFFF'
//Limpando os Dados
			document.form.txt_cep.value = ''
			document.form.txt_endereco.value = ''
			document.form.txt_numero.value = ''
			document.form.txt_complemento.value = ''
			document.form.txt_bairro.value = ''
			document.form.txt_cidade.value = ''
			document.form.txt_estado.value = ''

			document.form.txt_cep.disabled = true
//Habilitando
			document.form.txt_endereco.disabled = false
			document.form.txt_bairro.disabled = false
			document.form.txt_cidade.disabled = false
			document.form.txt_estado.disabled = false
//Estado
			document.form.txt_endereco.focus()
		}
//Significa que já veio do Banco de Dados como sendo Importação
	}else {
		if (document.form.cmb_pais.value == '31') {
/*Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro,
caso o cep não retornar os mesmos*/
			if(endereco == '' || bairro == '') {
//Trocando a Cor da Letra para Habilitado
				document.form.txt_endereco.style.color = 'Brown'
				document.form.txt_bairro.style.color = 'Brown'
//Trocando a Cor do Fundo para Habilitado
				document.form.txt_endereco.style.background = '#FFFFFF'
				document.form.txt_bairro.style.background = '#FFFFFF'
//Habilitando
				document.form.txt_endereco.disabled = false
				document.form.txt_bairro.disabled = false
			}else {
//Trocando a Cor da Letra para Desabilitado
				document.form.txt_endereco.style.color = 'gray'
				document.form.txt_bairro.style.color = 'gray'
//Trocando a Cor do Fundo para Desabilitado
				document.form.txt_endereco.style.background = '#FFFFE1'
				document.form.txt_bairro.style.background = '#FFFFE1'
//Desabilitando
				document.form.txt_endereco.disabled = true
				document.form.txt_bairro.disabled = true
			}
//Trocando a Cor da Letra para Desabilitado
			document.form.txt_cidade.style.color = 'gray'
			document.form.txt_estado.style.color = 'gray'
//Trocando a Cor do Fundo para Desabilitado
			document.form.txt_cidade.style.background = '#FFFFE1'
			document.form.txt_estado.style.background = '#FFFFE1'
//Desabilitando
			document.form.txt_cidade.disabled = true
			document.form.txt_estado.disabled = true
//Habilitando
			document.form.txt_cep.disabled = false
//Exibindo os Dados
			document.form.txt_cep.value = ''
			document.form.txt_endereco.value = ''
			document.form.txt_numero.value = ''
			document.form.txt_complemento.value = ''
			document.form.txt_bairro.value = ''
			document.form.txt_cidade.value = ''
			document.form.txt_estado.value = ''
		}else {
//Trocando a Cor da Letra para Habilitado
			document.form.txt_endereco.style.color = 'Brown'
			document.form.txt_bairro.style.color = 'Brown'
			document.form.txt_cidade.style.color = 'Brown'
			document.form.txt_estado.style.color = 'Brown'
//Trocando a Cor do Fundo para Habilitado
			document.form.txt_endereco.style.background = '#FFFFFF'
			document.form.txt_bairro.style.background = '#FFFFFF'
			document.form.txt_cidade.style.color = '#FFFFFF'
			document.form.txt_estado.style.color = '#FFFFFF'
//Limpando os Dados
			document.form.txt_cep.value = "<?=$cep?>"
			document.form.txt_endereco.value = "<?=$endereco?>"
			document.form.txt_numero.value = "<?=$numero?>"
			document.form.txt_complemento.value = "<?=$complemento?>"
			document.form.txt_bairro.value = "<?=$bairro?>"
			document.form.txt_cidade.value = "<?=$cidade?>"
			document.form.txt_estado.value = "<?=$estado?>"

			document.form.txt_cep.disabled = true
//Habilitando
			document.form.txt_endereco.disabled = false
			document.form.txt_bairro.disabled = false
			document.form.txt_cidade.disabled = false
			document.form.txt_estado.disabled = false
//Estado
			document.form.txt_endereco.focus()
		}
	}
}

function validar() {
    var id_funcionario = eval('<?=$id_funcionario_loop?>')
//Nome
    if(!texto('form', 'txt_nome', '3', 'qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ ', 'NOME', '2')) {
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
    if(!texto('form', 'txt_naturalidade', '3', 'qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ ', 'NATURALIDADE', '1')) {
        return false
    }
//Nível Acadêmico
    if(!combo('form', 'cmb_nivel_academico', '', 'SELECIONE O NÍVEL ACADÊMICO !')) {
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
//Órgão
    if(document.form.txt_orgao_expedidor.value != '') {
        if(!texto('form', 'txt_orgao_expedidor', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ- ', 'ÓRGÃO EXPEDIDOR', '2')) {
            return false
        }
    }
//Data de Emissão
    if(document.form.txt_data_emissao.value != '') {
        if(!data('form', 'txt_data_emissao', "4000", 'EMISSÃO')) {
            return false
        }
    }
//CPF
    var nro = document.form.txt_cpf.value
    if(nro.length > 11) {
        for(i = 0; i < nro.length; i++) {
            letra = nro.charAt(i)
            if((letra == '.') || (letra == '-')) nro = nro.replace(letra,'')
        }
        document.form.txt_cpf.value = nro
        if (!cpf('form','txt_cpf')) {
            return false
        }
    }else {
        if (!cpf('form','txt_cpf')) return false
    }
//Se o País for Brasil, então força o preenchimento de CEP
    if(document.form.cmb_pais.value == 31) {
//Cep
        if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
            return false
        }
//Número
        if(!texto('form', 'txt_numero', '1', '0123456789', 'NÚMERO', '2')) {
            return false
        }
//Complemento
        if(document.form.txt_complemento.value != '') {
            if(!texto('form', 'txt_complemento', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-. ', 'COMPLEMENTO', '2')) {
                return false
            }
        }
//País Internacional
    }else {
//Endereço
        if(!texto('form', 'txt_endereco', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'ENDEREÇO', '2')) {
            return false
        }
//Número
        if(!texto('form', 'txt_numero', '1', '0123456789', 'NÚMERO', '2')) {
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
            if(!texto('form', 'txt_bairro', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'BAIRRO', '2')) {
                return false
            }
        }
//Cidade
        if(document.form.txt_cidade.value != '') {
            if(!texto('form', 'txt_cidade', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CIDADE', '1')) {
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
//Aqui serve para não submeter
    if(document.form.controle.value == 0) {
        return false
    }
//Desabilita para poder gravar no Banco de Dados
//Endereço
    document.form.txt_endereco.disabled = false
    document.form.txt_bairro.disabled = false
    document.form.txt_cidade.disabled = false
    document.form.txt_estado.disabled = false
//Converte o endereço e o bairro para maiúsculo para ficar mais organizado
    document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
    document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
    document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
}

function atualizar_cep() {
	var id_pais = document.form.cmb_pais.value
	var txt_cep = document.form.txt_cep.value
	if(txt_cep == '') {//É vázio
		document.form.txt_endereco.value = "<?=$endereco;?>"
		document.form.txt_bairro.value = "<?=$bairro;?>"
		document.form.txt_cidade.value = "<?=$cidade;?>"
		document.form.txt_estado.value = "<?=$estado?>"
	}else {//Não é vázio
//Verifica se o CEP é válido
		if(txt_cep.length < 9) {
			alert('CEP INVÁLIDO !')
			document.form.txt_cep.focus()
			document.form.txt_cep.select()
			return false
		}
		if(id_pais == 31) {//Só buscará o CEP se for Brasil
			window.parent.parent.cep.location = 'buscar_cep.php?txt_cep='+txt_cep
		}
	}
}

//Função que controla para não submeter
function controlar(valor) {
	document.form.controle.value = valor
}
</Script>
</head>
<body onload='pais_abilita();document.form.txt_nome.focus()'>
<form name='form' method='post' onsubmit='return validar()' action='<?=$PHP_SELF.'?passo=1'?>' enctype="multipart/form-data">
<!--Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão-->
<input type="hidden" name="id_funcionario_loop" value="<?=$_GET['id_funcionario_loop'];?>">
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
			<input type='text' name='txt_nome' value='<?=$nome;?>' class="caixadetexto" size='30' maxlength='50' title='Digite o Nome'>
		</td>
		<td>
		<?
			if($sexo == 'M') {
		?>
				<input type='radio' name='rad_sexo' value='M' title='Selecione o sexo' checked>Masculino
				<input type='radio' name='rad_sexo' value='F' title='Selecione o sexo'>Feminino
		<?
			}else {
		?>
				<input type='radio' name='rad_sexo' value='M' title='Selecione o sexo'>Masculino
				<input type='radio' name='rad_sexo' value='F' title='Selecione o sexo' checked>Feminino
		<?
			}
		?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Foto:</b></td>
		<td><b>Foto Atual:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type="file" name="txt_foto" title="Digite o Caminho da Foto" class="caixadetexto">
		</td>
		<td>
		<?
//Se o Funcionário não tiver foto, então eu exibo o logotipo da Albafer p/ não ficar feio na Tela ...
			if(empty($campos[0]['path_foto'])) {
		?>
			<img src="../../../imagem/logosistema.jpg" width="110" height="120">
		<?
//Aqui já é a foto do usuário que vai estar sendo exibida normalmente no Sistema ...
			}else {
		?>
			<img src="../../../imagem/fotos_funcionarios/<?=$campos[0]['path_foto'];?>" title="Clique aqui para ampliar" width="180" height="120">
		<?
			}
		?>
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
				$sql = "Select id_nacionalidade, nacionalidade 
					from `nacionalidades` 
					where ativo = '1' order by nacionalidade asc ";
				echo combos::combo($sql, $id_nacionalidade);
			?>
			</select>
		</td>
		<td>
        		<select name='cmb_estado_civil' title="Selecione o Estado Civil" class="combo">
				<?=combos::combo_array($estado_civil, $cod_civil);?>
			</select>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Naturalidade:</b></td>
		<td><b>Nível Acadêmico:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_naturalidade' value='<?=$naturalidade;?>' title='Digite a Naturalidade' size='20' maxlength='20' class="caixadetexto">
		</td>
		<td>
			<select name='cmb_nivel_academico' title="Selecione o Nível Acadêmico" class="combo">
				<?=combos::combo_array($nivel_academico, $cod_academico);?>
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
				<?=combos::combo_array($tipo_sangue, $cod_sangue);?>
			</select>
		</td>
		<td>
			<input type='text' name='txt_data_nascimento' value='<?=data::datetodata($data_nascimento, '/');?>' size='20' maxlength='10' title='Digite a Data de Nascimento' onkeyup="verifica(this, 'data', '', '',event)" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>RG:</b></td>
		<td>Org&atilde;o Expedidor:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_rg' value='<?=$rg;?>' size='20' maxlength='15' title='Digite o RG' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_orgao_expedidor' value='<?=$orgao_expedidor;?>' size='20' maxlength='40' title='Digite o Órgao Expedidor' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Data de Emissão:</td>
		<td>Número da Carteira Profissional:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' size='20' maxlength='10' title='Digite a data de Emissão do RG' onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_carteira' value='<?=$carteira_profissional;?>' size='20' maxlength='15' title='Digite a Carteira de Trabalho' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Série:</td>
		<td>PIS:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_serie' value='<?=$serie_profissional;?>' size='20' maxlength='15' title='Digite o Número de Série de Sua Carteira' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_pis' value='<?=$pis;?>' size='20' maxlength='15' title='Digite o PIS' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Título de Eleitor:</td>
		<td><b>CPF:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_titulo' value='<?=$titulo_eleitor;?>' size='20' maxlength='15' title='Digite o Título de Eleitor' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_cpf' value='<?=$cpf;?>' size='20' maxlength='11' title='Digite o CPF' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Carteira de Habilitação:</td>
		<td>Categoria da Carteira de Habilitação:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_habilitacao' value='<?=$habilitacao;?>' size='20' maxlength='20' title='Digite a Carteira de Habilitação' class="caixadetexto">
		</td>
		<td>
			<select name='cmb_categoria' title='Selecione a Categoria' class="combo">
				<?=combos::combo_array($carteira_habilitacao, $cod_categoria);?>
			</select>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>País:</b></td>
		<td><b>CEP:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<select name='cmb_pais' title='Selecione o País' onchange='pais_abilita()' class="combo">
			<?
				$sql = "Select id_pais, pais 
					from `paises` order by pais ";
				echo combos::combo($sql, $id_pais);
			?>
			</select>
		</td>
		<td>
			<input type="text" name="txt_cep" value='<?=$cep;?>' size="20" maxlength="9" class="caixadetexto" title="Digite o Cep" onkeyup="verifica(this, 'cep', '', '', event)" onfocus="controlar(0)" onblur="atualizar_cep();controlar(1)">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>Endereço / N.º / Comp.:</b>
		</td>
		<td>
			<b>Bairro:</b>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_endereco' value='<?=$endereco;?>' size='30' maxlength='50' title='Endereço' class="textdisabled" disabled>&nbsp;
			<input type="text" name="txt_numero" value='<?=$numero;?>' size="4" maxlength="15" class="caixadetexto" title="Digite o Número" onkeyup="verifica(this, 'aceita', 'numeros', '', event)">&nbsp;
			<input type="text" name="txt_complemento" value='<?=$complemento;?>' size="9" maxlength="10" class="caixadetexto" title="Digite o Complemento">
		</td>
		<td>
			<input type='text' name='txt_bairro' value='<?=$bairro;?>' size='30' maxlength='20' title='Bairro' class="textdisabled" disabled>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Cidade:</b></td>
		<td><b>Estado:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_cidade' value='<?=$cidade;?>' size='20' title='Cidade' class="textdisabled" disabled>
		</td>
		<td>
			<?
				$sql = "Select sigla 
					from ufs 
					where id_uf = '$id_uf' limit 1 ";
				$campos2 = bancos::sql($sql);
			?>
			<input type="text" name="txt_estado" value='<?=$campos2[0]['sigla'];?>' size="2" title="Estado" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>DDD Residencial:</td>
		<td><b>Telefone Residencial:</b></td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_ddd_residencial' value='<?=$ddd_residencial;?>' maxlength='3' size='10' title='Digite o DDD Residencial' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_telefone_residencial' value='<?=$telefone_residencial;?>' maxlength='9' size='20' title='Digite o Telefone Residencial' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td>DDD Celular:</td>
		<td>Telefone Celular:</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<input type='text' name='txt_ddd_celular' value='<?=$ddd_celular;?>' maxlength='3' size='10' title='Digite o DDD Celular' class="caixadetexto">
		</td>
		<td>
			<input type='text' name='txt_telefone_celular' value='<?=$telefone_celular;?>' maxlength='9' size='20' title='Digite o Telefone Celular' class="caixadetexto">
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2">Email Particular:</td>
	</tr>
	<tr class="linhanormal" >
		<td colspan="2">
			<input type="text" name="txt_email_particular" value='<?=$email_particular;?>' size="35" maxlength="50" title="Digite o Email Particular" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>Descrição:</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<textarea name='txt_descricao' cols='67' title='Digite a Descrição' class="caixadetexto"><?=$descricao;?></textarea>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.parent.location = 'alterar2.php<?=$parametro;?>'" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');pais_abilita();document.form.txt_nome.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>