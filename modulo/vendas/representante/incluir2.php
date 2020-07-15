<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/incluir.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>REPRESENTANTE INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>REPRESENTANTE JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_nome_representante'])) {
    $sql = "SELECT id_representante 
            FROM `representantes` 
            WHERE (`nome_fantasia` LIKE '%$_POST[txt_nome_fantasia]%') 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não encontrou o representante
/*********************************Controle com os Checkbox*********************************/
        if(empty($_POST['chkt_descontar_ir']))          $_POST['chkt_descontar_ir'] = 'N';
        if(empty($_POST['chkt_pgto_comissao_grupo'])) 	$_POST['chkt_pgto_comissao_grupo'] = 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_pais           = (!empty($_POST[cmb_pais])) ? "'".$_POST[cmb_pais]."'" : 'NULL';
        $cmb_pais_corresp   = (!empty($_POST[cmb_pais_corresp])) ? "'".$_POST[cmb_pais_corresp]."'" : 'NULL';
        
        $sql = "INSERT INTO `representantes` (`id_representante`, `id_pais`, `id_pais_corresp`, `nome_representante`, `nome_fantasia`, `descontar_ir`, `pgto_comissao_grupo`, `endereco`, `num_comp`, `cep`, `bairro`, `cidade`, `uf`, `fone`, `fax`, `socios`, `contato`, `tipo_pessoa`, `cnpj_cpf`, `insc_estadual`, `insc_municipal`, `core`, `tipo_firma`, `zona_atuacao`, `banco`, `agencia`, `conta_corrente`, `correntista`, `end_corresp`, `num_comp_corresp`, `cep_corresp`, `cidade_corresp`, `bairro_corresp`, `uf_corresp`, `empresa`, `email`, `observacao`, `ativo`) VALUES (NULL, $cmb_pais, $cmb_pais_corresp, '$_POST[txt_nome_representante]', '$_POST[txt_nome_fantasia]', '$_POST[chkt_descontar_ir]', '$_POST[chkt_pgto_comissao_grupo]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_cep]', '$_POST[txt_bairro]', '$_POST[txt_cidade]', '$_POST[txt_estado]', '$_POST[txt_fone]', '$_POST[txt_cel_fax]', '$_POST[txt_socio]', '$_POST[txt_contato]', '$_POST[cmb_tipo_pessoa]', '$_POST[txt_cnpj_cpf]', '$_POST[txt_inscricao_estadual]', '$_POST[txt_insc_municipal]', '$_POST[txt_core]', '$_POST[txt_tipo_firma]', '$_POST[txt_zona_atuacao]', '$_POST[txt_banco]', '$_POST[txt_agencia]', '$_POST[txt_conta_corrente]', '$_POST[txt_correntista]' ,'$_POST[txt_endereco_corresp]', '$_POST[txt_num_complemento_corresp]', '$_POST[txt_cep_corresp]', '$_POST[txt_cidade_corresp]', '$_POST[txt_bairro_corresp]', '$_POST[txt_estado_corresp]', '$_POST[txt_empresa]', '$_POST[txt_email]', '$_POST[txt_observacao]', '1') ";
        bancos::sql($sql);
        $id_representante = bancos::id_registro();
        
        if(!empty($_POST['id_func_selecionado'])) {
            //Verifico o funcionário na tabela Relacional de "representantes_vs_funcionarios" ...
            $sql = "SELECT id_funcionario 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_funcionario` = '$_POST[id_func_selecionado]' ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                //Insere o Funcionário na tabela Relacional de "representantes_vs_funcionarios" ...
                $sql = "INSERT INTO `representantes_vs_funcionarios` (`id_representante_funcionario`, `id_representante`, `id_funcionario`) VALUES (NULL, '$id_representante', '$_POST[id_func_selecionado]') ";
                bancos::sql($sql);
            }
        }else {//Autônomo
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
    if(document.form.opt_opcao[1].checked == true) {//Autônomo
//Supervisor
        if(document.form.cmb_supervisor.value == '') {
            if(!combo('form', 'cmb_supervisor', '', 'SELECIONE O SUPERVISOR !')) {
                return false
            }
        }
    }
//Nome do Representante
    if(document.form.txt_nome_representante.disabled == false) {
        if(!texto('form', 'txt_nome_representante', '1', 'qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ[].,&()|_-0123456789 ', 'NOME DO REPRESENTANTE', '2')) {
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
    if(!texto('form', 'txt_nome_fantasia', '1', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'NOME FANTASIA', '2')) {
        return false
    }

    if(document.form.opt_opcao[1].checked == true) {//Autônomo
//Se o País for Brasil, então força o preenchimento de CEP
        if(document.form.cmb_pais.value == 31) {
//Cep
            if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
                return false
            }
//Número / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,()ªº° ", 'NÚMERO / COMPLEMENTO', '2')) {
                return false
            }
        }else {
//Endereço
            if(document.form.txt_endereco.value != '') {
                if(!texto('form', 'txt_endereco', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'ENDEREÇO', '2')) {
                    return false
                }
            }
//Número / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,()ªº° ", 'NÚMERO / COMPLEMENTO', '2')) {
                return false
            }
//Bairro
            if(document.form.txt_bairro.value != '') {
                if(!texto('form', 'txt_bairro', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'BAIRRO', '2')) {
                    return false
                }
            }
//Cidade
            if(document.form.txt_cidade.value != '') {
                if(!texto('form', 'txt_cidade', '3', '-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,".Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ', 'CIDADE', '1')) {
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
//Sócios
        if(document.form.txt_socio.value != '') {
            if(!texto('form', 'txt_socio', '1', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'SOCIO', '2')) {
                return false
            }
        }
//Contato
        if(document.form.txt_contato.value != '') {
            if(!texto('form', 'txt_contato', '1', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'CONTATO', '2')) {
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
//Inscrição Estadual
        if(document.form.txt_inscricao_estadual.value != '') {
            if(!texto('form', 'txt_inscricao_estadual', '3', '0123456789', 'INSCRIÇÃO ESTADUAL', '1')) {
                return false
            }
        }
//Inscrição Municipal
        if(document.form.txt_insc_municipal.value != '') {
            if(!texto('form', 'txt_insc_municipal', '3', '0123456789', 'INSCRIÇÃO MUNICIPAL', '1')) {
                return false
            }
        }
//Tipo de Firma
        if(document.form.txt_tipo_firma.value != '') {
            if(!texto('form', 'txt_tipo_firma', '3', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'TIPO DE FIRMA', '2')) {
                return false
            }
        }
//Zona de Atuação
        if(document.form.txt_zona_atuacao.value != '') {
            if(!texto('form', 'txt_zona_atuacao', '3', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'ZONA DE ATUAÇÃO', '1')) {
                return false
            }
        }
//Banco ...
        if(document.form.txt_banco.value != '') {
            if(!texto('form', 'txt_banco', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇáéíóúâêîôûÂÊÎÔÛÁÉÍÓÚãõÃÕ-_.(), ', 'BANCO', '2')) {
                return false
            }
        }
//Agência ...
        if(document.form.txt_agencia.value != '') {
            if(!texto('form', 'txt_agencia', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇáéíóúâêîôûÂÊÎÔÛÁÉÍÓÚãõÃÕ-_.(), ', 'AGÊNCIA', '1')) {
                return false
            }
        }
//Conta Corrente ...
        if(document.form.txt_conta_corrente.value != '') {
            if(!texto('form', 'txt_conta_corrente', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇáéíóúâêîôûÂÊÎÔÛÁÉÍÓÚãõÃÕ-_.(), ', 'CONTA CORRENTE', '1')) {
                return false
            }
        }
//Correntista ...
        if(document.form.txt_correntista.value != '') {
            if(!texto('form', 'txt_correntista', '3', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'CORRENTISTA', '2')) {
                return false
            }
        }
    }
//Validação dos Campos de Correspondência
    if(document.form.cmb_pais_corresp.value != '') {
//Se o País for Brasil, então força o preenchimento de CEP
        if(document.form.cmb_pais_corresp.value == 31) {
//Cep de Correspondência
            if(document.form.txt_cep_corresp.value != '') {
                if(!texto('form', 'txt_cep_corresp', '9', '-1234567890', 'CEP DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
//Número / Complemento de Correspondência
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-¢{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,()ªº° ", 'NÚMERO / COMPLEMENTO DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
//País Internacional
        }else {
//Endereço de Correspondência
            if(document.form.txt_endereco_corresp.value != '') {
                if(!texto('form', 'txt_endereco_corresp', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'ENDEREÇO DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
//Número / Complemento de Correspondência
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-¢{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,()ªº° ", 'NÚMERO / COMPLEMENTO DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
//Bairro de Correspondência
            if(document.form.txt_bairro_corresp.value != '') {
                if(!texto('form', 'txt_bairro_corresp', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'BAIRRO DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
//Cidade de Correspondência
            if(document.form.txt_cidade_corresp.value != '') {
                if(!texto('form', 'txt_cidade_corresp', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CIDADE DE CORRESPONDÊNCIA', '1')) {
                    return false
                }
            }
//Estado de Correspondência
            if(document.form.txt_estado_corresp.value != '') {
                if(!texto('form', 'txt_estado_corresp', '2', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'ESTADO DE CORRESPONDÊNCIA', '2')) {
                    return false
                }
            }
        }
    }
//Core
    if(document.form.txt_core.value != '') {
        if(!texto('form', 'txt_core', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CORE', '2')) {
            return false
        }
    }
//Empresa
    if(document.form.txt_empresa.value != '') {
        if(!texto('form', 'txt_empresa', '1', '-qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNMÜüáé§íóúÁÉÍÀàÓÚâêîôûÂÊÎÔÛãõÃÕ.,&(): ', 'EMPRESA', '1')) {
            return false
        }
    }
//E-mail
    if(document.form.opt_opcao[1].checked == true) {//Representante Autônomo ...
        if(!new_email('form', 'txt_email')) {
            return false
        }
    }
//Tipo de Pessoa
    if(document.form.opt_opcao[1].checked == true) {//Autônomo
        if(!combo('form', 'cmb_tipo_pessoa', '', 'SELECIONE O TIPO DE PESSOA !')) {
            return false
        }
    }
//Aqui nessa parte se faz o tratamento das caixas de texto para poder gravar no bd
    var elementos = document.form.elements
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
/*Verifica se no nome tem um símbolo de colchete para saber se já são os texts
de arrays de desconto de clientes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras
                elementos[i].value = strtofloat(elementos[i].value)
            }
        }
    }
    //Aqui serve para não submeter
    if(document.form.controle.value == 0) return false
//Desabilita para poder gravar no Banco de Dados
    if(document.form.opt_opcao[1].checked == true) {//Autônomo
//Endereço Normal
        document.form.txt_endereco.disabled = false
        document.form.txt_bairro.disabled = false
        document.form.txt_cidade.disabled = false
        document.form.txt_estado.disabled = false
//Converte o endereço e o bairro para maiúsculo para ficar mais organizado
        document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
        document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
        document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
        document.form.txt_estado.value = document.form.txt_estado.value.toUpperCase()
    }
//Endereço de Correspondência
    document.form.txt_endereco_corresp.disabled = false
    document.form.txt_bairro_corresp.disabled = false
    document.form.txt_cidade_corresp.disabled = false
    document.form.txt_estado_corresp.disabled = false
//Converte o endereço e o bairro para maiúsculo para ficar mais organizado
    document.form.txt_endereco_corresp.value = document.form.txt_endereco_corresp.value.toUpperCase()
    document.form.txt_bairro_corresp.value = document.form.txt_bairro_corresp.value.toUpperCase()
    document.form.txt_cidade_corresp.value = document.form.txt_cidade_corresp.value.toUpperCase()
    document.form.txt_estado_corresp.value = document.form.txt_estado_corresp.value.toUpperCase()
    //Deixa em formato de gravar no BD ...
    document.form.txt_nome_representante.disabled = false
//Travo o botão p/ não correr o risco de submeter os dados 2 vezes p/ o BD de Dados ...
    document.form.cmd_salvar.disabled = true
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep(valor) {
    if(valor == 1) {//Endereço Normal
        var id_pais = document.form.cmb_pais.value
        var txt_cep = document.form.txt_cep.value
        if(txt_cep == '') {//É vázio
            document.form.txt_endereco.value = '<?=$endereco;?>'
            document.form.txt_bairro.value = '<?=$bairro;?>'
            document.form.txt_cidade.value = '<?=$cidade;?>'
            document.form.txt_estado.value = '<?=$estado?>'
        }else {//Não é vázio
//Verifica se o CEP é válido
            if(txt_cep.length < 9) {
                alert('CEP INVÁLIDO !')
                document.form.txt_cep.focus()
                document.form.txt_cep.select()
                return false
            }
            if(id_pais == 31) {//Só buscará o CEP se for Brasil
                window.parent.cep.location = 'buscar_cep.php?txt_cep='+txt_cep
            }
        }
    }else {//Endereço de Correspondência
        var id_pais_corresp = document.form.cmb_pais_corresp.value
        var txt_cep_corresp = document.form.txt_cep_corresp.value
        if(txt_cep_corresp == '') {//É vázio
            document.form.txt_endereco_corresp.value = '<?=$endereco_corresp;?>'
            document.form.txt_bairro_corresp.value = '<?=$bairro_corresp;?>'
            document.form.txt_cidade_corresp.value = '<?=$cidade_corresp;?>'
            document.form.txt_estado_corresp.value = '<?=$estado_corresp;?>'
        }else {//Não é vázio
//Verifica se o CEP de Correspondência é válido
            if(txt_cep_corresp.length < 9) {
                alert('CEP DE CORRESPONDÊNCIA INVÁLIDO !')
                document.form.txt_cep_corresp.focus()
                document.form.txt_cep_corresp.select()
                return false
            }
            if(id_pais_corresp == 31) {//Só buscará o CEP se for Brasil
                window.parent.cep.location = 'buscar_cep.php?txt_cep_corresp='+txt_cep_corresp
            }
        }
    }
}

//Função que controla para não submeter
function controlar(valor) {
    document.form.controle.value = valor
}

function habilitar_desabilitar() {
    if(document.form.opt_opcao[0].checked == true) {//Funcionário ...
        with(document.form) {
            cmd_funcionario.disabled        = false//Habilita Botão
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
            cmd_funcionario.disabled        = true//Desabilita Botão
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
            //Verifica se no nome tem um símbolo de colchete para saber se já são os texts de arrays de desconto de clientes ...
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
            <label for='opt2'>Autônomo</label>
        </td>
        <td>
            <select name='cmb_supervisor' title='Selecione o Supervisor' class='combo'>
            <?
//Só seleciona funcionários que são Representantes, mas que são do Tipo Supervisores
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
            <!--Variável incluir dentro do botão representante -> indica que a tela é incluir-->
            <input type='button' name='cmd_funcionario' value='Buscar Funcionário' title='Buscar Funcionário' onclick="html5Lightbox.showLightbox(7, 'consultar_funcionario.php')" class='botao' disabled>
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
            <input type='checkbox' name='chkt_pgto_comissao_grupo' value='S' title='Descontar Pagamento de Comissão pelo Grupo' id='label1' class='checkbox'>
            <label for='label1'>
                Pagamento de Comissão pelo Grupo
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
            País:
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
            Endereço:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.º / Complemento
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco' title='Endereço' size='35' maxlength='50' class='textdisabled' disabled>
            &nbsp;
            <input type='text' name='txt_num_complemento' title='Número, Complemento, ...' size='21' maxlength='20' class='caixadetexto'>
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
            <input type='text' name='txt_socio' title='Digite o Sócio' class='caixadetexto'>
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
            <input type='text' name='txt_inscricao_estadual' title='Digite a Inscrição Estadual' size='18' class='caixadetexto'>
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
            <input type='text' name='txt_insc_municipal' title='Digite a Inscrição Municipal' class='caixadetexto'>
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
            <input type='text' name='txt_zona_atuacao' title='Digite a Zona de Atuação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados Bancários
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
            <input type='text' name='txt_agencia' title='Digite a Agência' size='35' maxlength='30' class='caixadetexto'>
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
            País de Correspondência:
        </td>
        <td>
            CEP de Correspondência:
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
            Endere&ccedil;o de Correspondência:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.º / Complemento
        </td>
        <td>
            Bairro de Correspondência:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco_corresp' size='35' maxlength='50' title='Endereço de Correspondência' class='textdisabled' disabled>
            &nbsp;
            <input type='text' name='txt_num_complemento_corresp' title='Número, Complemento, ... de Correspondência' size='21' maxlength='20' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro_corresp' title='Bairro de Correspondência' size='50' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade de Correspondência:
        </td>
        <td>
            Estado de Correspondência:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cidade_corresp' title='Cidade de Correspondência' size='35' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_estado_corresp' title='Estado de Correspondência' size='35' class='textdisabled' disabled>
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
                <option value='F'>PESSOA FÍSICA</option>
                <option value='J'>PESSOA JURÍDICA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='2' cols='75' maxlength='150' class='caixadetexto'></textarea>
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
<b><font color='red'>Observação:</font></b>
<pre>
* A combo <b>SUPERVISOR</b>, só lista Representantes que são Funcionários.
</pre>
</pre>