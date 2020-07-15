<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');

session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CLIENTE ALTERADO COM SUCESSO.</font>";

$id_cliente     = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];
$pop_up 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
$nao_exibir_menu= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['nao_exibir_menu'] : $_GET['nao_exibir_menu'];

//Desatrela o Cliente Matriz do Cliente que foi acessado ...
if(!empty($_POST['hdd_desatrelar_matriz'])) {
    $sql = "UPDATE `clientes` SET `id_cliente_matriz` = NULL WHERE `id_cliente` = '$id_cliente' limit 1 ";
    bancos::sql($sql);
}

if(isset($_POST['cmb_perfil_cliente'])) {
    //Busco alguns dados que ser„o utilizados mais abaixo, antes da alteraÁ„o de cadastro ...
    $sql = "SELECT `id_cliente_tipo` 
            FROM `clientes` 
            WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
    $campos_cliente 	= bancos::sql($sql);
    $id_cliente_tipo    = $campos_cliente[0]['id_cliente_tipo'];
    
    //Tratamento de Campos ...
    $trading			= (!empty($_POST['chkt_trading'])) ? 1 : 0;
    $base_pag_comissao		= (!empty($_POST['chkt_base_pag_comissao'])) ? 1 : 0;
    $tributar_ipi_rev 		= (!empty($_POST['chkt_tributar_ipi_rev'])) ? 'S' : 'N';
    $optante_simples_nacional 	= (!empty($_POST['chkt_optante_simples_nacional'])) ? 'S' : 'N';
    $isento_st 			= (!empty($_POST['chkt_isento_st'])) ? 'S' : 'N';
    $isento_st_em_pinos         = (!empty($_POST['chkt_isento_st_em_pinos'])) ? 'S' : 'N';
    $certificado_qualidade      = (!empty($_POST['chkt_certificado_qualidade'])) ? 'S' : 'N';
    $suframa_ativo              = (!empty($_POST['chkt_suframa_ativo'])) ? 'S' : 'N';
    
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
    $id_tipo_cliente            = (!empty($_POST[cmb_tipo_cliente])) ? "'".$_POST[cmb_tipo_cliente]."'" : 'NULL';
    $id_cliente_perfil          = (!empty($_POST[cmb_perfil_cliente])) ? "'".$_POST[cmb_perfil_cliente]."'" : 'NULL';
    
    if(!empty($_POST[txt_estado_cobranca])) {//Foram preenchidos dados do EndereÁo de CobranÁa ...
        $id_pais_cobranca       = (!empty($_POST[cmb_pais_cobranca])) ? "'".$_POST[cmb_pais_cobranca]."'" : 'NULL';
        
        //Busca o id_uf atravÈs do campo Estado de CobranÁa ...
        $sql = "SELECT `id_uf` 
                FROM `ufs` 
                WHERE `sigla` = '$_POST[txt_estado_cobranca]' LIMIT 1 ";
        $campos_uf_cobranca = bancos::sql($sql);
        $id_uf_cobranca     = (!empty($campos_uf_cobranca[0]['id_uf'])) ? "'".$campos_uf_cobranca[0]['id_uf']."'" : 'NULL';
    }else {//N„o foi preenchido nenhum dado do EndereÁo de CobranÁa ... 
        $id_pais_cobranca   = 'NULL';
        $id_uf_cobranca     = 'NULL';
    }
    
    $sql = "UPDATE `clientes` SET `id_pais_cobranca` = $id_pais_cobranca, `id_uf_cobranca` = $id_uf_cobranca, `id_cliente_tipo` = $id_tipo_cliente, `id_cliente_perfil` = $id_cliente_perfil, `artigo_isencao` = '$chkt_artigo_isencao', `endereco_cobranca` = '".addslashes($_POST[txt_endereco_cobranca])."', `num_complemento_cobranca` = '$_POST[txt_num_complemento_cobranca]', `bairro_cobranca` = '".addslashes($_POST[txt_bairro_cobranca])."', `cep_cobranca` = '$_POST[txt_cep_cobranca]', `cidade_cobranca` = '".addslashes($_POST[txt_cidade_cobranca])."', `trading` = '$trading', `base_pag_comissao` = '$base_pag_comissao', `tipo_faturamento` = '$_POST[cmb_tipo_faturamento]', `tipo_suframa` = '$_POST[cmb_tipo_suframa]', `certificado_qualidade` = '$certificado_qualidade', `cod_suframa` = '$_POST[txt_codigo_suframa]', `suframa_ativo` = '$suframa_ativo', `pta` = '$_POST[txt_pta]', `tributar_ipi_rev` = '$tributar_ipi_rev', `optante_simples_nacional` = '$optante_simples_nacional', `isento_st` = '$isento_st', `isento_st_em_pinos` = '$isento_st_em_pinos', `texto_da_nota` = '$_POST[txt_texto_da_nota]' WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
    
    /************************************************************************************************************/
    /*Quando existir mudanÁa no tipo de Cliente de/ou para Ind˙stria temos de atualizar os Descontos 
    desse Cliente nos itens de OrÁamento descongelados ...*/
    if($id_cliente_tipo != $_POST['cmb_tipo_cliente'] && ($id_cliente_tipo == 4 || $_POST['cmb_tipo_cliente'] == 4)) {
        //Aqui faz a verificaÁ„o das Empresas Divisıes cadastradas no Sistema ...
        $sql = "SELECT `id_empresa_divisao` 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            for($i = 0; $i < $linhas; $i++) {
                /*O sistema atualiza o Representante e Desconto de todos os OrÁamentos descongelados do Cliente 
                e das Divisıes que foram submetidas, OBS: Somente Produtos Normais de Linha ...*/
                $sql = "SELECT ov.`id_orcamento_venda`, ovi.`id_orcamento_venda_item` 
                        FROM `orcamentos_vendas` ov 
                        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` AND ov.congelar = 'N' 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`referencia` <> 'ESP' 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '".$campos[$i]['id_empresa_divisao']."' 
                        WHERE ov.`id_cliente` = '$_POST[id_cliente]' ORDER BY ov.data_emissao DESC ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($j = 0; $j < $linhas; $j++) {
                    /*Se o OrÁamento estiver com sua Data de Emiss„o dentro do Prazo de Validade 
                    atualizo os Custos dos Itens "PAs" desse OrÁamento ...*/
                    $vetor_dados_gerais     = vendas::dados_gerais_orcamento($campos[$j]['id_orcamento_venda']);
                    $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];

                    if($data_validade_orc >= date('Y-m-d')) {
/*******************************************************************************************************/
/*FunÁ„o pesadÌssima que verifica o Custo do Produto Acabado, Comiss„o do Representante p/ o determinado 
Item de OrÁamento, sendo executada desse jeito por item, a mesma j· fica um pouco mais leve ...*/
                        vendas::calculo_preco_liq_final_item_orc($campos[$j]['id_orcamento_venda_item'], 'S', 'S');
//Aqui eu atualizo a ML Est do Iem do OrÁamento ...
                        custos::margem_lucro_estimada($campos[$j]['id_orcamento_venda_item']);
/*************Rodo a funÁ„o de Comiss„o depois de ter gravado a ML Estimada*************/
                        vendas::calculo_ml_comissao_item_orc($campos[$j]['id_orcamento_venda'], $campos[$j]['id_orcamento_venda_item']);
                    }
                }
            }
        }
    }
}

//Busco dados do $id_cliente passado por par‚metro ...
$sql = "SELECT c.*, ufs.`sigla` 
        FROM `clientes` c 
        LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf_cobranca` 
        WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
$campos_cliente = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//ValidaÁ„o dos Campos de CobranÁa
    if(document.form.cmb_pais_cobranca.value != '') {
        if(document.form.cmb_pais_cobranca.value == 31) {//PaÌs = 'Brasil' ...
            //Cep de CobranÁa ...
            if(!texto('form', 'txt_cep_cobranca', '9', '-1234567890', 'CEP DE COBRAN«A', '2')) {
                return false
            }
            //N˙mero / Complemento de CobranÁa ...
            if(!texto('form', 'txt_num_complemento_cobranca', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞_/ ", 'N⁄MERO / COMPLEMENTO DE COBRAN«A', '2')) {
                return false
            }
        }else {//PaÌs Internacional ...
            //EndereÁo de CobranÁa ...
            if(!texto('form', 'txt_endereco_cobranca', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'ENDERE«O DE COBRAN«A', '2')) {
                return false
            }
            //N˙mero / Complemento de CobranÁa ...
            if(!texto('form', 'txt_num_complemento_cobranca', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞_/ ", 'N⁄MERO / COMPLEMENTO DE COBRAN«A', '2')) {
                return false
            }
            //Bairro de CobranÁa ...
            if(!texto('form', 'txt_bairro_cobranca', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'BAIRRO DE COBRAN«A', '2')) {
                return false
            }
            //Cidade de CobranÁa ...
            if(!texto('form', 'txt_cidade_cobranca', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CIDADE DE COBRAN«A', '1')) {
                return false
            }
        }
    }
    
    var id_cliente_tipo = eval('<?=$campos_cliente[0]['id_cliente_tipo'];?>')
//Verifico o Tipo de Cliente atual e se o Funcion·rio est· tentando colocar o Tipo de Cliente como sendo Atacadista ...
    if(document.form.cmb_tipo_cliente.value == 5 && id_cliente_tipo != 5) {
        //Se sim, os ˙nicos funcion·rios que podem fazer esse Tipo de alteraÁ„o: Roberto Diretor, Wilson Diretor, D·rcio e Nishimura ...
        var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>')
        if(id_funcionario != 62 && id_funcionario != 68 && id_funcionario != 98 && id_funcionario != 136) {
            alert('FUNCION¡RIO SEM PERMISS√O P/ ALTERAR ESTE TIPO DE CLIENTE PARA ATACADISTA !')
            document.form.cmb_tipo_cliente.focus()
            return false
        }
    }
//Perfil de Cliente
    if(!combo('form', 'cmb_perfil_cliente', '', 'SELECIONE UM PERFIL DE CLIENTE !')) {
        return false
    }
//Volume de Compras
    if(!combo('form', 'cmb_volume_compras', '', 'SELECIONE UM VOLUME DE COMPRAS !')) {
        return false
    }
//CÛdigo do Suframa
    if(document.form.cmb_tipo_suframa.disabled == false && document.form.cmb_tipo_suframa.value != '') {
        if(!texto('form', 'txt_codigo_suframa', '1', '-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ', 'C”DIGO DO SUFRAMA', '2')) {
            return false
        }
    }
//PTA
    if(document.form.txt_pta.value != '') {
        if(!texto('form', 'txt_pta', '6', '0123456789.-', 'PTA', '2')) {
            return false
        }
    }
//Aqui eu desabilito esse campo p/ poder gravar na Base de Dados ...
    document.form.cmb_tipo_cliente.disabled         = false
    document.form.chkt_base_pag_comissao.disabled   = false
//Aqui È para n„o reler a Tela de Baixo quando Clicar no Bot„o Salvar, a idÈia È apenas reler pelo Bot„o X do Pop-UP ...
    document.form.nao_atualizar.value = 1
    document.form.submit()
}

//Habilita a Unidade Federal de CobranÁa
function habilitar_pais_cobranca() {
    //Limpo todos os campos de EndereÁo ...
    document.form.txt_cep_cobranca.value                = ''
    document.form.txt_endereco_cobranca.value           = ''
    document.form.txt_num_complemento_cobranca.value    = ''
    document.form.txt_bairro_cobranca.value             = ''
    document.form.txt_cidade_cobranca.value             = ''
    document.form.txt_estado_cobranca.value             = ''

    if(document.form.cmb_pais_cobranca.value == 31) {//Se o PaÌs selecionado for Brasil ...
        document.form.txt_cep_cobranca.className        = 'caixadetexto'
        document.form.txt_endereco_cobranca.className   = 'textdisabled'
        document.form.txt_bairro_cobranca.className     = 'textdisabled'
        document.form.txt_cidade_cobranca.className     = 'textdisabled'
        document.form.txt_estado_cobranca.className     = 'textdisabled'
        document.form.txt_cep_cobranca.focus()
    }else if(document.form.cmb_pais_cobranca.value == '') {//Se n„o tiver nenhum PaÌs selecionado ...
        document.form.txt_cep_cobranca.className        = 'textdisabled'
        document.form.txt_endereco_cobranca.className   = 'textdisabled'
        document.form.txt_bairro_cobranca.className     = 'textdisabled'
        document.form.txt_cidade_cobranca.className     = 'textdisabled'
        document.form.txt_estado_cobranca.className     = 'textdisabled'
        document.form.txt_cod_cliente.focus()
    }else {//Se for outro PaÌs Internacional ...
        document.form.txt_cep_cobranca.className        = 'textdisabled'
        document.form.txt_endereco_cobranca.className   = 'caixadetexto'
        document.form.txt_bairro_cobranca.className     = 'caixadetexto'
        document.form.txt_cidade_cobranca.className     = 'caixadetexto'
        document.form.txt_endereco_cobranca.focus()
    }
}

function habilitar_suframa() {
    if(document.form.cmb_tipo_suframa.value != '') {//Se alguma opÁ„o de Suframa estiver marcada ent„o ...
        document.form.txt_codigo_suframa.className 	= 'caixadetexto'
        document.form.chkt_suframa_ativo.disabled 	= false
        document.form.txt_codigo_suframa.focus()
    }else {
        document.form.txt_codigo_suframa.value = ''
        document.form.txt_codigo_suframa.className 	= 'textdisabled'
        document.form.chkt_suframa_ativo.disabled 	= true
        document.form.chkt_suframa_ativo.checked 	= false
    }
}

function excluir_cliente_matriz() {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA DESATRELAR ESSE CLIENTE MATRIZ DESSA FILIAL ? ')
    if(resposta == true) {
        document.form.hdd_desatrelar_matriz.value = 1
        validar()
    }
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep_cobranca() {
    var id_pais = eval('<?=$campos_cliente[0]['id_pais'];?>')
    if(id_pais == 31) {//SÛ buscar· o CEP se for Brasil
        if(document.form.txt_cep_cobranca.value == '') {//Verifico se o CEP de CobranÁa È v·lido ...
            document.form.txt_endereco_cobranca.value = ''
            document.form.txt_bairro_cobranca.value = ''
            document.form.txt_cidade_cobranca.value = ''
            document.form.txt_estado_cobranca.value = ''
        }else {
            if(document.form.txt_cep_cobranca.value.length < 9) {//Verifico se o CEP de CobranÁa È v·lido ...
                alert('CEP INV¡LIDO !')
                document.form.txt_cep_cobranca.focus()
                document.form.txt_cep_cobranca.select()
                return false
            }else {
                cep.location = '../../classes/cep/buscar_cep.php?txt_cep_cobranca='+document.form.txt_cep_cobranca.value
            }
        }
    }
}

function atualizar_abaixo() {
    if(typeof(window.top.opener.document.form) == 'object') {
        var valor = eval('<?=$valor;?>')
        if(document.form.nao_atualizar.value == 0 && valor == 1) window.top.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_cod_cliente.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar' value='0'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>' onclick='validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='nao_exibir_menu' value='<?=$nao_exibir_menu;?>'>
<input type='hidden' name='hdd_desatrelar_matriz'>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            Raz&atilde;o Social / CÛdigo do Cliente (Lotus):
        </td>
        <td width='50%'>
            Nome Fantasia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue' size='-1'>
                <b><?=$campos_cliente[0]['razaosocial'];?></b>
            </font>
            &nbsp;<b>/</b>&nbsp;
            <input type='text' name="txt_cod_cliente" value="<?=$campos_cliente[0]['cod_cliente'];?>" title="CÛdigo do Cliente" maxlength="5" size="4" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='textdisabled' disabled>
        </td>
        <td>
            <font color='darkblue' size='-1'>
                <b><?=$campos_cliente[0]['nomefantasia'];?></b>
            </font>
        </td>
    </tr>
    <!--*******************Dados de EndereÁo de CobranÁa*******************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            DADOS DE ENDERE«O DE COBRAN«A 
            <font color='yellow'>
                (Utilizado na parte de impress„o de CÛpia de Duplicata)
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            PaÌs de CobranÁa:
        </td>
        <td>
            CEP de CobranÁa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais_cobranca' onchange='habilitar_pais_cobranca()' class='combo'>
            <?
                $sql = "SELECT `id_pais`, `pais` 
                        FROM `paises` 
                        ORDER BY `pais` ";
                echo combos::combo($sql, $campos_cliente[0]['id_pais_cobranca']);
            ?>
            </select>
        </td>
        <td>
            <input type='text' name='txt_cep_cobranca' value='<?=$campos_cliente[0]['cep_cobranca'];?>' title='Digite o Cep de CobranÁa' onfocus="if(this.className == 'textdisabled') document.form.txt_endereco_cobranca.focus()" onkeyup="verifica(this, 'cep', '', '', event)" onblur='buscar_cep_cobranca()' size='20' maxlength='9' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Endere&ccedil;o de CobranÁa:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;
            N.&#176; / Complemento: 
        </td>
        <td>
            Bairro de CobranÁa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco_cobranca' value="<?=stripslashes($campos_cliente[0]['endereco_cobranca']);?>" size='43' title='Endere&ccedil;o de CobranÁa' maxlength='50' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento_cobranca.focus()" class='textdisabled'>
            &nbsp;
            <input type='text' name='txt_num_complemento_cobranca' value='<?=$campos_cliente[0]['num_complemento_cobranca'];?>' title='Digite o N&uacute;mero, Complemento, ... de CobranÁa' size='10' maxlength='50' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro_cobranca' value="<?=stripslashes($campos_cliente[0]['bairro_cobranca']);?>" title='Bairro de CobranÁa' size='35' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento_cobranca.focus()" class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade de CobranÁa:
        </td>
        <td>
            Estado de CobranÁa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cidade_cobranca' value="<?=stripslashes($campos_cliente[0]['cidade_cobranca']);?>" title='Cidade de CobranÁa' size='35' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento_cobranca.focus()" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_estado_cobranca' value='<?=$campos_cliente[0]['sigla'];?>' title='Estado de CobranÁa' size='35' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento_cobranca.focus()" class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            OUTRAS INFORMA«’ES
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Cliente Matriz:</b>
            </font>
        </td>
        <td>
            Tipo de Cliente:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
//Busca do Cliente Matriz ...
            $sql = "SELECT `id_cliente_matriz` 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_matriz = bancos::sql($sql);
            if($campos_matriz[0]['id_cliente_matriz'] <> 0) {//Significa que existe Cliente Matriz Atrelado ...
                $sql = "SELECT IF(`nomefantasia` = '', `razaosocial`, `nomefantasia`) AS cliente 
                        FROM `clientes` 
                        WHERE `id_cliente` = '".$campos_matriz[0]['id_cliente_matriz']."' LIMIT 1 ";
                $campos_cliente_matriz = bancos::sql($sql);
        ?>
            <font title='Cliente Matriz' color='darkblue' style='cursor:help'>
                <b><?=$campos_cliente_matriz[0]['cliente'];?></b>
                <img src = "../../../imagem/menu/excluir.png" border='0' title="Excluir Cliente Matriz" alt="Excluir Cliente Matriz" onClick="excluir_cliente_matriz('<?=$campos_matriz[0]['id_cliente_matriz'];?>')">
            </font>
        <?
            }else {
        ?>
                <input type='button' name='cmd_consultar_cliente_matriz' value='Atrelar Cliente Matriz' title='Atrelar Cliente Matriz' onclick="nova_janela('consultar_cliente_matriz.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '500', '980', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:black' class='botao'>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147 || $_SESSION['id_funcionario'] == 136) {
                $disabled 	= '';
                $class		= 'combo';
            }else {//Demais usu·rios n„o podem mexer nessa combo ...
                $disabled 	= 'disabled';
                $class		= 'textdisabled';
            }
        ?>
            <select name='cmb_tipo_cliente' title='Selecione o Tipo de Cliente' class='<?=$class;?>' <?=$disabled?>>
            <?
                $sql = "SELECT `id_cliente_tipo`, `tipo` 
                        FROM `clientes_tipos` ";
                echo combos::combo($sql, $campos_cliente[0]['id_cliente_tipo']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Perfil de Cliente:</b>
        </td>
        <td>
            <b>Volume de Compras:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_perfil_cliente' title='Selecione o Perfil de Cliente' class='combo'>
            <?
                $sql = "SELECT `id_cliente_perfil`, `perfil` 
                        FROM `clientes_perfils ";
                echo combos::combo($sql, $campos_cliente[0]['id_cliente_perfil']);
            ?>
            </select>
        </td>
        <td>
            <select name='cmb_volume_compras' title="Selecione o Volume de Compras" class='textdisabled' disabled>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos_cliente[0]['volume_compras'] == 'A') {
                        $selecteda = 'selected';
                    }else if($campos_cliente[0]['volume_compras'] == 'B') {
                        $selectedb = 'selected';
                    }else if($campos_cliente[0]['volume_compras'] == 'C') {
                        $selectedc = 'selected';
                    }else if($campos_cliente[0]['volume_compras'] == 'D') {
                        $selectedd = 'selected';
                    }else if($campos_cliente[0]['volume_compras'] == 'E') {
                        $selectede = 'selected';
                    }
                ?>
                <option value="A" <?=$selecteda;?>>A</option>
                <option value="B" <?=$selectedb;?>>B</option>
                <option value="C" <?=$selectedc;?>>C</option>
                <option value="D" <?=$selectedd;?>>D</option>
                <option value="E" <?=$selectede;?>>E</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?$checked = (!empty($campos_cliente[0]['trading'])) ? 'checked' : '';?>
            <input type='checkbox' name='chkt_trading' value='1' id='trading' class='checkbox' <?=$checked;?>>
            <label for='trading'>
                <b>… COMERCIAL EXPORTADOR (TRADING).
                    <font color='red'>
                        <br/>(S” MARCAR SE O CLIENTE ENVIAR COMPROVANTE QUE … TRADING. AP”S A VENDA, CLIENTE TEM DE ENVIAR TODOS OS COMPROVANTES AP”S EXPORTAR O PRODUTO)
                    </font>
                </b>
            </label>
        </td>
        <td>
            <?
                $checked 	= (!empty($campos_cliente[0]['base_pag_comissao'])) ? 'checked' : '';
                //Somente o Roberto, o Wilson, D·rcio, Netto e  Nishi que podem alterar esse campo ...
                $disabled 	= ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147 || $_SESSION['id_funcionario'] == 136) ? '' : 'disabled';
            ?>
            <input type='checkbox' name='chkt_base_pag_comissao' value='1' id='base_pag_comissao' class='checkbox' <?=$checked;?> <?=$disabled;?>>
            <label for='base_pag_comissao'><b>PAGAR COMISS√O COMO CIDADE DE S√O PAULO.</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?$checked = ($campos_cliente[0]['tributar_ipi_rev'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_tributar_ipi_rev' value='S' id='tributar_ipi_rev' class='checkbox' <?=$checked;?>>
            <label for='tributar_ipi_rev'>
                <b>TRIBUTAR o PA OC=Revenda COMO INDUSTRIAL:
                <font color='red'>
                    <br/>(ERA TRIBUTAR IPI DE PA REVENDA)
                </font>
                </b>
            </label>
        </td>
        <td>
            <?$checked = ($campos_cliente[0]['optante_simples_nacional'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_optante_simples_nacional' value='S' id='optante_simples_nacional' class='checkbox' <?=$checked;?>>
            <label for='optante_simples_nacional'>
                <b>OPTANTE PELO SIMPLES NACIONAL
                    <font color='red'>
                        <br/>(CONFIRMAR CADASTRO DO CLIENTE NO SINTEGRA)
                    </font>
                </b>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?$checked = (!empty($campos_cliente[0]['artigo_isencao'])) ? 'checked' : '';?>
            <input type='checkbox' name='chkt_artigo_isencao' value='1' id='artigo_isencao' class='checkbox' <?=$checked;?>>
            <label for='artigo_isencao'>
                <b>SUSPENSO IPI, CONF.ART.29, PAR¡GRAFO 1, ALÕNEA A E B, LEI 10637/02.</b>
                <font color='red'>
                    <br/><b>(S” MARCAR SE O CLIENTE ENVIAR COMPROVANTE QUE … ISENTO E COMPRAR COMO CONSUMO. A ST TAMB…M SER¡ ZERADA)</b>
                </font>
            </label>
        </td>
        <td>
            <?$checked = ($campos_cliente[0]['isento_st'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_isento_st' value='1' id='isento_st' class='checkbox' <?=$checked;?>>
            <label for='isento_st'>
                <b>ISENTO DE SUBSTITUI«√O TRIBUT¡RIA (TODOS OS PRODUTOS)
                <font color='red'>
                    <br/>(S” MARCAR SE O CLIENTE ENVIAR COMPROVANTE QUE … ISENTO)
                </font>
                </b>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?$checked = ($campos_cliente[0]['certificado_qualidade'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_certificado_qualidade' value='S' id='certicado_qualidade' class='checkbox' <?=$checked;?>>
            <label for='certicado_qualidade'><b>SEMPRE EMITIR CERTIFICADO DE QUALIDADE</b></label>
        </td>
        <td>
            <?$checked = ($campos_cliente[0]['isento_st_em_pinos'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_isento_st_em_pinos' value='1' id='isento_st_em_pinos' class='checkbox' <?=$checked;?>>
            <label for='isento_st_em_pinos'>
                <b>ISENTO DE SUBSTITUI«√O TRIBUT¡RIA (EM PINOS)</b>
            </label>
        </td>                
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Tipo de Suframa:
            <font color='red'>
                <br/><b>(CONFIRMAR CADASTRO DO CLIENTE NO SINTEGRA)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_suframa' title='Selecione o Tipo de Suframa' onchange='habilitar_suframa()' class='combo'>
            <?
                if($campos_cliente[0]['tipo_suframa'] == 0 || $campos_cliente[0]['tipo_suframa'] == '') {
                    $selected_suframa0 	= 'selected';
                    $class_suframa      = 'textdisabled';
                    $disabled_suframa	= 'disabled';
                }else if($campos_cliente[0]['tipo_suframa'] == 1) {
                    $selected_suframa1 	= 'selected';
                    $class_suframa      = 'caixadetexto';
                    $disabled_suframa	= '';
                }else if($campos_cliente[0]['tipo_suframa'] == 2) {
                    $selected_suframa2 	= 'selected';
                    $class_suframa      = 'caixadetexto';
                    $disabled_suframa	= '';
                }else if($campos_cliente[0]['tipo_suframa'] == 3) {
                    $selected_suframa3 	= 'selected';
                    $class_suframa      = 'caixadetexto';
                    $disabled_suframa	= '';
                }
            ?>
                <option value='' style='color:red' <?=$selected_suframa0;?>>SELECIONE</option>
                <option value='1' <?=$selected_suframa1;?>>¡rea de Livre ComÈrcio - ISENTO (ICMS/IPI)</option>
                <option value='2' <?=$selected_suframa2;?>>Zona Franca de Manaus - ISENTO (ICMS/PIS/COFINS/IPI)</option>
                <option value='3' <?=$selected_suframa3;?>>AmazÙnia Ocidental - ISENTO (IPI)</option>			
            </select>
        </td>
        <td>
            <input type='text' name='txt_codigo_suframa' value='<?=$campos_cliente[0]['cod_suframa'];?>' title='Digite o CÛdigo do Suframa' size='20' onfocus="if(this.className == 'textdisabled') document.form.txt_pta.focus()" class='<?=$class_suframa;?>'>
            &nbsp;
            <?$checked = ($campos_cliente[0]['suframa_ativo'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_suframa_ativo' value='S' id='suframa_ativo' class='checkbox' <?=$checked;?> <?=$disabled_suframa;?>>
            <label for='suframa_ativo'><b>SUFRAMA ATIVO</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            PTA: 
        </td>
        <td>
            <b>Tipo de Faturamento:</b> 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
                <input type='text' name="txt_pta" value="<?=$campos_cliente[0]['pta'];?>" title="Digite o PTA" size="21" maxlength="18" class='caixadetexto'>
                <font color='red'>
                        &nbsp;<b>(CR…DITO TRIBUT¡RIO GERALMENTE UTILIZADO EM MG)</b>
                </font>
        </td>
        <td>
            <select name='cmb_tipo_faturamento' title='Selecione o Tipo de Faturamento' class='combo'>
            <?
                    if($campos_cliente[0]['tipo_faturamento'] == 1) {
                        $selected_alba = 'selected';
                    }else if($campos_cliente[0]['tipo_faturamento'] == 2) {
                        $selected_tool = 'selected';
                    }else if($campos_cliente[0]['tipo_faturamento'] == 'Q') {
                        $selected_qualquer_emp 	= 'selected';
                    }else if($campos_cliente[0]['tipo_faturamento'] == 'S') {
                        $selected_separadamente	= 'selected';
                    }
                ?>
                <option value='1' <?=$selected_alba;?>>TUDO PELA ALBAFER</option>
                <option value='2' <?=$selected_tool;?>>TUDO PELA TOOL MASTER</option>
                <option value='Q' <?=$selected_qualquer_emp;?>>QUALQUER EMPRESA</option>
                <option value='S' <?=$selected_separadamente;?>>SEPARADAMENTE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Texto da Nota: 
            <textarea name='txt_texto_da_nota' title='Texto da Nota' rows='5' cols='100' maxlength='500' class='caixadetexto'><?=$campos_cliente[0]['texto_da_nota'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            //Quando essa tela, for aberta como Pop-Up ou for pedido para n„o exibir menu ent„o, n„o exibo esse bot„o de Voltar ...
            if($pop_up != 1 && $nao_exibir_menu != 1) {
        ?>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.parent.location = 'alterar.php<?=$parametro;?>'" class='botao'>
        <?
            }
            //Quando essa tela, for aberta como Pop-Up, n„o exibo esses botıes ...
            if($pop_up != 1) {
        ?>
        <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
        <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }else {
        ?>
        <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='parent.close()' class='botao'>
        <?		
            }
        ?>
        </td>
    </tr>
    <!--Aqui busco o EndereÁo atravÈs do Cep do Cliente ...-->
    <iframe name='cep' id='cep' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
</table>
</form>
</body>
</html>