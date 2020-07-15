<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');

if(empty($_GET['pop_up'])) {
    require('../../../lib/menu/menu.php');//Se essa Tela for aberta de forma normal, exibe o Menu ...
    segurancas::geral('/erp/albafer/modulo/vendas/representante/alterar.php', '../../../');
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>REPRESENTANTE ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>REPRESENTANTE J¡ EXISTENTE.</font>";

if($passo == 1) {
    $sql = "SELECT * 
            FROM `representantes_vs_funcionarios` 
            WHERE id_representante = '$id_representante' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//O funcion·rio n„o est· na tabela de Representantes ...
        //Busca os dados de representante, È autÙnomo
        $sql = "SELECT * 
                FROM `representantes` 
                WHERE `id_representante` = '$id_representante' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $nome_fantasia          = $campos[0]['nome_fantasia'];
        $porc_comissao_fixa 	= $campos[0]['porc_comissao_fixa'];
        $porc_comissao_sob_fat 	= $campos[0]['porc_comissao_sob_fat'];
        $nome_representante 	= $campos[0]['nome_representante'];
        $descontar_ir           = $campos[0]['descontar_ir'];
        $pgto_comissao_grupo 	= $campos[0]['pgto_comissao_grupo'];
        $id_pais                = $campos[0]['id_pais'];
        $cep                    = $campos[0]['cep'];
        $endereco               = $campos[0]['endereco'];
        $num_comp               = $campos[0]['num_comp'];
        $bairro                 = $campos[0]['bairro'];
        $cidade                 = $campos[0]['cidade'];
        $estado                 = $campos[0]['uf'];
        $fone                   = $campos[0]['fone'];
        $cel_fax                = $campos[0]['fax'];
        $socios                 = $campos[0]['socios'];
        $contato                = $campos[0]['contato'];
        $tipo_pessoa            = $campos[0]['tipo_pessoa'];
        $cnpj_cpf               = $campos[0]['cnpj_cpf'];
        $insc_estadual          = $campos[0]['insc_estadual'];
        $insc_municipal         = $campos[0]['insc_municipal'];
        $core                   = $campos[0]['core'];
        $tipo_firma             = $campos[0]['tipo_firma'];
        $zona_atuacao           = $campos[0]['zona_atuacao'];
        $banco                  = $campos[0]['banco'];
        $agencia                = $campos[0]['agencia'];
        $conta_corrente         = $campos[0]['conta_corrente'];
        $correntista            = $campos[0]['correntista'];
        $id_pais_corresp        = $campos[0]['id_pais_corresp'];
        $cep_corresp            = $campos[0]['cep_corresp'];
        $endereco_corresp       = $campos[0]['end_corresp'];
        $num_comp_corresp       = $campos[0]['num_comp_corresp'];
        $bairro_corresp         = $campos[0]['bairro_corresp'];
        $cidade_corresp         = $campos[0]['cidade_corresp'];
        $estado_corresp         = $campos[0]['uf_corresp'];
        $empresa                = $campos[0]['empresa'];
        $email                  = $campos[0]['email'];
        $checado_autonomo       = 'checked';//Habilita a opÁ„o AutÙnomo - Radio
        $disabled               = '';//Caixa de Texto
        $class                  = 'caixadetexto';//Class dos Objetos
        $disabled_botao         = 'disabled';
        $disabled_supervisor 	= '';
        /*OpÁ„o = 2 -> Quer dizer que n„o È funcion·rio, essa v·ri·vel opÁ„o vai dentro 
        do objeto hidden chamado opcao ...*/
        $opcao = 2;
    }else {//Busca os dados de funcion·rio
        $sql = "SELECT c.cargo, e.nomefantasia as empresa_func, f.*, f.email_externo as email_func, r.nome_representante, r.nome_fantasia, 
                r.porc_comissao_fixa, r.porc_comissao_sob_fat, r.descontar_ir, r.pgto_comissao_grupo, r.socios, r.contato, r.core, 
                r.tipo_firma, r.zona_atuacao, r.banco, r.agencia, r.conta_corrente, r.correntista, 
                r.end_corresp, r.num_comp_corresp, r.cep_corresp, r.cidade_corresp, r.bairro_corresp, r.uf_corresp, r.observacao 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
                INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                WHERE r.`id_representante` = '$id_representante' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        $id_funcionario_selecionado = $campos[0]['id_funcionario'];
        $nome_fantasia              = $campos[0]['nome_fantasia'];
        $porc_comissao_fixa         = $campos[0]['porc_comissao_fixa'];
        $porc_comissao_sob_fat      = $campos[0]['porc_comissao_sob_fat'];
        $nome_representante         = $campos[0]['nome_representante'];
        $descontar_ir               = $campos[0]['descontar_ir'];
        $pgto_comissao_grupo        = $campos[0]['pgto_comissao_grupo'];
        $cargo                      = $campos[0]['cargo'];
        $id_pais                    = $campos[0]['id_pais'];
        $cep                        = $campos[0]['cep'];
        $endereco                   = $campos[0]['endereco'];
        $num_comp                   = $campos[0]['numero'].$campos[0]['complemento'];
        $bairro                     = $campos[0]['bairro'];
        $cidade                     = $campos[0]['cidade'];
        $id_uf                      = $campos[0]['id_uf'];
        
        //Busca o estado atravÈs do id_uf
        $sql = "SELECT sigla 
                FROM `ufs` 
                WHERE `id_uf` = '$id_uf' LIMIT 1 ";
        $campos_sigla           = bancos::sql($sql);
        $estado                 = $campos_sigla[0]['sigla'];
        $fone                   = $campos[0]['ddd_residencial'].' '.$campos[0]['telefone_residencial'];
        $ddd_cel                = ($campos[0]['ddd_celular'] == 0) ? '' : $campos[0]['ddd_celular'];
        $tel_cel                = ($campos[0]['telefone_celular'] == 0) ? '' : $campos[0]['telefone_celular'];
        $cel_fax                = $ddd_cel.' '.$tel_cel;
        $socios                 = $campos[0]['socios'];
        $contato                = $campos[0]['contato'];
        $cnpj_cpf               = $campos[0]['cnpj_cpf'];
        $insc_estadual 		= $campos[0]['insc_estadual'];
        $insc_municipal 	= $campos[0]['insc_municipal'];
        $core                   = $campos[0]['core'];
        $tipo_firma 		= $campos[0]['tipo_firma'];
        $zona_atuacao 		= $campos[0]['zona_atuacao'];
        $banco                  = $campos[0]['banco'];
        $agencia                = $campos[0]['agencia'];
        $conta_corrente         = $campos[0]['conta_corrente'];
        $correntista 		= $campos[0]['correntista'];
        $id_pais_corresp 	= $campos[0]['id_pais_corresp'];
        $cep_corresp 		= $campos[0]['cep_corresp'];
        $endereco_corresp 	= $campos[0]['end_corresp'];
        $num_comp_corresp 	= $campos[0]['num_comp_corresp'];
        $bairro_corresp 	= $campos[0]['bairro_corresp'];
        $cidade_corresp 	= $campos[0]['cidade_corresp'];
        $estado_corresp 	= $campos[0]['uf_corresp'];
        $empresa                = $campos[0]['empresa_func'];
        $email                  = $campos[0]['email_func'];
        //Checa o opt_opcao Funcion·rio
        $checado_funcionario    = 'checked';//Habilita a opÁ„o Funcion·rio - Radio
        $disabled               = 'disabled';//Caixa de Texto
        $class                  = 'textdisabled';//Class dos Objetos
        $disabled_botao         = '';
        $disabled_supervisor    = 'disabled';
        /*OpÁ„o = 1 -> Quer dizer que È funcion·rio, essa v·ri·vel opÁ„o vai dentro 
        do objeto hidden chamado opcao ...*/
        $opcao = 1;
    }
//Aqui È uma verificaÁ„o para habilitaÁ„o no JavaScript
//Busca os dados da tabela ceps
    $sql = "SELECT `logradouro`, `bairro` 
            FROM `ceps` 
            WHERE `cep` = '$cep' LIMIT 1 ";
    $campos_cep         = bancos::sql($sql);
    $endereco_verifica  = $campos_cep[0]['logradouro'];
    $bairro_verifica    = $campos_cep[0]['bairro'];

//Busca os dados da tabela ceps
    $sql = "SELECT `logradouro`, `bairro` 
            FROM `ceps` 
            WHERE `cep` = '$cep_corresp' LIMIT 1 ";
    $campos_cep_corresp         = bancos::sql($sql);
    $endereco_corresp_verifica  = $campos_cep_corresp[0]['logradouro'];
    $bairro_corresp_verifica    = $campos_cep_corresp[0]['bairro'];

//Aki busca o supervisor da Tabela Relacional de Representantes
    $sql = "SELECT `id_representante_supervisor` 
            FROM `representantes_vs_supervisores` 
            WHERE `id_representante` = '$id_representante' LIMIT 1 ";
    $campos_representante_supervisor = bancos::sql($sql);
    if(count($campos_representante_supervisor) == 1) $id_representante_supervisor = $campos_representante_supervisor[0]['id_representante_supervisor'];
?>
<html>
<title>.:: Alterar Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var id_representante = eval('<?=$id_representante;?>')
    if(document.form.opt_opcao[1].checked == true) {//AutÙnomo
//O sistema sÛ n„o vai forÁar um Supervisor para o Representante Direto que È o id_representante = 1
        if(id_representante != 1) {
//Supervisor
            if(document.form.cmb_supervisor.value == '') {
                if(!combo('form', 'cmb_supervisor', '', 'SELECIONE O SUPERVISOR !')) {
                    return false
                }
            }
        }
    }
//CÛdigo do Representante
    if(!texto('form', 'txt_cod_representante', '1', '1234567890', 'C”DIGO DO REPRESENTANTE', '2')) {
        return false
    }
//Nome do Representante
    if(document.form.txt_nome_representante.disabled == false) {
        if(!texto('form', 'txt_nome_representante', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’[].,&()|_-0123456789 ', 'NOME DO REPRESENTANTE', '2')) {
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
    if(!texto('form', 'txt_nome_fantasia', '1', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'NOME FANTASIA', '2')) {
        return false
    }

    if(document.form.opt_opcao[1].checked == true) {//AutÙnomo
//Se o PaÌs for Brasil, ent„o forÁa o preenchimento de CEP
        if(document.form.cmb_pais.value == 31) {
//Cep
            if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
                return false
            }
//N˙mero / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO', '2')) {
                return false
            }
        }else {
//EndereÁo
            if(document.form.txt_endereco.value != '') {
                if(!texto('form', 'txt_endereco', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'ENDERE«O', '2')) {
                    return false
                }
            }
//N˙mero / Complemento
            if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO', '2')) {
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
                if(!texto('form', 'txt_cidade', '3', '-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,".‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ', 'CIDADE', '1')) {
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
//SÛcios
        if(document.form.txt_socio.value != '') {
            if(!texto('form', 'txt_socio', '1', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'SOCIO', '2')) {
                return false
            }
        }
//Contato
        if(document.form.txt_contato.value != '') {
            if(!texto('form', 'txt_contato', '1', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'CONTATO', '2')) {
                return false
            }
        }
//CNPJ ou CPF
        if(document.form.txt_cnpj_cpf.value != '') {
            nro = document.form.txt_cnpj_cpf.value
            for(i = 0; i < nro.length; i++) {
                letra = nro.charAt(i)
                if((letra == '.') || (letra == '/') || (letra == '-')) {
                    nro = nro.replace(letra, '')
                }
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
//InscriÁ„o Estadual
        if(document.form.txt_inscricao_estadual.value != '') {
            if(!texto('form', 'txt_inscricao_estadual', '3', '0123456789', 'INSCRI«√O ESTADUAL', '1')) {
                return false
            }
        }
//InscriÁ„o Municipal
        if(document.form.txt_insc_municipal.value != '') {
            if(!texto('form', 'txt_insc_municipal', '3', '0123456789', 'INSCRI«√O MUNICIPAL', '1')) {
                return false
            }
        }
//Tipo de Firma
        if(document.form.txt_tipo_firma.value != '') {
            if(!texto('form', 'txt_tipo_firma', '3', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'TIPO DE FIRMA', '2')) {
                return false
            }
        }
//Zona de AtuaÁ„o
        if(document.form.txt_zona_atuacao.value != '') {
            if(!texto('form', 'txt_zona_atuacao', '3', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'ZONA DE ATUA«√O', '1')) {
                return false
            }
        }
//Banco ...
        if(document.form.txt_banco.value != '') {
            if(!texto('form', 'txt_banco', '3', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ«·ÈÌÛ˙‚ÍÓÙ˚¬ Œ‘€¡…Õ”⁄„ı√’-_.(), ', 'BANCO', '2')) {
                return false
            }
        }
//AgÍncia ...
        if(document.form.txt_agencia.value != '') {
            if(!texto('form', 'txt_agencia', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ«·ÈÌÛ˙‚ÍÓÙ˚¬ Œ‘€¡…Õ”⁄„ı√’-_.(), ', 'AG NCIA', '1')) {
                return false
            }
        }
//Conta Corrente ...
        if(document.form.txt_conta_corrente.value != '') {
            if(!texto('form', 'txt_conta_corrente', '3', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ«·ÈÌÛ˙‚ÍÓÙ˚¬ Œ‘€¡…Õ”⁄„ı√’-_.(), ', 'CONTA CORRENTE', '1')) {
                return false
            }
        }
//Correntista ...
        if(document.form.txt_correntista.value != '') {
            if(!texto('form', 'txt_correntista', '3', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'CORRENTISTA', '2')) {
                return false
            }
        }
    }
//ValidaÁ„o dos Campos de CorrespondÍncia
    if(document.form.cmb_pais_corresp.value != '') {
//Se o PaÌs for Brasil, ent„o forÁa o preenchimento de CEP
        if(document.form.cmb_pais_corresp.value == 31) {
//Cep de CorrespondÍncia
            if(document.form.txt_cep_corresp.value != '') {
                if(!texto('form', 'txt_cep_corresp', '9', '-1234567890', 'CEP DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
//N˙mero / Complemento de CorrespondÍncia
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
//PaÌs Internacional
        }else {
//EndereÁo de CorrespondÍncia
            if(document.form.txt_endereco_corresp.value != '') {
                if(!texto('form', 'txt_endereco_corresp', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'ENDERE«O DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
//N˙mero / Complemento de CorrespondÍncia
            if(document.form.txt_num_complemento_corresp.value != '') {
                if(!texto('form', 'txt_num_complemento_corresp', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
//Bairro de CorrespondÍncia
            if(document.form.txt_bairro_corresp.value != '') {
                if(!texto('form', 'txt_bairro_corresp', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'BAIRRO DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
//Cidade de CorrespondÍncia
            if(document.form.txt_cidade_corresp.value != '') {
                if(!texto('form', 'txt_cidade_corresp', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CIDADE DE CORRESPOND NCIA', '1')) {
                    return false
                }
            }
//Estado de CorrespondÍncia
            if(document.form.txt_estado_corresp.value != '') {
                if(!texto('form', 'txt_estado_corresp', '2', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'ESTADO DE CORRESPOND NCIA', '2')) {
                    return false
                }
            }
        }
    }
//Core
    if(document.form.txt_core.value != '') {
        if(!texto('form', 'txt_core', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CORE', '2')) {
            return false
        }
    }
//Empresa
    if(document.form.txt_empresa.value != '') {
        if(!texto('form', 'txt_empresa', '1', '-qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM‹¸·ÈßÌÛ˙¡…Õ¿‡”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,&(): ', 'EMPRESA', '1')) {
            return false
        }
    }
//E-mail
    if(document.form.opt_opcao[1].checked == true) {//Representante AutÙnomo ...
        if(!new_email('form', 'txt_email')) {
            return false
        }
    }
//Tipo de Pessoa
    if(document.form.opt_opcao[1].checked == true) {//AutÙnomo
        if(!combo('form', 'cmb_tipo_pessoa', '', 'SELECIONE O TIPO DE PESSOA !')) {
            return false
        }
    }
//Aqui nessa parte se faz o tratamento das caixas de texto para poder gravar no bd
    var elementos = document.form.elements
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
/*Verifica se no nome tem um sÌmbolo de colchete para saber se j· s„o os texts
de arrays de desconto de clientes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras
                elementos[i].value = strtofloat(elementos[i].value)
            }
        }
    }
    //Aqui serve para n„o submeter
    if(document.form.controle.value == 0) return false
//Desabilita para poder gravar no Banco de Dados
    if(document.form.opt_opcao[1].checked == true) {//AutÙnomo
//EndereÁo Normal
        document.form.txt_endereco.disabled = false
        document.form.txt_bairro.disabled = false
        document.form.txt_cidade.disabled = false
        document.form.txt_estado.disabled = false
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
        document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
        document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
        document.form.txt_cidade.value = document.form.txt_cidade.value.toUpperCase()
        document.form.txt_estado.value = document.form.txt_estado.value.toUpperCase()
    }
//EndereÁo de CorrespondÍncia
    document.form.txt_endereco_corresp.disabled = false
    document.form.txt_bairro_corresp.disabled = false
    document.form.txt_cidade_corresp.disabled = false
    document.form.txt_estado_corresp.disabled = false
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
    document.form.txt_endereco_corresp.value = document.form.txt_endereco_corresp.value.toUpperCase()
    document.form.txt_bairro_corresp.value = document.form.txt_bairro_corresp.value.toUpperCase()
    document.form.txt_cidade_corresp.value = document.form.txt_cidade_corresp.value.toUpperCase()
    document.form.txt_estado_corresp.value = document.form.txt_estado_corresp.value.toUpperCase()
    //Deixa em formato de gravar no BD ...
    document.form.txt_porc_comissao_fixa.value 		= strtofloat(document.form.txt_porc_comissao_fixa.value)
    document.form.txt_porc_comissao_sob_fat.value 	= strtofloat(document.form.txt_porc_comissao_sob_fat.value)
    document.form.txt_nome_representante.disabled = false
//Travo o bot„o p/ n„o correr o risco de submeter os dados 2 vezes p/ o BD de Dados ...
    document.form.cmd_salvar.disabled = true
}

//Atualiza o frame de baixo para controle do CEP ...
function buscar_cep(valor) {
    if(valor == 1) {//EndereÁo Normal
        var id_pais = document.form.cmb_pais.value
        var txt_cep = document.form.txt_cep.value
        if(txt_cep == '') {//… v·zio
            document.form.txt_endereco.value = ''
            document.form.txt_bairro.value = ''
            document.form.txt_cidade.value = ''
            document.form.txt_estado.value = ''
        }else {//N„o È v·zio
//Verifica se o CEP È v·lido
            if(txt_cep.length < 9) {
                alert('CEP INV¡LIDO !')
                document.form.txt_cep.focus()
                document.form.txt_cep.select()
                return false
            }
            if(id_pais == 31) {//SÛ buscar· o CEP se for Brasil
                window.parent.cep.location = 'buscar_cep.php?txt_cep='+txt_cep
            }
        }
    }else {//EndereÁo de CorrespondÍncia
        var id_pais_corresp = document.form.cmb_pais_corresp.value
        var txt_cep_corresp = document.form.txt_cep_corresp.value
        if(txt_cep_corresp == '') {//… v·zio
            document.form.txt_endereco_corresp.value = ''
            document.form.txt_bairro_corresp.value = ''
            document.form.txt_cidade_corresp.value = ''
            document.form.txt_estado_corresp.value = ''
        }else {//N„o È v·zio
//Verifica se o CEP de CorrespondÍncia È v·lido
            if(txt_cep_corresp.length < 9) {
                alert('CEP DE CORRESPOND NCIA INV¡LIDO !')
                document.form.txt_cep_corresp.focus()
                document.form.txt_cep_corresp.select()
                return false
            }
            if(id_pais_corresp == 31) {//SÛ buscar· o CEP se for Brasil
                parent.cep.location = 'buscar_cep.php?txt_cep_corresp='+txt_cep_corresp
            }
        }
    }
}

//FunÁ„o que controla para n„o submeter
function controlar(valor) {
    document.form.controle.value = valor
}

function habilitar_desabilitar() {
    if(document.form.opt_opcao[0].checked == true) {//Funcion·rio ...
        
        var opcao = eval('<?=$opcao;?>')
        if(opcao != document.form.opt_opcao[0].value) {//Significa que estava gravado no BD a opÁ„o Representante ...
            alert('CLIQUE NO BOT√O BUSCAR FUNCION¡RIO !')
            document.form.cmd_funcionario.focus()
        }
        
        with(document.form) {
            cmd_funcionario.disabled        = false//Habilita Bot„o
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
            cmd_funcionario.disabled        = true//Desabilita Bot„o
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
    document.form.txt_cod_representante.focus()
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
</Script>
</head>
<body onload='habilitar_desabilitar()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<!--Vari·vel que guarda o par‚metro da Tela PÛs-Filtro de Representantes e que serve para n„o conflitar com o par‚metro da Tela PÛs-Filtro 
do bot„o Consultar Funcion·rio que È aberto como sendo Pop-UP-->
<input type='hidden' name='hdd_parametro_principal' value='<?=$parametro?>'>
<input type='hidden' name='id_representante' value='<?=$id_representante?>'>
<input type='hidden' name='hdd_funcionario_selecionado' value='<?=$id_funcionario_selecionado;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<input type='hidden' name='controle' value='1'>
<!--****************************Controles de Tela****************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Representante(s)
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
            <input type='radio' <?=$checado_funcionario;?> name='opt_opcao' value='1' id='opt1' onclick='habilitar_desabilitar()'>
            <label for='opt1'>Funcion&aacute;rio</label>
            <input type='radio' <?=$checado_autonomo;?> name='opt_opcao' value='2' id='opt2' onclick='habilitar_desabilitar()'>
            <label for='opt2'>AutÙnomo</label>
        </td>
        <td>
            <select name='cmb_supervisor' title='Selecione o Supervisor' class='combo' <?=$disabled_supervisor;?>>
            <?
                /*O sistema sÛ traz Representantes que s„o funcion·rios nos cargos de 
                (Supervisor Externo de Vendas "25" e Supervisor Interno de Vendas "109") 
                ou Departamento de Chefia "19" ...*/
                $sql = "SELECT r.`id_representante`, f.`nome` 
                        FROM `representantes_vs_funcionarios` rf 
                        INNER JOIN `representantes` r ON r.`id_representante` = rf.`id_representante` 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` AND (f.`id_cargo` IN (25, 109) OR f.`id_departamento` = '19') 
                        ORDER BY f.`nome` ";
                echo combos::combo($sql, $id_representante_supervisor);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CÛdigo do Representante:</b>
        </td>
        <td>
            <b>Nome do Representante:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cod_representante' value='<?=$id_representante;?>' title='Digite o CÛdigo do Representante' size='15' maxlength='11' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_nome_representante" value="<?=$nome_representante;?>" size="32" maxlength="85" title="Digite o Nome do Representante" class="<?=$class;?>" <?=$disabled;?>>
            <!--Vari·vel incluir dentro do bot„o representante -> indica que a tela È incluir-->
            <input type='button' name="cmd_funcionario" value="Buscar Funcion·rio" title="Buscar Funcion·rio" onclick="html5Lightbox.showLightbox(7, 'consultar_funcionario.php')" class='botao' <?=$disabled_botao;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome Fantasia /</b>
        </td>
        <td>
            Cargo:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_nome_fantasia" value="<?=$nome_fantasia;?>" title="Digite o Nome Fantasia" size="26" maxlength="25" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_cargo" value="<?=$cargo;?>" title="Cargo" size="40" maxlength="85" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            % de Comiss„o Fixa:
        </td>
        <td>
            % de Comiss„o sob Faturamento:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_porc_comissao_fixa' value='<?=number_format($porc_comissao_fixa, 2, ',', '.');?>' title='Digite a % de Comiss„o Fixa' size='6' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_porc_comissao_sob_fat' value='<?=number_format($porc_comissao_sob_fat, 3, ',', '.');?>' title='Digite a % de Comiss„o Sob Faturamento' size='8' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
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
            <?
                $checked = ($descontar_ir == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_descontar_ir' value='S' title='Descontar Imposto de Renda (Alba / Tool)' id='label' class='checkbox' <?=$checked;?>>
            <label for='label'>
                Descontar Imposto de Renda (Alba / Tool)
            </label>
        </td>
        <td>
            <?
                $checked = ($pgto_comissao_grupo == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_pgto_comissao_grupo' value='S' title='Descontar Pagamento de Comiss„o pelo Grupo' id='label1' class='checkbox' <?=$checked;?>>
            <label for='label1'>
                Pagamento de Comiss„o pelo Grupo
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
            PaÌs:
        </td>
        <td>
            CEP:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais' onchange='pais_habilita()' class='combo' <?=$disabled;?>>
                <?=combos::combo('SELECT id_pais, pais FROM `paises` ', $id_pais);?>
            </select>
        </td>
        <td>
            <input type='text' name="txt_cep" value="<?=$cep;?>" size='12' maxlength='9' title="Digite o Cep" onkeyup="verifica(this, 'cep', '', '', event)" onfocus="controlar(0)" onblur="buscar_cep(1);controlar(1)" class='caixadetexto' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            EndereÁo:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.∫ / Complemento
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_endereco" value="<?=$endereco;?>" title="EndereÁo" size='35' maxlength="50">
            &nbsp;
            <input type='text' name="txt_num_complemento" value="<?=$num_comp;?>" title="N˙mero, Complemento, ..." size="21" maxlength="20" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_bairro" value="<?=$bairro;?>" title="Bairro" size="50">
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
            <input type='text' name="txt_cidade" value="<?=$cidade;?>" title="Cidade" size='35'>
        </td>
        <td>
            <input type='text' name="txt_estado" value="<?=$estado;?>" title="Estado" size='35'>
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
            <input type='text' name="txt_fone" value="<?=$fone;?>" title="Digite o Fone" size="15" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_cel_fax" value="<?=$cel_fax;?>" title="Digite o Cel / Fax" size="15" class="<?=$class;?>" <?=$disabled;?>>
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
            <input type='text' name="txt_socio" value="<?=$socios;?>" title="Digite o SÛcio" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_contato" value="<?=$contato;?>" title="Digite o Contato" class="<?=$class;?>" <?=$disabled;?>>
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
            <input type='text' name="txt_cnpj_cpf" value="<?=$cnpj_cpf?>" title="Digite o CNPJ / CPF" size="26" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_inscricao_estadual" value="<?=$insc_estadual;?>" title="Digite a InscriÁ„o Estadual" size="18" class="<?=$class;?>" <?=$disabled;?>>
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
            <input type='text' name='txt_insc_municipal' value="<?=$insc_municipal;?>" title="Digite a InscriÁ„o Municipal" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_tipo_firma' value="<?=$tipo_firma;?>" title="Digite o Tipo de Firma" size='52' maxlength='50' class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Zona de Atua&ccedil;&atilde;o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_zona_atuacao' value='<?=$zona_atuacao;?>' title='Digite a Zona de AtuaÁ„o' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados Banc·rios
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
            <input type='text' name='txt_banco' value='<?=$banco;?>' title='Digite o Banco' size='35' maxlength='30' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_agencia' value='<?=$agencia;?>' title='Digite a AgÍncia' size='35' maxlength='30' class='<?=$class;?>' <?=$disabled;?>>
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
            <input type='text' name='txt_conta_corrente' value='<?=$conta_corrente;?>' title='Digite a Conta Corrente' size='35' maxlength='30' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_correntista' value='<?=$correntista;?>' title='Digite o Correntista' size='65' maxlength='60' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            PaÌs de CorrespondÍncia:
        </td>
        <td>
            CEP de CorrespondÍncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name="cmb_pais_corresp" class="combo" onchange='pais_habilita_corresp()' <?=$disabled;?>>
            <?
                $sql = "SELECT id_pais, pais 
                        FROM `paises` ";
                echo combos::combo($sql, $id_pais_corresp);
            ?>
            </select>
        </td>
        <td colspan='2'>
            <input type='text' name="txt_cep_corresp" value="<?=$cep_corresp;?>" size='12' maxlength='9' title="Digite o Cep" onkeyup="verifica(this, 'cep', '', '', event)" onfocus="controlar(0)" onblur="buscar_cep(2);controlar(1)" class='caixadetexto' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Endere&ccedil;o de CorrespondÍncia:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            N.∫ / Complemento
        </td>
        <td>
            Bairro de CorrespondÍncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_endereco_corresp" value="<?=$endereco_corresp;?>" size='35' maxlength="50" title="EndereÁo de CorrespondÍncia" class='caixadetexto' disabled>
            &nbsp;
            <input type='text' name="txt_num_complemento_corresp" value="<?=$num_comp_corresp;?>" size="21" maxlength="20" class='caixadetexto' title="N˙mero, Complemento, ... de CorrespondÍncia" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_bairro_corresp" value="<?=$bairro_corresp;?>" size="50" title="Bairro de CorrespondÍncia" class='caixadetexto' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade de CorrespondÍncia:
        </td>
        <td>
            Estado de CorrespondÍncia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_cidade_corresp" value="<?=$cidade_corresp;?>" size='35' title="Cidade de CorrespondÍncia" class='caixadetexto' disabled>
        </td>
        <td>
            <input type='text' name="txt_estado_corresp" value="<?=$estado_corresp;?>" size='35' title="Estado de CorrespondÍncia" class='caixadetexto' disabled>
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
            <input type='text' name="txt_core" value="<?=$core;?>" title="Digite o Core" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name="txt_empresa" value="<?=$empresa;?>" title="Digite a Empresa" class="<?=$class;?>" <?=$disabled;?>>
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
            <input type='text' name='txt_email' value='<?=$email;?>' title='Digite o E-mail' size='35' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <select name='cmb_tipo_pessoa' title='Selecione o Tipo de Pessoa' class='<?=$class;?>' <?=$disabled;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($tipo_pessoa == 'F') {
                        $selectedf = 'selected';
                    }else if($tipo_pessoa == 'J') {
                        $selectedj = 'selected'; 
                    }
                ?>
                <option value='F' <?=$selectedf;?>>PESSOA FÕSICA</option>
                <option value='J' <?=$selectedj;?>>PESSOA JURÕDICA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            ObservaÁ„o: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' rows='2' cols='75' maxlength='150' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            if($_GET['pop_up'] != 1) {//Se essa Tela for aberta de forma normal, exibe esses botıes do contr·rio ...
        ?>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar2.php<?=$parametro;?>'" class='botao'>
            <input type="reset" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome_representante.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
            }else {//Se for aberta como sendo Pop-UP ...
        ?>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>ObservaÁ„o:</font></b>
<pre>
* A combo <b>SUPERVISOR</b>, sÛ lista Representantes que s„o Funcion·rios.
</pre>
</pre>
<?
}else if($passo == 2) {
    $sql = "SELECT `id_representante` 
            FROM `representantes` 
            WHERE (`nome_fantasia` = '$_POST[txt_nome_fantasia]') 
            AND `ativo` = '1' 
            AND `id_representante` <> '$id_representante' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
/*********************************Controle com os Checkbox*********************************/
        if(empty($chkt_descontar_ir))           $chkt_descontar_ir = 'N';
        if(empty($chkt_pgto_comissao_grupo))    $chkt_pgto_comissao_grupo = 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
        $cmb_pais           = (!empty($_POST[cmb_pais])) ? "'".$_POST[cmb_pais]."'" : 'NULL';
        $cmb_pais_corresp   = (!empty($_POST[cmb_pais_corresp])) ? "'".$_POST[cmb_pais_corresp]."'" : 'NULL';
        
//Atualiza a Tabela de rep. independente de ser funcion·rio ou n„o
        $sql = "UPDATE `representantes` SET `id_pais` = $cmb_pais, `id_pais_corresp` = $cmb_pais_corresp, `nome_representante` = '$_POST[txt_nome_representante]', `nome_fantasia` = '$_POST[txt_nome_fantasia]', `porc_comissao_fixa` = '$_POST[txt_porc_comissao_fixa]', `porc_comissao_sob_fat` = '$_POST[txt_porc_comissao_sob_fat]', `descontar_ir` = '$chkt_descontar_ir', `pgto_comissao_grupo` = '$chkt_pgto_comissao_grupo', `endereco` = '$_POST[txt_endereco]', `num_comp` = '$_POST[txt_num_complemento]', cep = '$_POST[txt_cep]', `bairro` = '$_POST[txt_bairro]', `cidade` = '$_POST[txt_cidade]', `uf` = '$_POST[txt_estado]', `fone` = '$_POST[txt_fone]', fax = '$_POST[txt_cel_fax]', socios = '$_POST[txt_socio]', contato = '$_POST[txt_contato]', tipo_pessoa = '$_POST[cmb_tipo_pessoa]', cnpj_cpf = '$_POST[txt_cnpj_cpf]', insc_estadual = '$_POST[txt_inscricao_estadual]', insc_municipal = '$_POST[txt_insc_municipal]', core = '$_POST[txt_core]', tipo_firma = '$_POST[txt_tipo_firma]', zona_atuacao = '$_POST[txt_zona_atuacao]', `banco` = '$_POST[txt_banco]', `agencia` = '$_POST[txt_agencia]', `conta_corrente` = '$_POST[txt_conta_corrente]', `correntista` = '$_POST[txt_correntista]', end_corresp = '$_POST[txt_endereco_corresp]', `num_comp_corresp` = '$_POST[txt_num_complemento_corresp]', cep_corresp = '$_POST[txt_cep_corresp]', bairro_corresp = '$_POST[txt_bairro_corresp]', cidade_corresp = '$_POST[txt_cidade_corresp]', uf_corresp = '$_POST[txt_estado_corresp]', empresa = '$_POST[txt_empresa]', `email` = '$_POST[txt_email]', `observacao` = '$_POST[txt_observacao]' WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
        bancos::sql($sql);
        
        if(!empty($_POST['hdd_funcionario_selecionado'])) {//Representante do Tipo Funcion·rio
//Verifica se tem o func na tab. de rep ...
            $sql = "SELECT id_representante 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//N„o existe ...
                $sql = "INSERT INTO `representantes_vs_funcionarios` (`id_representante_funcionario`, `id_representante`, `id_funcionario`) VALUES (NULL, '$_POST[id_representante]', '$_POST[hdd_funcionario_selecionado]') ";
                bancos::sql($sql);
            }else {//J· existe e est· substituindo por AutÙnomo
                $sql = "UPDATE `representantes_vs_funcionarios` SET `id_funcionario` = '$_POST[hdd_funcionario_selecionado]' WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
                bancos::sql($sql);
            }
//Deleta o Supervisor na tabela Relacional de Representantes
            $sql = "DELETE FROM `representantes_vs_supervisores` WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Representante do Tipo AutÙnomo
            $sql = "SELECT id_representante_funcionario 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {
                $sql = "DELETE FROM `representantes_vs_funcionarios` WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
                bancos::sql($sql);
            }
//Verifica se existe o Supervisor na tabela Relacional de Representantes
            $sql = "SELECT id_representante_vs_supervisor 
                    FROM `representantes_vs_supervisores` 
                    WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) == 1) {//J· existia
                $sql = "UPDATE `representantes_vs_supervisores` SET `id_representante_supervisor` = '$_POST[cmb_supervisor]' WHERE `id_representante` = '$_POST[id_representante]' LIMIT 1 ";
//Insere o Supervisor na tabela Relacional de Representantes
            }else {//N„o existia atÈ ent„o
                $sql = "INSERT INTO `representantes_vs_supervisores` (`id_representante_vs_supervisor`, `id_representante_supervisor`, `id_representante`) values (NULL, '$_POST[cmb_supervisor]', '$_POST[id_representante]') ";
            }
            bancos::sql($sql);
            genericas::atualizar_representantes_no_site_area_cliente($_POST['id_representante']);
        }
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar2.php?parametro=<?=$_POST['hdd_parametro_principal'];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
/*Esse par‚metro de nÌvel vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisiÁ„o desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../';
//Aqui eu vou puxar a Tela ˙nica de Filtro de Notas Fiscais que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar Representante(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            CÛd Rep
        </td>
        <td>
            Nome do Representante
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Cargo / Supervisor
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Tel Cel / Fax
        </td>
        <td>
            Zona de AtuaÁ„o
        </td>
        <td>
            E-mail
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar2.php?passo=1&id_representante=<?=$campos[$i]['id_representante'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='center'>
            <?=$campos[$i]['id_representante'];?>
        </td>
        <td>
            <?=$campos[$i]['nome_representante'];?>
        </td>
        <td>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
        <?
//Aqui eu verifico se o repres. tambÈm È um funcion·rio, se for retorna o cargo do Funcion·rio ...
            $sql = "SELECT c.`cargo` 
                    FROM `representantes_vs_funcionarios` rf 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    WHERE rf.`id_representante` = ".$campos[$i]['id_representante']." LIMIT 1 ";
            $campos_cargo = bancos::sql($sql);//Significa que È funcion·rio ...
            if(count($campos_cargo) == 1) $cargo = $campos_cargo[0]['cargo'];
//Aki busca o supervisor da Tabela Relacional de Representantes ...
            $sql = "SELECT `id_representante_supervisor` 
                    FROM `representantes_vs_supervisores` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante_supervisor = bancos::sql($sql);
            if(count($campos_representante_supervisor) == 1) $id_representante_supervisor = $campos_representante_supervisor[0]['id_representante_supervisor'];
//Seleciono o funcion·rio que È Representante e Supervisor ...
            $sql = "SELECT f.`nome` AS supervisor 
                    FROM `representantes_vs_funcionarios` rf 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                    WHERE rf.`id_representante` = '$id_representante_supervisor' ORDER BY f.`nome` ";
            $campos_supervisor  = bancos::sql($sql);
            $supervisor         = $campos_supervisor[0]['supervisor'];

            if(!empty($cargo)) {//Funcion·rio ent„o printa o cargo dele
                echo '<font title="Cargo">'.$cargo.'</font>';
            }else {//AutÙnomo ent„o printa o supervisor dele
                echo '<font title="Supervisor">'.$supervisor.'</font>';
            }
//J· limpa as vari·veis para n„o dar problema na volta do outro loop
            $cargo = '';
            $supervisor = '';
        ?>
        </td>
        <td>
            <?=$campos[$i]['fone'];?>
        </td>
        <td>
            <?=$campos[$i]['fax'];?>
        </td>
        <td>
            <?=$campos[$i]['zona_atuacao'];?>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar2.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>