<?
if(!class_exists('segurancas')) require '../../../lib/segurancas.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas'))  require '../../../lib/genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $detalhes = $_POST['detalhes'];
}else {
    $detalhes = $_GET['detalhes'];
}
if(empty($detalhes)) 		require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
session_start('funcionarios');

$mensagem[1] = "<font class = 'confirmacao'>CLIENTE INCLUÕDO COM SUCESSO.</font>";
$mensagem[2] = "<font class = 'confirmacao'>CLIENTE REATIVADO COM SUCESSO.</font>";
$mensagem[3] = "<font class = 'erro'>J¡ EXISTE OUTRO CLIENTE COM O MESMO \"NOME FANTASIA\" OU \"RAZ√O SOCIAL\" NA MESMA UNIDADE FEDERAL, MESMA CIDADE E MESMO N.∫ / COMPLEMENTO.</font>";

/********************************************ReativaÁ„o do Cadastro de Cliente********************************************/
if(!empty($_GET['id_cliente'])) {//Significa que o Usu·rio optou por reativar o cadastro de Cliente ...
    $sql = "UPDATE `clientes` SET `ativo` = '1' WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}
/***************************************************Inclus„o de Cliente***************************************************/
if(isset($_POST['txt_razao_social'])) {
    //Busca o id_uf atravÈs do campo Estado ...
    $sql = "SELECT `id_uf` 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
    $campos_uf = bancos::sql($sql);
    /*************************Controle para saber se o Cliente j· Existe sÛ que pela Raz„o Social ou Nome Fantasia*************************/
    //Verifico se esse Cliente existe pelo Nome Fantasia ...
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
    //Verifico se esse Cliente existe pela Raz„o Social ...
    for($i = 0; $i < strlen($_POST['txt_razao_social']); $i++) {
        if(substr($_POST['txt_razao_social'], $i, 1) == ' ') {//Se o caractÈr for EspaÁo ...
            $razao_social.= '%';
        }else {//Se o caractÈr n„o for EspaÁo ...
            if(substr($_POST['txt_razao_social'], $i, 1) != '.') {//E nem Ponto ...
                $razao_social.= substr($_POST['txt_razao_social'], $i, 1);
            }
        }
    }
    $razao_social   = trim($razao_social);
    /**************************************************************************************************************************************/
    //Verifico se existe um outro Cliente com o nesmo Nome Fantasia ou Raz„o Social ...
    $condicao       = (!empty($nome_fantasia)) ? " (`nomefantasia` LIKE '$nome_fantasia%' AND `razaosocial` LIKE '$razao_social') " : " `razaosocial` LIKE '$razao_social%' ";

    $sql = "SELECT `id_cliente`, IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente, `ativo` 
            FROM `clientes` 
            WHERE $condicao 
            AND `cnpj_cpf` = '$_POST[txt_cnpj_cpf]' 
            AND `id_uf` = '".$campos_uf[0]['id_uf']."' 
            AND `cidade` = '".addslashes($_POST['txt_cidade'])."' 
            AND `num_complemento` = '".addslashes($_POST['txt_num_complemento'])."' 
            AND `id_cliente` <> '$id_cliente' 
            AND `ativo`	= '1' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    if(count($campos_cliente) == 1) {
        echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_cliente[0]['id_cliente'].' - '.utf8_encode($campos_cliente[0]['cliente'])."</center></b></font>";
        $executar_update = 0;
        $valor = 3;
    }else {//Cliente n„o Existe ...
        $razao_social 	= strtoupper(str_replace('%', ' ', $razao_social));
        $nome_fantasia 	= strtoupper($nome_fantasia);
        $matriz         = (!empty($_POST[matriz])) ? 'S' : 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
        $id_uf          = (!empty($campos_uf[0]['id_uf'])) ? "'".$campos_uf[0]['id_uf']."'" : 'NULL';
        
        if(!empty($_SESSION[id_funcionario])) {//99% dos casos, ser„o os funcion·rios da Albafer que ir„o acessar nosso sistema ...
            //Mudo os nomes das vari·veis aqui p/ n„o conflitar com as que existem na Sess„o e s„o esses mesmos nomes ...
            $id_funcionario_gravar  = $_SESSION[id_funcionario];
        }else {//No demais representantes ...
            $id_funcionario_gravar  = 'NULL';
        }
        
        //Insere o Novo Cliente ...
        $sql = "INSERT INTO `clientes` (`id_cliente`, `id_pais`, `id_uf`, `id_funcionario`, `matriz`, `nomefantasia`, `razaosocial`, `cnpj_cpf`, `endereco`, `num_complemento`, `bairro`, `cep`, `cidade`, `ddi_com`, `ddd_com`, `telcom`, `telfax`, `ddi_fax`, `ddd_fax`, `email`, `data_atualizacao_emails`, `pagweb`, `data_cadastro`, `ativo`) VALUES (NULL, '$_POST[cmb_pais]', $id_uf, $id_funcionario_gravar, '$matriz', '$nome_fantasia', '$razao_social', '$_POST[txt_cnpj_cpf]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_bairro]', '$_POST[txt_cep]', '$_POST[txt_cidade]', '$_POST[txt_ddi_comercial]', '$_POST[txt_ddd_comercial]', '$_POST[txt_tel_comercial]', '$_POST[txt_tel_fax]', '$_POST[txt_ddi_fax]', '$_POST[txt_ddd_fax]', '$_POST[txt_email]', '".date('Y-m-d')."', '$_POST[txt_pagina_web]', '".date('Y-m-d')."', '1')";
        bancos::sql($sql);
        $id_cliente = bancos::id_registro();
        
        //Insere o contato digitado pelo Usu·rio referente ao novo Cliente gerado ...
        $sql = "INSERT INTO `clientes_contatos` (`id_cliente_contato`, `id_cliente`, `id_departamento`, `nome`, `opcao_phone`, `ddi`, `ddd`, `telefone`, `email`, `ativo`) VALUES (NULL, '$id_cliente', '4', '$_POST[txt_contato]', '1', '$_POST[txt_ddi_comercial]', '$_POST[txt_ddd_comercial]', '$_POST[txt_tel_comercial]', '$_POST[txt_email]', '1') ";
        bancos::sql($sql);
        $id_cliente_contato = bancos::id_registro();
        
        //Aqui eu busco todas as Empresas Divisıes que est„o cadastradas no Sistema ...
        $sql = "SELECT id_empresa_divisao 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ";
        $campos_empresas_divisoes = bancos::sql($sql);
        $linhas_empresas_divisoes = count($campos_empresas_divisoes);
        //Insere o Representante "DIRETO em todas as Divisıes existentes" para o Cliente gerado ...
        for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
            $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`, `desconto_cliente`) VALUES (NULL, '$id_cliente', '1', '".$campos_empresas_divisoes[$i]['id_empresa_divisao']."', '0') ";
            bancos::sql($sql);
        }
        genericas::atualizar_clientes_no_site_area_cliente($id_cliente);
        
        if(!empty($_POST['txt_observacao'])) {
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_funcionario_gravar, '5', '".ucfirst(strtolower($_POST['txt_observacao']))."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $valor = 1;

        if($_POST['chkt_gerar_orcamento'] == 'S') {//Aqui com essa opÁ„o marcada, gero um OrÁamento automaticamente apÛs a InserÁ„o do Cliente ...
            $data_emissao 	= date('Y-m-d');
            $data_sys 		= date('Y-m-d H:i:s');
            $sql = "INSERT INTO `orcamentos_vendas` (`id_orcamento_venda`, `id_cliente_contato`, `id_cliente`, `id_funcionario`, `finalidade`, `nota_sgd`, `data_emissao`, `prazo_a`, `data_sys`, `status`) VALUES (NULL, '$id_cliente_contato', '$id_cliente', $id_funcionario_gravar, 'R', 'N', '$data_emissao', '28', '$data_sys', '1') ";
            bancos::sql($sql);
            $id_orcamento_venda = bancos::id_registro();
?>
        <Script Language = 'JavaScript'>
            parent.document.location = '../../vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
        </Script>
<?
        }
    }
}
/*************************************************************************************************************************/
?>
<html>
<title>.:: Incluir Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
//Habilita a Unidade Federal
function alterar_pais() {
    //Quando o PaÌs È Internacional n„o se coloca CNPJ ou CPF ...
    if(document.form.cmb_pais.value != 31) {
        document.form.txt_cnpj_cpf.value        = ''
        document.form.txt_cnpj_cpf.className    = 'textdisabled'
        ajax('../../classes/cliente/incluir_dados_basicos2.php', 'incluir_dados_basicos')
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
//Contato
    if(!texto('form', 'txt_contato', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CONTATO', '2')) {
        return false
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
        if(!texto('form', 'txt_endereco', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/_ ", 'ENDERE«O', '2')) {
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
    if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞_/ ", 'N⁄MERO / COMPLEMENTO', '2')) {
        return false
    }
//DDI Comercial
    if(document.form.txt_ddi_comercial.value != '') {
        if(!texto('form', 'txt_ddi_comercial', '1', '1234567890', 'DDI COMERCIAL', '2')) {
            return false
        }
    }
//DDD Comercial
    if(document.form.txt_ddd_comercial.value != '') {
        if(!texto('form', 'txt_ddd_comercial', '1', '1234567890', 'DDD COMERCIAL', '2')) {
            return false
        }
    }
//Telefone Comercial
    if(!texto('form', 'txt_tel_comercial', '7', '1234567890', 'TELEFONE COMERCIAL','2')) {
        return false
    }
//DDI Fax
    if(document.form.txt_ddi_fax.value != '') {
        if(!texto('form', 'txt_ddi_fax', '1', '1234567890', 'DDI FAX', '2')) {
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
        if(!texto('form', 'txt_tel_fax', '7', '1234567890', 'TELEFONE FAX', '2')) {
            return false
        }
    }
//E-mail
    if(!new_email('form', 'txt_email')) {
        return false
    }
//SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, afinal albafer È e-mail daqui da empresa ...
    if(document.form.txt_email.value.indexOf('albafer') != -1) {
        alert('ENDERE«O DE E-MAIL INV¡LIDO !!!\n\nE-MAIL COM DADOS DAQUI DA EMPRESA !')
        document.form.txt_email.focus()
        document.form.txt_email.select()
        return false
    }
//Novo Tipo de Cliente
    if(!combo('form', 'cmb_tipo_cliente', '', 'SELECIONE UM TIPO DE CLIENTE !')) {
        return false
    }
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
    document.form.txt_razao_social.value    = document.form.txt_razao_social.value.toUpperCase()
    document.form.txt_nome_fantasia.value   = document.form.txt_nome_fantasia.value.toUpperCase()
    document.form.txt_endereco.value        = document.form.txt_endereco.value.toUpperCase()
    document.form.txt_bairro.value          = document.form.txt_bairro.value.toUpperCase()
}

function copiar_telefone() {
    document.form.txt_ddi_fax.value = document.form.txt_ddi_comercial.value
    document.form.txt_ddd_fax.value = document.form.txt_ddd_comercial.value
    document.form.txt_tel_fax.value = document.form.txt_tel_comercial.value
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
            alert('DIGITE O CNPJ OU CPF !')
            document.form.txt_cnpj_cpf.focus()
            return false
        }else {
            if(document.form.txt_cnpj_cpf.value.length > 11) {//SÛ verifica CNPJ
                if(!cnpj('form', 'txt_cnpj_cpf')) {
                    document.form.txt_cnpj_cpf.focus()
                    document.form.txt_cnpj_cpf.select()
                    return false
                }
            }else {//SÛ verifica CPF
                if(!cpf('form', 'txt_cnpj_cpf')) {
                    document.form.txt_cnpj_cpf.focus()
                    document.form.txt_cnpj_cpf.select()
                    return false
                }
            }
        }
        ajax('../../classes/cliente/incluir_dados_basicos2.php?txt_cnpj_cpf='+document.form.txt_cnpj_cpf.value, 'incluir_dados_basicos')
    }else {//PaÌs Estrangeiro ...
        ajax('../../classes/cliente/incluir_dados_basicos2.php', 'incluir_dados_basicos')
    }
}
</Script>
</head>
<body onload='document.form.txt_cnpj_cpf.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF;?>'>
<input type='hidden' name='detalhes' value='<?=$detalhes;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Cliente - 
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
            <b>CNPJ / CPF:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_pais' onchange='alterar_pais()' class='combo'>
            <?
                $sql = "SELECT `id_pais`, `pais` 
                        FROM `paises` 
                        ORDER BY `pais` ";
                echo combos::combo($sql, 31);
            ?>
            </select>
        </td>
        <td>
            <input type="text" name='txt_cnpj_cpf' title='Digite o CPF ou CNPJ' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar_teclas(event)" onfocus="if(this.className == 'textdisabled') document.form.txt_razao_social.focus()" size='20' maxlength='18' class='caixadetexto'>
            &nbsp;
            <img src = "../../../imagem/menu/pesquisar.png" onclick="pesquisar_cnpj_cpf()" title='Pesquisar' style='cursor:pointer' border='0'>
        </td>
    </tr>
</table>
<div id='incluir_dados_basicos'></div>
</form>
</body>
</html>