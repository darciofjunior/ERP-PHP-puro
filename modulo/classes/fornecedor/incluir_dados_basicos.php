<?
if(!class_exists('segurancas')) require '../../../lib/segurancas.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('menu'))       require '../../../lib/menu/menu.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas'))  require '../../../lib/genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
session_start('funcionarios');

$mensagem[1] = "<font class = 'confirmacao'>FORNECEDOR INCLUÕDO COM SUCESSO.</font>";
$mensagem[2] = "<font class = 'confirmacao'>FORNECEDOR REATIVADO COM SUCESSO.</font>";
$mensagem[3] = "<font class = 'erro'>J¡ EXISTE OUTRO FORNECEDOR COM O MESMO \"NOME FANTASIA\" OU \"RAZ√O SOCIAL\" NA MESMA UNIDADE FEDERAL.</font>";

/********************************************ReativaÁ„o do Cadastro de Fornecedor********************************************/
if(!empty($_GET['id_fornecedor'])) {//Significa que o Usu·rio optou por reativar o cadastro de Fornecedor ...
    $sql = "UPDATE `fornecedores` SET `ativo` = '1' WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}
/***************************************************Inclus„o de Fornecedor***************************************************/
if(isset($_POST['txt_razao_social'])) {
    //Busca o id_uf atravÈs do campo Estado ...
    $sql = "SELECT id_uf 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
    $campos_uf = bancos::sql($sql);
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
    //Verifico se existe um outro Fornecedor com o nesmo Nome Fantasia ou Raz„o Social ...
    $condicao       = (!empty($nome_fantasia)) ? " (`nomefantasia` LIKE '$nome_fantasia%' AND `razaosocial` LIKE '$razao_social') " : " `razaosocial` LIKE '$razao_social%' ";
    
    $sql = "SELECT id_fornecedor, IF(razaosocial = '', nomefantasia, razaosocial) fornecedor, ativo 
            FROM `fornecedores` 
            WHERE $condicao 
            AND `id_uf` = '".$campos_uf[0]['id_uf']."' 
            AND `id_fornecedor` <> '$id_fornecedor' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_fornecedor = bancos::sql($sql);
    if(count($campos_fornecedor) == 1) {
        echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_fornecedor[0]['id_fornecedor'].' - '.utf8_encode($campos_fornecedor[0]['fornecedor'])."</center></b></font>";
        $executar_update = 0;
        $valor = 3;
    }else {//Fornecedor n„o Existe ...
        $razao_social 	= strtoupper(str_replace('%', ' ', $razao_social));
        $nome_fantasia 	= strtoupper($nome_fantasia);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
        $id_uf          = (!empty($campos_uf[0]['id_uf'])) ? "'".$campos_uf[0]['id_uf']."'" : 'NULL';

        //Insere o Novo Fornecedor ...
        $sql = "INSERT INTO `fornecedores` (`id_fornecedor`, `id_uf`, `id_pais`, `nomefantasia`, `razaosocial`, `insc_est`, `cnpj_cpf`, `rg`, `orgao`, `endereco`, `num_complemento`, `bairro`, `cep`, `cidade`, `ddd_fone1`, `fone1`, `ddd_fone2`, `fone2`, `ddd_fax`, `fax`, `email`, `site`, `ativo`) VALUES (NULL, $id_uf, '$_POST[cmb_pais]', '$_POST[txt_nome_fantasia]', '$_POST[txt_razao_social]', '$_POST[hdd_insc_estadual]', '$_POST[txt_cnpj_cpf]', '$_POST[hdd_rg]', '$_POST[hdd_orgao]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_bairro]', '$_POST[txt_cep]', '$_POST[txt_cidade]', '$_POST[txt_ddd_comercial]', '$_POST[txt_tel_comercial]', '$_POST[txt_ddd_comercial2]', '$_POST[txt_tel_comercial2]', '$_POST[txt_ddd_fax]', '$_POST[txt_tel_fax]', '$_POST[txt_email]', '$_POST[txt_pagina_web]', '1')";
        bancos::sql($sql);
        $id_fornecedor = bancos::id_registro();
        
        if(!empty($_POST['txt_observacao'])) {
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_fornecedor]', '$_SESSION[id_funcionario]', '5', '".ucfirst(strtolower($_POST['txt_observacao']))."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $valor = 1;
    }
}
/*************************************************************************************************************************/
?>
<html>
<title>.:: Incluir Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
//Habilita a Unidade Federal
function alterar_pais() {
    //Quando o PaÌs È Internacional n„o se coloca CNPJ ou CPF ...
    if(document.form.cmb_pais.value != 31) {
        document.form.txt_cnpj_cpf.value = ''
        document.form.txt_cnpj_cpf.className = 'textdisabled'
        ajax('../../classes/fornecedor/incluir_dados_basicos2.php', 'incluir_dados_basicos')
    }else {//Se for do Brasil ...
        window.location = 'incluir.php'
    }
}

function validar() {
//PaÌs
    if(!combo('form', 'cmb_pais', '', 'SELECIONE UM PAÕS !')) {
        return false
    }
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
    if(document.form.cmb_pais.value == 31) {
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
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
    document.form.txt_endereco.value = document.form.txt_endereco.value.toUpperCase()
    document.form.txt_bairro.value = document.form.txt_bairro.value.toUpperCase()
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
    if(document.form.cmb_pais.value == 31) {//SÛ buscar· o CEP se for Brasil
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

function verificar_teclas(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13 || event.keyCode == 35) {//Se Enter ou End faz a Consulta.
            pesquisar_cnpj_cpf()
        }
    }else {
        if(event.which == 13 || event.which == 35) {//Se Enter ou End faz a Consulta.
            pesquisar_cnpj_cpf()
        }
    }
}

function pesquisar_cnpj_cpf() {
    if(document.form.cmb_pais.value == 31) {//PaÌs Nacional ...
        if(document.form.txt_cnpj_cpf.value == '') {//Verifica validaÁ„o do cpf ou cnpj
            alert('DIGITE O CNPJ / CPF OU CLIQUE NO BOT√O CLONAR CLIENTE !')
            document.form.txt_cnpj_cpf.focus()
            return false
        }else {
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
        ajax('../../classes/fornecedor/incluir_dados_basicos2.php?txt_cnpj_cpf='+document.form.txt_cnpj_cpf.value, 'incluir_dados_basicos')
    }else {//PaÌs Estrangeiro ...
        ajax('../../classes/fornecedor/incluir_dados_basicos2.php', 'incluir_dados_basicos')
    }
}

function clonar_cliente() {
    ajax('../../classes/fornecedor/incluir_dados_basicos2.php?clonar_cliente=1', 'incluir_dados_basicos')
    html5Lightbox.showLightbox(7, '../../classes/fornecedor/clonar_cliente.php')
}
</Script>
</head>
<body onload='document.form.txt_cnpj_cpf.focus()'>
<form name='form' method='post' action=''>
<!--Essas caixas s„o utilizadas quando È Clonado um Cliente de Vendas--> 
<input type='hidden' name='hdd_insc_estadual'>
<input type='hidden' name='hdd_rg'>
<input type='hidden' name='hdd_orgao'>
<!--*****************************************************************-->
<table width='850' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Fornecedor - 
            <font color='yellow'>
                <b>Dados B·sicos</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            <b>PaÌs:</b>
        </td>
        <td width='50%'>
            <b>CNPJ ou CPF:<b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais' onchange='alterar_pais()' class='combo'>
                <?=combos::combo('SELECT id_pais, pais FROM paises ', 31);?>
            </select>
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CPF ou CNPJ' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar_teclas(event)" onfocus="if(this.className == 'textdisabled') document.form.txt_razao_social.focus()" size='20' maxlength='18' class='caixadetexto'>
            &nbsp;
            <img src = '../../../imagem/menu/pesquisar.png' id='img_pesquisar' onclick='pesquisar_cnpj_cpf()' title='Pesquisar' style='cursor:pointer' border='0'>
            &nbsp;-&nbsp;
            <input type='button' name="cmd_clonar_cliente" value='Clonar Cliente' title='Clonar Cliente' onclick='clonar_cliente()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<div id='incluir_dados_basicos'></div>
</form>
</body>
</html>