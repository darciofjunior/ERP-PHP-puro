<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/incluir.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>REPRESENTANTE INCLU�DO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>REPRESENTANTE J� EXISTENTE.</font>";

if(!empty($_POST['txt_nome_representante'])) {
    $sql = "SELECT id_representante 
            FROM `representantes` 
            WHERE (`nome_fantasia` LIKE '%$_POST[txt_nome_fantasia]%') 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N�o encontrou o representante
/*********************************Controle com os Checkbox*********************************/
        if(empty($_POST['chkt_descontar_ir']))          $_POST['chkt_descontar_ir'] = 'N';
        if(empty($_POST['chkt_pgto_comissao_grupo'])) 	$_POST['chkt_pgto_comissao_grupo'] = 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
        $cmb_pais           = (!empty($_POST[cmb_pais])) ? "'".$_POST[cmb_pais]."'" : 'NULL';
        $cmb_pais_corresp   = (!empty($_POST[cmb_pais_corresp])) ? "'".$_POST[cmb_pais_corresp]."'" : 'NULL';
        
        $sql = "INSERT INTO `representantes` (`id_representante`, `id_pais`, `id_pais_corresp`, `nome_representante`, `nome_fantasia`, `descontar_ir`, `pgto_comissao_grupo`, `endereco`, `num_comp`, `cep`, `bairro`, `cidade`, `uf`, `fone`, `fax`, `socios`, `contato`, `tipo_pessoa`, `cnpj_cpf`, `insc_estadual`, `insc_municipal`, `core`, `tipo_firma`, `zona_atuacao`, `banco`, `agencia`, `conta_corrente`, `correntista`, `end_corresp`, `num_comp_corresp`, `cep_corresp`, `cidade_corresp`, `bairro_corresp`, `uf_corresp`, `empresa`, `email`, `observacao`, `ativo`) VALUES (NULL, $cmb_pais, $cmb_pais_corresp, '$_POST[txt_nome_representante]', '$_POST[txt_nome_fantasia]', '$_POST[chkt_descontar_ir]', '$_POST[chkt_pgto_comissao_grupo]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_cep]', '$_POST[txt_bairro]', '$_POST[txt_cidade]', '$_POST[txt_estado]', '$_POST[txt_fone]', '$_POST[txt_cel_fax]', '$_POST[txt_socio]', '$_POST[txt_contato]', '$_POST[cmb_tipo_pessoa]', '$_POST[txt_cnpj_cpf]', '$_POST[txt_inscricao_estadual]', '$_POST[txt_insc_municipal]', '$_POST[txt_core]', '$_POST[txt_tipo_firma]', '$_POST[txt_zona_atuacao]', '$_POST[txt_banco]', '$_POST[txt_agencia]', '$_POST[txt_conta_corrente]', '$_POST[txt_correntista]' ,'$_POST[txt_endereco_corresp]', '$_POST[txt_num_complemento_corresp]', '$_POST[txt_cep_corresp]', '$_POST[txt_cidade_corresp]', '$_POST[txt_bairro_corresp]', '$_POST[txt_estado_corresp]', '$_POST[txt_empresa]', '$_POST[txt_email]', '$_POST[txt_observacao]', '1') ";
        bancos::sql($sql);
        $id_representante = bancos::id_registro();
        
        if(!empty($_POST['id_func_selecionado'])) {
            //Verifico o funcion�rio na tabela Relacional de "representantes_vs_funcionarios" ...
            $sql = "SELECT id_funcionario 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_funcionario` = '$_POST[id_func_selecionado]' ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                //Insere o Funcion�rio na tabela Relacional de "representantes_vs_funcionarios" ...
                $sql = "INSERT INTO `representantes_vs_funcionarios` (`id_representante_funcionario`, `id_representante`, `id_funcionario`) VALUES (NULL, '$id_representante', '$_POST[id_func_selecionado]') ";
                bancos::sql($sql);
            }
        }else {//Aut�nomo
            //Insere o Supervisor na tabela Relacional de "representantes_vs_funcionarios" ...
            $sql = "INSERT INTO `representantes_vs_funcionarios` (`id_representante_funcionario`, `id_representante_supervisor`, `id_representante`) VALUES (NULL, '$_POST[cmb_supervisor]', '$id_representante') ";
            bancos::sql($sql);
            genericas::atualizar_representantes_no_site_area_cliente($id_representante);
        }
        $valor = 1;
    }else {//Encontrou o representante
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(document.form.opt_opcao[1].checked == true) {//Aut�nomo
//Supervisor
        if(document.form.cmb_supervisor.value == '') {
            if(!combo('form', 'cmb_supervisor', '', 'SELECIONE O SUPERVISOR !')) {
                return false
            }
        }
    }
//Nome do Representante
    if(document.form.txt_nome_representante.disabled == false) {
        if(!texto('form', 'txt_nome_representante', '1', 'qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM����������������������������[].,&()|_-0123456789 ', 'NOME DO REPRESENTANTE', '2')) {
            return false
        }
    }else {
        if(document.form.txt_nome_representante.value == '') {
            alert('DIGITE O NOME DO REPRESENTANTE !')
            document.form.cmd_funcionario.focus()
            return false
        }
    }
//Nome Fantasia
    if(!texto('form', 'txt_nome_fantasia', '1', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'NOME FANTASIA', '2')) {
        return false
    }

    if(document.form.opt_opcao[1].checked == true) {//Aut�nomo
//Se o Pa�s for Brasil, ent�o for�a o preenchimento de CEP
        if(document.form.cmb_pais.value == 31) {
//Cep
            if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
                return false
            }
//N�mero / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-�{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,()��� ", 'N�MERO / COMPLEMENTO', '2')) {
                return false
            }
        }else {
//Endere�o
            if(document.form.txt_endereco.value != '') {
                if(!texto('form', 'txt_endereco', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'ENDERE�O', '2')) {
                    return false
                }
            }
//N�mero / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-�{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,()��� ", 'N�MERO / COMPLEMENTO', '2')) {
                return false
            }
//Bairro
            if(document.form.txt_bairro.value != '') {
                if(!texto('form', 'txt_bairro', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'BAIRRO', '2')) {
                    return false
                }
            }
//Cidade
            if(document.form.txt_cidade.value != '') {
                if(!texto('form', 'txt_cidade', '3', '-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,".����������������������������{[]}.,%&*$()@#<>���:;\/ ', 'CIDADE', '1')) {
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
//Fone
        if(document.form.txt_fone.value != '') {
            if(!texto('form', 'txt_fone', '7', '()1234567890-/ ', 'FONE', '2')) {
                return false
            }
        }
//Cel / Fax
        if(document.form.txt_cel_fax.value != '') {
            if(!texto('form', 'txt_cel_fax', '7', '()1234567890-/ ', 'CEL / FAX', '2')) {
                return false
            }
        }
//S�cios
        if(document.form.txt_socio.value != '') {
            if(!texto('form', 'txt_socio', '1', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'SOCIO', '2')) {
                return false
            }
        }
//Contato
        if(document.form.txt_contato.value != '') {
            if(!texto('form', 'txt_contato', '1', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'CONTATO', '2')) {
                return false
            }
        }
//CNPJ ou CPF
        if(document.form.txt_cnpj_cpf.value != '') {
            nro = document.form.txt_cnpj_cpf.value
            for(i = 0; i < nro.length; i++) {
                letra = nro.charAt(i)
                if((letra == '.') || (letra == '/') || (letra == '-')) nro = nro.replace(letra, '')
            }
            document.form.txt_cnpj_cpf.value = nro
            if(nro.length > 11) {//CNPJ
                if (!cnpj('form', 'txt_cnpj_cpf')) {
                    return false
                }
            }else {//CPF
                if (!cpf('form', 'txt_cnpj_cpf')) {
                    return false
                }
            }
        }
//Inscri��o Estadual
        if(document.form.txt_inscricao_estadual.value != '') {
            if(!texto('form', 'txt_inscricao_estadual', '3', '0123456789', 'INSCRI��O ESTADUAL', '1')) {
                return false
            }
        }
//Inscri��o Municipal
        if(document.form.txt_insc_municipal.value != '') {
            if(!texto('form', 'txt_insc_municipal', '3', '0123456789', 'INSCRI��O MUNICIPAL', '1')) {
                return false
            }
        }
//Tipo de Firma
        if(document.form.txt_tipo_firma.value != '') {
            if(!texto('form', 'txt_tipo_firma', '3', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'TIPO DE FIRMA', '2')) {
                return false
            }
        }
//Zona de Atua��o
        if(document.form.txt_zona_atuacao.value != '') {
            if(!texto('form', 'txt_zona_atuacao', '3', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'ZONA DE ATUA��O', '1')) {
                return false
            }
        }
//Banco ...
        if(document.form.txt_banco.value != '') {
            if(!texto('form', 'txt_banco', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ��������������������������-_.(), ', 'BANCO', '2')) {
                return false
            }
        }
//Ag�ncia ...
        if(document.form.txt_agencia.value != '') {
            if(!texto('form', 'txt_agencia', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ��������������������������-_.(), ', 'AG�NCIA', '1')) {
                return false
            }
        }
//Conta Corrente ...
        if(document.form.txt_conta_corrente.value != '') {
            if(!texto('form', 'txt_conta_corrente', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ��������������������������-_.(), ', 'CONTA CORRENTE', '1')) {
                return false
            }
        }
//Correntista ...
        if(document.form.txt_correntista.value != '') {
            if(!texto('form', 'txt_correntista', '3', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'CORRENTISTA', '2')) {
                return false
            }
        }
    }
//Valida��o dos Campos de Correspond�ncia
    if(document.form.cmb_pais_corresp.value != '') {
//Se o Pa�s for Brasil, ent�o for�a o preenchimento de CEP
        if(document.form.cmb_pais_corresp.value == 31) {
//Cep de Correspond�ncia
            if(document.form.txt_cep_corresp.value != '') {
                if(!texto('form', 'txt_cep_corresp', '9', '-1234567890', 'CEP DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
//N�mero / Complemento de Correspond�ncia
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-�{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,()��� ", 'N�MERO / COMPLEMENTO DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
//Pa�s Internacional
        }else {
//Endere�o de Correspond�ncia
            if(document.form.txt_endereco_corresp.value != '') {
                if(!texto('form', 'txt_endereco_corresp', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'ENDERE�O DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
//N�mero / Complemento de Correspond�ncia
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-�{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,()��� ", 'N�MERO / COMPLEMENTO DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
//Bairro de Correspond�ncia
            if(document.form.txt_bairro_corresp.value != '') {
                if(!texto('form', 'txt_bairro_corresp', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'BAIRRO DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
//Cidade de Correspond�ncia
            if(document.form.txt_cidade_corresp.value != '') {
                if(!texto('form', 'txt_cidade_corresp', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'CIDADE DE CORRESPOND�NCIA', '1')) {
                    return false
                }
            }
//Estado de Correspond�ncia
            if(document.form.txt_estado_corresp.value != '') {
                if(!texto('form', 'txt_estado_corresp', '2', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'ESTADO DE CORRESPOND�NCIA', '2')) {
                    return false
                }
            }
        }
    }
//Core
    if(document.form.txt_core.value != '') {
        if(!texto('form', 'txt_core', '3', "-=!@������{}1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM,'.����������������������������{[]}.,%&*$()@#<>���:;\/ ", 'CORE', '2')) {
            return false
        }
    }
//Empresa
    if(document.form.txt_empresa.value != '') {
        if(!texto('form', 'txt_empresa', '1', '-qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�J.|HGFDSAZXCVBNM����������������������������.,&(): ', 'EMPRESA', '1')) {
            return false
        }
    }
//E-mail
    if(document.form.opt_opcao[1].checked == true) {//Representante Aut�nomo ...
        if(!new_email('form', 'txt_email')) {
            return false
        }
    }
//Tipo de Pessoa
    if(document.form.opt_opcao[1].checked == true) {//Aut�nomo
        if(!combo('form', 'cmb_tipo_pessoa', '', 'SELECIONE O TIPO DE PESSOA !')) {
            return false
        }
    }
//Aqui nessa parte se faz o tratamento das caixas de texto para poder gravar no bd
    var elementos = document.form.elements
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
/*Verifica se no nome tem um s�mbolo de colchete para saber se j� s�o os texts
de arrays de desconto de clientes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras
                elementos[i].value = strtofloat(elementos[i].value)
            }
        }
    }
    //Aqui serve para n�o submeter
    if(document.form.controle.value == 0) return false
//Desabilita para poder gravar no Banco de Dados
    if(document.form.opt_opcao[1].checked == true) {//Aut�nomo
//Endere�o Normal
        document.form.txt_endereco.disabled = false
        document.form.txt_bairro.disabled = false
        document.form.txt_cidade.disabled = false
        document.form.txt_estado.disabled = false
//Converte o endere�o e o bairro para mai�sculo para ficar mais organizado
        document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
        document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
        document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
        document.form.txt_estado.value = document.form.txt_estado.value.toUpperCase()
    }
//Endere�o de Correspond�ncia
    document.form.txt_endereco_corresp.disabled = false
    document.form.txt_bairro_corresp.disabled = false
    document.form.txt_cidade_corresp.disabled = false
    document.form.txt_estado_corresp.disabled = false
//Converte o endere�o e o bairro para mai�sculo para ficar mais organizado
    document.form.txt_endereco_corresp.value = document.form.txt_endereco_corresp.value.toUpperCase()
    document.form.txt_bairro_corresp.value = document.form.txt_bairro_corresp.value.toUpperCase()
    document.form.txt_cidade_corresp.value = document.form.txt_cidade_corresp.value.toUpperCase()
    document.form.txt_estado_corresp.value = document.form.txt_estado_corresp.value.toUpperCase()
    //Deixa em formato de gravar no BD ...
    document.form.txt_nome_representante.disabled = false
//Travo o bot�o p/ n�o correr o risco de submeter os dados 2 vezes p/ o BD de Dados ...
    document.form.cmd_salvar.disabled = true
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep(valor) {
    if(valor == 1) {//Endere�o Normal
        var id_pais = document.form.cmb_pais.value
        var txt_cep = document.form.txt_cep.value
        if(txt_cep == '') {//� v�zio
            document.form.txt_endereco.value = '<?=$endereco;?>'
            document.form.txt_bairro.value = '<?=$bairro;?>'
            document.form.txt_cidade.value = '<?=$cidade;?>'
            document.form.txt_estado.value = '<?=$estado?>'
        }else {//N�o � v�zio
//Verifica se o CEP � v�lido
            if(txt_cep.length < 9) {
                alert('CEP INV�LIDO !')
                document.form.txt_cep.focus()
                document.form.txt_cep.select()
                return false
            }
            if(id_pais == 31) {//S� buscar� o CEP se for Brasil
                window.parent.cep.location = 'buscar_cep.php?txt_cep='+txt_cep
            }
        }
    }else {//Endere�o de Correspond�ncia
        var id_pais_corresp = document.form.cmb_pais_corresp.value
        var txt_cep_corresp = document.form.txt_cep_corresp.value
        if(txt_cep_corresp == '') {//� v�zio
            document.form.txt_endereco_corresp.value = '<?=$endereco_corresp;?>'
            document.form.txt_bairro_corresp.value = '<?=$bairro_corresp;?>'
            document.form.txt_cidade_corresp.value = '<?=$cidade_corresp;?>'
            document.form.txt_estado_corresp.value = '<?=$estado_corresp;?>'
        }else {//N�o � v�zio
//Verifica se o CEP de Correspond�ncia � v�lido
            if(txt_cep_corresp.length < 9) {
                alert('CEP DE CORRESPOND�NCIA INV�LIDO !')
                document.form.txt_cep_corresp.focus()
                document.form.txt_cep_corresp.select()
                return false
            }
            if(id_pais_corresp == 31) {//S� buscar� o CEP se for Brasil
                window.parent.cep.location = 'buscar_cep.php?txt_cep_corresp='+txt_cep_corresp
            }
        }
    }
}

//Fun��o que controla para n�o submeter
function controlar(valor) {
    document.form.controle.value = valor
}

function habilitar_desabilitar() {
    if(document.form.opt_opcao[0].checked == true) {//Funcion�rio ...
        with(document.form) {
            cmd_funcionario.disabled        = false//Habilita Bot�o
            cmd_funcionario.className       = 'botao'
            cmb_supervisor.disabled         = true
            cmb_supervisor.className        = 'textdisabled'
            //Designer de Desabilitado ...
            txt_nome_representante.className= 'textdisabled'
            cmb_pais.className              = 'textdisabled'
            txt_cep.className               = 'textdisabled'
            txt_num_complemento.className   = 'textdisabled'
            txt_fone.className              = 'textdisabled'
            txt_cel_fax.className           = 'textdisabled'
            txt_socio.className             = 'textdisabled'
            txt_contato.className           = 'textdisabled'
            txt_cnpj_cpf.className          = 'textdisabled'
            txt_inscricao_estadual.className= 'textdisabled'
            txt_insc_municipal.className    = 'textdisabled'
            txt_tipo_firma.className        = 'textdisabled'
            txt_zona_atuacao.className      = 'textdisabled'
            txt_banco.className             = 'textdisabled'
            txt_agencia.className           = 'textdisabled'
            txt_conta_corrente.className    = 'textdisabled'
            txt_correntista.className       = 'textdisabled'
            cmb_pais_corresp.className      = 'textdisabled'
            txt_cep_corresp.className       = 'textdisabled'
            txt_num_complemento_corresp.className = 'textdisabled'
            txt_core.className              = 'textdisabled'
            txt_empresa.className           = 'textdisabled'
            txt_email.className             = 'textdisabled'
            cmb_tipo_pessoa.className       = 'textdisabled'
            //Desabilita os Objetos ...
            txt_nome_representante.disabled = true
            cmb_pais.disabled               = true
            txt_cep.disabled                = true
            txt_num_complemento.disabled    = true
            txt_fone.disabled               = true
            txt_cel_fax.disabled            = true
            txt_socio.disabled              = true
            txt_contato.disabled            = true
            txt_cnpj_cpf.disabled           = true
            txt_inscricao_estadual.disabled = true
            txt_insc_municipal.disabled     = true
            txt_tipo_firma.disabled         = true
            txt_zona_atuacao.disabled       = true
            txt_banco.disabled              = true
            txt_agencia.disabled            = true
            txt_conta_corrente.disabled     = true
            txt_correntista.disabled        = true
            cmb_pais_corresp.disabled       = true
            txt_cep_corresp.disabled        = true
            txt_num_complemento_corresp.disabled = true
            txt_core.disabled               = true
            txt_empresa.disabled            = true
            txt_email.disabled              = true
            cmb_tipo_pessoa.disabled        = true
        }
    }else {//Representante ...
        with(document.form) {
            cmd_funcionario.disabled        = true//Desabilita Bot�o
            cmd_funcionario.className       = 'textdisabled'
            cmb_supervisor.disabled         = false
            cmb_supervisor.className        = 'caixadetexto'
            //Designer de Habilitado ...
            txt_nome_representante.className= 'caixadetexto'
            cmb_pais.className              = 'caixadetexto'
            txt_cep.className               = 'caixadetexto'
            txt_num_complemento.className   = 'caixadetexto'
            txt_fone.className              = 'caixadetexto'
            txt_cel_fax.className           = 'caixadetexto'
            txt_socio.className             = 'caixadetexto'
            txt_contato.className           = 'caixadetexto'
            txt_cnpj_cpf.className          = 'caixadetexto'
            txt_inscricao_estadual.className= 'caixadetexto'
            txt_insc_municipal.className    = 'caixadetexto'
            txt_tipo_firma.className        = 'caixadetexto'
            txt_zona_atuacao.className      = 'caixadetexto'
            txt_banco.className             = 'caixadetexto'
            txt_agencia.className           = 'caixadetexto'
            txt_conta_corrente.className    = 'caixadetexto'
            txt_correntista.className       = 'caixadetexto'
            cmb_pais_corresp.className      = 'caixadetexto'
            txt_cep_corresp.className       = 'caixadetexto'
            txt_num_complemento_corresp.className= 'caixadetexto'
            txt_core.className              = 'caixadetexto'
            txt_empresa.className           = 'caixadetexto'
            txt_email.className             = 'caixadetexto'
            cmb_tipo_pessoa.className       = 'caixadetexto'
            //Habilita os Objetos ...
            txt_nome_representante.disabled = false
            cmb_pais.disabled               = false
            txt_cep.disabled                = false
            txt_num_complemento.disabled    = false
            txt_fone.disabled               = false
            txt_cel_fax.disabled            = false
            txt_socio.disabled              = false
            txt_contato.disabled            = false
            txt_cnpj_cpf.disabled           = false
            txt_inscricao_estadual.disabled = false
            txt_insc_municipal.disabled     = false
            txt_tipo_firma.disabled         = false
            txt_zona_atuacao.disabled       = false
            txt_banco.disabled              = false
            txt_agencia.disabled            = false
            txt_conta_corrente.disabled     = false
            txt_correntista.disabled        = false
            cmb_pais_corresp.disabled       = false
            txt_cep_corresp.disabled        = false
            txt_num_complemento_corresp.disabled    = false
            txt_core.disabled               = false
            txt_empresa.disabled            = false
            txt_email.disabled              = false
            cmb_tipo_pessoa.disabled        = false
        }
    }
    pais_habilita()
    pais_habilita_corresp()
}

function pais_habilita() {
    if(document.form.cmb_pais.value == 31) {//Brasil ...
        with(document.form) {
            //Trocando a Cor para Desabilitado
            txt_endereco.className  = 'textdisabled'
            txt_bairro.className    = 'textdisabled'
            txt_cidade.className    = 'textdisabled'
            txt_estado.className    = 'textdisabled'
            //Desabilitando
            txt_endereco.disabled   = true
            txt_bairro.disabled     = true
            txt_cidade.disabled     = true
            txt_estado.disabled     = true
        }
    }else {//Internacional ...
        with(document.form) {
            //Trocando a Cor para Halibitado ...
            txt_endereco.className  = 'caixadetexto'
            txt_bairro.className    = 'caixadetexto'
            txt_cidade.className    = 'caixadetexto'
            txt_estado.className    = 'caixadetexto'
            //Habilitando
            txt_endereco.disabled   = false
            txt_bairro.disabled     = false
            txt_cidade.disabled     = false
            txt_estado.disabled     = false
        }
    }
}

function pais_habilita_corresp() {
    if(document.form.cmb_pais_corresp.value == 31) {//Brasil ...
        with(document.form) {
            //Trocando a Cor para Desabilitado
            txt_endereco_corresp.className  = 'textdisabled'
            txt_bairro_corresp.className    = 'textdisabled'
            txt_cidade_corresp.className    = 'textdisabled'
            txt_estado_corresp.className    = 'textdisabled'
            //Desabilitando
            txt_endereco_corresp.disabled   = true
            txt_bairro_corresp.disabled     = true
            txt_cidade_corresp.disabled     = true
            txt_estado_corresp.disabled     = true
        }
    }else {//Internacional ...
        with(document.form) {
            //Trocando a Cor para Habilitado
            txt_endereco_corresp.className  = 'textdisabled'
            txt_bairro_corresp.className    = 'textdisabled'
            txt_cidade_corresp.className    = 'textdisabled'
            txt_estado_corresp.className    = 'textdisabled'
            //Habilitando ...
            txt_endereco_corresp.disabled   = false
            txt_bairro_corresp.disabled     = false
            txt_cidade_corresp.disabled     = false
            txt_estado_corresp.disabled     = false
        }
    }
}

function copiar_cotas() {
    var elementos = document.form.elements
    var contador = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
            //Verifica se no nome tem um s�mbolo de colchete para saber se j� s�o os texts de arrays de desconto de clientes ...
            if(elementos[i].name.indexOf('[') != -1) {
                //Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras ...
                if(contador == 0) {
                    valor_caixa = elementos[i].value
                    contador++
                }else {
                    elementos[i].value = valor_caixa
                }
            }
        }
    }
}
</Script>
</head>
<body onload='pais_habilita();pais_habilita_corresp();document.form.txt_nome_representante.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_func_selecionado'>
<!--Caixa que faz controle para submeter a tela de Cliente-->
<input type='hidden' name='controle' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Representante(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Representante:</b>
        </td>
        <td>
            <b>Supervisor:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' id='opt1' onclick='habilitar_desabilitar()'>
            <label for='opt1'>Funcion&aacute;rio</label>
            <input type='radio' name='opt_opcao' value='2' id='opt2' onclick='habilitar_desabilitar()' checked>
            <label for='opt2'>Aut�nomo</label>
        </td>
        <td>
            <select name='cmb_supervisor' title='Selecione o Supervisor' class='combo'>
            <?
//S� seleciona funcion�rios que s�o Representantes, mas que s�o do Tipo Supervisores
                $sql = "SELECT r.id_representante, f.nome 
                        FROM `funcionarios` f 
                        INNER JOIN `representantes_vs_funcionarios` rf ON rf.`id_funcionario` = f.`id_funcionario` 
                        INNER JOIN `representantes` r ON r.`id_representante` = rf.`id_representante` 
                        WHERE f.`id_cargo` IN (25, 109) ORDER BY f.nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Nome do Representante:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_nome_representante' title='Digite o Nome do Representante' size='32' maxlength='85' class='caixadetexto'>
            <!--Vari�vel incluir dentro do bot�o representante -> indica que a tela � incluir-->
            <input type='button' name='cmd_funcionario' value='Buscar Funcion�rio' title='Buscar Funcion�rio' onclick="html5Lightbox.showLightbox(7, 'consultar_funcionario.php')" class='botao' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome Fantasia:</b>
        </td>
        <td>
            Cargo:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite o Nome Fantasia' size='40' maxlength="85" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_cargo' title='Cargo' size='40' maxlength='85' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Controle(s) Extra(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_descontar_ir' value='S' title='Descontar Imposto de Renda (Alba / Tool)' id='label' class='checkbox'>
            <label for='label'>
                Descontar Imposto de Renda (Alba / Tool)
            </label>
        </td>
        <td>
            <input type='checkbox' name='chkt_pgto_comissao_grupo' value='S' title='Descontar Pagamento de Comiss�o pelo Grupo' id='label1' class='checkbox'>
            <label for='label1'>
                Pagamento de Comiss�o pelo Grupo
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pa�s:
        </td>
        <td>
            CEP:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais' onchange='pais_habilita()' class='combo'>
            <?
                $sql = "SELECT id_pais, pais 
                        FROM `paises` ";
                echo combos::combo($sql, 31);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_cep' title='Digite o Cep' size='12' maxlength='9' onkeyup="verifica(this, 'cep', '', '', event)" onfocus='controlar(0)' onblur='buscar_cep(1);controlar(1)' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Endere�o:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.� / Complemento
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco' title='Endere�o' size='35' maxlength='50' class='textdisabled' disabled>
            &nbsp;
            <input type='text' name='txt_num_complemento' title='N�mero, Complemento, ...' size='21' maxlength='20' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro' size='50' title='Bairro' class='textdisabled' disabled>
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
            <input type='text' name='txt_cidade' title='Cidade' size='35' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_estado' title='Estado' size='35' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fone:
        </td>
        <td>
            Cel / Fax:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_fone' title='Digite o Fone' size='15' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_cel_fax' title='Digite o Cel / Fax' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            S&oacute;cios:
        </td>
        <td>
            Contato:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_socio' title='Digite o S�cio' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_contato' title='Digite o Contato' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ / CPF:
        </td>
        <td>
            Inscri&ccedil;&atilde;o Estadual:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ / CPF' size='26' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_inscricao_estadual' title='Digite a Inscri��o Estadual' size='18' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Inscri&ccedil;&atilde;o Municipal:
        </td>
        <td>
            Tipo de Firma:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_insc_municipal' title='Digite a Inscri��o Municipal' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_tipo_firma' title='Digite o Tipo de Firma' size='52' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Zona de Atua&ccedil;&atilde;o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_zona_atuacao' title='Digite a Zona de Atua��o' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados Banc�rios
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Banco:
        </td>
        <td>
            Ag&ecirc;ncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_banco' title='Digite o Banco' size='35' maxlength='30' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_agencia' title='Digite a Ag�ncia' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Corrente:
        </td>
        <td>
            Correntista:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_conta_corrente' title='Digite a Conta Corrente' size='35' maxlength='30' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_correntista' title='Digite o Correntista' size='65' maxlength='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pa�s de Correspond�ncia:
        </td>
        <td>
            CEP de Correspond�ncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais_corresp' onchange='pais_habilita_corresp()' class='combo'>
            <?
                $sql = "SELECT id_pais, pais 
                        FROM `paises` ";
                echo combos::combo($sql, 31);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_cep_corresp' title='Digite o Cep' size='12' maxlength='9' onkeyup="verifica(this, 'cep', '', '', event)" onfocus='controlar(0)' onblur='buscar_cep(2);controlar(1)' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Endere&ccedil;o de Correspond�ncia:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.� / Complemento
        </td>
        <td>
            Bairro de Correspond�ncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco_corresp' size='35' maxlength='50' title='Endere�o de Correspond�ncia' class='textdisabled' disabled>
            &nbsp;
            <input type='text' name='txt_num_complemento_corresp' title='N�mero, Complemento, ... de Correspond�ncia' size='21' maxlength='20' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro_corresp' title='Bairro de Correspond�ncia' size='50' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade de Correspond�ncia:
        </td>
        <td>
            Estado de Correspond�ncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cidade_corresp' title='Cidade de Correspond�ncia' size='35' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_estado_corresp' title='Estado de Correspond�ncia' size='35' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Core:
        </td>
        <td>
            Empresa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_core' title='Digite o Core' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_empresa' title='Digite a Empresa' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            E-mail:
        </td>
        <td>
            Tipo de Pessoa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_email' title='Digite o E-mail' size='35' class='caixadetexto'>
        </td>
        <td>
            <select name='cmb_tipo_pessoa' title='Selecione o Tipo de Pessoa' class='combo'>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='F'>PESSOA F�SICA</option>
                <option value='J'>PESSOA JUR�DICA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observa��o: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observa��o' rows='2' cols='75' maxlength='150' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');habilitar_desabilitar();pais_habilita();pais_habilita_corresp();document.form.txt_nome_representante.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* A combo <b>SUPERVISOR</b>, s� lista Representantes que s�o Funcion�rios.
</pre>
</pre>