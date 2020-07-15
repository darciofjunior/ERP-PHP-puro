<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');//Essa biblioteca È chamada aqui porque a mesma È utilizada dentro do Custos ...
require('../../../lib/custos.php');//Essa biblioteca È chamada aqui porque a mesma È utilizada dentro da Vendas ...
require('../../../lib/data.php');
require('../../../lib/intermodular.php');//Essa biblioteca È chamada aqui porque a mesma È utilizada dentro da Vendas ...
require('../../../lib/vendas.php');
session_start('funcionarios');

$mensagem[1] = "<font class = 'erro'>J&Aacute; EXISTE UM CLIENTE COM ESSE CNPJ OU CPF !</font>";
$mensagem[2] = "<font class = 'erro'>N√O PODE SER ALTERADA A UNIDADE FEDERAL DESTE CLIENTE DEVIDO O MESMO POSSUIR NF !!!\nA ALTERA«√O DA UNIDADE FEDERAL SER FEITA APRESENTANDO A ALTERA«√O CONTRATUAL ONDE CONSTA A MUDAN«A DE ENDERE«O ENTRE ESTADOS.</font>";
$mensagem[3] = "<font class = 'confirmacao'>CLIENTE ALTERADO COM SUCESSO.</font>";
$mensagem[4] = "<font class = 'erro'>J¡ EXISTE OUTRO CLIENTE COM O MESMO \"NOME FANTASIA\" OU \"RAZ√O SOCIAL\" NA MESMA UNIDADE FEDERAL, MESMA CIDADE E MESMO N.∫ / COMPLEMENTO.</font>";

$id_cliente     = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];
$pop_up 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
$nao_exibir_menu= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['nao_exibir_menu'] : $_GET['nao_exibir_menu'];

if(isset($_POST['txt_razao_social'])) {//AlteraÁ„o de Cliente ...
    $executar_update = 1;
    //Busco alguns dados que ser„o utilizados mais abaixo, antes da alteraÁ„o de cadastro ...
    $sql = "SELECT `id_pais`, `id_uf` 
            FROM `clientes` 
            WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
    $campos_cliente 	= bancos::sql($sql);
    $id_pais            = $campos_cliente[0]['id_pais'];
    $id_uf_cadastrado	= $campos_cliente[0]['id_uf'];

    //Busca o id_uf atravÈs do campo Estado ...
    $sql = "SELECT id_uf 
            FROM `ufs` 
            WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
    $campos_uf = bancos::sql($sql);

    if(!empty($_POST['txt_cnpj_cpf'])) {//Se essa vari·vel existir, ent„o atravÈs do CNPJ ou CPF verifico se o Cliente j· existe no BD ...
        $sql = "SELECT id_cliente, IF(razaosocial = '', nomefantasia, razaosocial) cliente, ativo 
                FROM `clientes` 
                WHERE `cnpj_cpf` = '$_POST[txt_cnpj_cpf]' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if(count($campos_cliente) == 1) {
            echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_cliente[0]['id_cliente'].' - '.utf8_encode($campos_cliente[0]['cliente'])."</center></b></font>";
            $executar_update = 0;
            $valor = 1;
        }else {
            //Atualiza o CPF ou CNPJ no cadastro do Cliente ...
            $campo_cnpj_cpf = " `cnpj_cpf` = '$_POST[txt_cnpj_cpf]', ";
        }
    }

    if($id_pais == 31) {
        if($campos_uf[0]['id_uf'] != $id_uf_cadastrado) {//Significa que houve mudanÁa na UF do Cliente ...
            //Verifico se esse Cliente possui pelo menos 1 NF que esteja Faturada ...
            $sql = "SELECT id_nf 
                    FROM `nfs` 
                    WHERE `id_cliente` = '$_POST[id_cliente]' 
                    AND `status` >= '2' LIMIT 1 ";
            $campos_nf = bancos::sql($sql);
            if(count($campos_nf) == 1) {//Significa que j· existe 1 NF anterior ...
                $executar_update = 0;
                $valor = 2;
            }
        }
    }
    if($executar_update == 1) {//Teoricamente j· posso atualizar o Cliente ...
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
                $razao_social.= substr($_POST['txt_razao_social'], $i, 1);
            }
        }
        $razao_social = trim($razao_social);		
        /**************************************************************************************************************************************/
        //Verifico se existe um outro Cliente com o nesmo Nome Fantasia ou Raz„o Social na mesma UF ...
        $condicao = (!empty($nome_fantasia)) ? " (`nomefantasia` LIKE '$nome_fantasia%' AND `razaosocial` LIKE '$razao_social') " : " `razaosocial` LIKE '$razao_social%' ";
        $sql = "SELECT id_cliente, IF(razaosocial = '', nomefantasia, razaosocial) cliente, ativo 
                FROM `clientes` 
                WHERE $condicao 
                AND `id_uf` = '".$campos_uf[0]['id_uf']."' 
                AND `cidade` = '".addslashes($_POST['txt_cidade'])."' 
                AND `num_complemento` = '".addslashes($_POST['txt_num_complemento'])."' 
                AND `id_cliente` <> '$_POST[id_cliente]' 
                AND `ativo`	= '1' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        if(count($campos_cliente) == 1) {
            echo "<font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue'><b><center>".$campos_cliente[0]['id_cliente'].' - '.utf8_encode($campos_cliente[0]['cliente'])."</center></b></font>";
            $executar_update = 0;
            $valor = 4;
        }else {
            $razao_social 	= strtoupper(str_replace('%', ' ', $razao_social));
            $nome_fantasia 	= strtoupper($nome_fantasia);
            $matriz             = (!empty($_POST[matriz])) ? 'S' : 'N';
            
            $data_fundacao      = data::datatodate($_POST['txt_data_fundacao'], '-');
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n„o tiver preenchidos  ...
/*******************************************************************************/
            $id_uf              = (!empty($campos_uf[0]['id_uf'])) ? "'".$campos_uf[0]['id_uf']."'" : 'NULL';
            $sql = "UPDATE `clientes` SET `id_uf` = $id_uf, `matriz` = '$matriz', `nomefantasia` = '$nome_fantasia', `razaosocial` = '$razao_social', `insc_estadual` = '$txt_insc_estadual', `insc_municipal` = '$txt_insc_municipal', $campo_cnpj_cpf `ccm` = '$_POST[txt_ccm]', `rg` = '$_POST[txt_rg]', `orgao` = '$_POST[txt_orgao]',  `endereco` = '".addslashes($_POST['txt_endereco'])."', `num_complemento` = '$_POST[txt_num_complemento]', `bairro` = '".addslashes($_POST[txt_bairro])."', `cep` = '$_POST[txt_cep]', `cidade` = '".addslashes($_POST[txt_cidade])."', `ddi_com` = '$_POST[txt_ddi_comercial]', `ddd_com` = '$_POST[txt_ddd_comercial]', `telcom` = '$_POST[txt_tel_comercial]', `telfax` = '$_POST[txt_tel_fax]', `ddi_fax` = '$_POST[txt_ddi_fax]', `ddd_fax` = '$_POST[txt_ddd_fax]', `email` = '$_POST[txt_email]', `email_nfe` = '$_POST[txt_email_nfe]', `email_financeiro` = '$_POST[txt_email_financeiro]', `data_atualizacao_emails` = '".date('Y-m-d')."', `pagweb` = '$_POST[txt_pagina_web]', `data_fundacao` = '$data_fundacao' WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
            bancos::sql($sql);
            
            genericas::atualizar_clientes_no_site_area_cliente($_POST['id_cliente']);
            $valor = 3;
        }
    }
}
/*************************************************************************************************************************/

//Busco dados do $id_cliente passado por par‚metro ...
$sql = "SELECT c.*, p.`pais`, ufs.`sigla` 
        FROM `clientes` c 
        LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
        INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
        WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
$campos_clientes = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var id_pais         = eval('<?=$campos_clientes[0]['id_pais'];?>')
//Raz„o Social ...
    if(!texto('form','txt_razao_social','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'RAZ√O SOCIAL', '1')) {
        return false
    }
//Nome Fantasia ...
    if(document.form.txt_nome_fantasia.value != '') {
        if(!texto('form', 'txt_nome_fantasia', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'NOME FANTASIA', '2')) {
            return false
        }
    }
//Se o PaÌs for Brasil, ent„o forÁa o preenchimento de CEP
    if(id_pais == 31) {
        if(typeof(document.form.txt_cnpj_cpf) == 'object') {//Se existir esse objeto no Formul·rio ent„o faÁo a ValidaÁ„o ...
            if(document.form.txt_cnpj_cpf.value != '') {
                if(document.form.txt_cnpj_cpf.value.length > 11) {//SÛ verifica CNPJ ...
                    if(!cnpj('form', 'txt_cnpj_cpf')) {
                        document.form.txt_cnpj_cpf.focus()
                        document.form.txt_cnpj_cpf.select()
                        return false
                    }
                }else {//SÛ verifica CPF ...
                    if(!cpf('form', 'txt_cnpj_cpf')) {
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
//N˙mero / Complemento ...
    if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞_/ ", 'N⁄MERO / COMPLEMENTO', '2')) {
        return false
    }
//DDI Comercial ...
    if(document.form.txt_ddi_comercial.value != '') {
        if(!texto('form', 'txt_ddi_comercial', '1', '1234567890', 'DDI COMERCIAL', '2')) {
            return false
        }
    }
//DDD Comercial ...
    if(document.form.txt_ddd_comercial.value != '') {
        if(!texto('form', 'txt_ddd_comercial', '1', '1234567890', 'DDD COMERCIAL', '2')) {
            return false
        }
    }
//Telefone Comercial ...
    if(!texto('form','txt_tel_comercial', '6', '1234567890','TELEFONE COMERCIAL','2')) {
        return false
    }
//DDI Fax ...
    if(document.form.txt_ddi_fax.value != '') {
        if(!texto('form', 'txt_ddi_fax', '1', '1234567890', 'DDI FAX', '2')) {
            return false
        }
    }
//DDD Fax ...
    if(document.form.txt_ddd_fax.value != '') {
        if(!texto('form', 'txt_ddd_fax', '1', '1234567890', 'DDD FAX', '2')) {
            return false
        }
    }
//Telefone FAX ...
    if(document.form.txt_tel_fax.value != '') {
        if(!texto('form', 'txt_tel_fax', '6', '1234567890', 'TELEFONE FAX', '2')) {
            return false
        }
    }
//E-mail - ser· obrigatÛrio o preenchimento quando o Cliente n„o for do Tipo Revenda Inativa ou Cliente ExcluÌdo ...
    if(document.form.cmb_tipo_cliente.value != 2 && document.form.cmb_tipo_cliente.value != 13) {
        if (!new_email('form', 'txt_email')) {
            return false
        }
        //SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, afinal albafer È e-mail daqui da empresa ...
        if(document.form.txt_email.value.indexOf('albafer') != -1) {
            alert('ENDERE«O DE E-MAIL INV¡LIDO !!!\n\nE-MAIL COM DADOS DAQUI DA EMPRESA !')
            document.form.txt_email.focus()
            document.form.txt_email.select()
            return false
        }
    }
//E-mail NFe ...
    if(!new_email('form', 'txt_email_nfe')) {
        return false
    }
//SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, afinal albafer È e-mail daqui da empresa ...
    if(document.form.txt_email_nfe.value.indexOf('albafer') != -1) {
        alert('ENDERE«O DE E-MAIL INV¡LIDO !!!\n\nE-MAIL COM DADOS DAQUI DA EMPRESA !')
        document.form.txt_email_nfe.focus()
        document.form.txt_email_nfe.select()
        return false
    }
//E-mail Financeiro ...
    if(!new_email('form', 'txt_email_financeiro')) {
        return false
    }
//SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, afinal albafer È e-mail daqui da empresa ...
    if(document.form.txt_email_financeiro.value.indexOf('albafer') != -1) {
        alert('ENDERE«O DE E-MAIL INV¡LIDO !!!\n\nE-MAIL COM DADOS DAQUI DA EMPRESA !')
        document.form.txt_email_financeiro.focus()
        document.form.txt_email_financeiro.select()
        return false
    }
//Data de FundaÁ„o ...
    if(document.form.txt_data_fundacao.value != '') {
        if(!data('form', 'txt_data_fundacao', '4000', 'FUNDA«√O')) {
            return false
        }
    }
//Aqui È para n„o reler a Tela de Baixo quando Clicar no Bot„o Salvar, a idÈia È apenas reler pelo Bot„o X do Pop-UP ...
    document.form.nao_atualizar.value   = 1
//Converte o endereÁo e o bairro para mai˙sculo para ficar mais organizado
    document.form.txt_razao_social.value    = document.form.txt_razao_social.value.toUpperCase()
    document.form.txt_nome_fantasia.value   = document.form.txt_nome_fantasia.value.toUpperCase()
    document.form.txt_endereco.value        = document.form.txt_endereco.value.toUpperCase()
    document.form.txt_bairro.value          = document.form.txt_bairro.value.toUpperCase()
//Habilito os objetos p/ gravar na Base de Dados ...
    document.form.txt_endereco.disabled = false
    document.form.txt_bairro.disabled   = false
//Travo o bot„o uma vez que esse foi clicado, para o palhaÁo do usu·rio n„o ficar com travessuras ...
    document.form.cmd_salvar.disabled = true
}

function colorir_inscricao_estadual() {
    if(document.form.txt_insc_estadual.value == '') {//InscriÁ„o Estadual Vazia ...
        document.form.txt_insc_estadual.style.background = 'red'
        document.form.txt_insc_estadual.style.color      = 'white'
    }else {//InscriÁ„o Preenchida ...
        document.form.txt_insc_estadual.style.background = 'white'
        document.form.txt_insc_estadual.style.color      = 'brown'
    }
}

function copiar_telefone() {
    document.form.txt_ddi_fax.value = document.form.txt_ddi_comercial.value
    document.form.txt_ddd_fax.value = document.form.txt_ddd_comercial.value
    document.form.txt_tel_fax.value = document.form.txt_tel_comercial.value
    document.form.txt_email.focus()
}

//Atualiza o frame de baixo para controle do CEP
function buscar_cep() {
    var id_pais = eval('<?=$campos_clientes[0]['id_pais'];?>')
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

function atualizar_abaixo() {
    if(typeof(window.top.opener.document.form) == 'object') {
        var valor = eval('<?=$valor;?>')
        if(document.form.nao_atualizar.value == 0 && valor == 1) window.top.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload='colorir_inscricao_estadual();document.form.txt_razao_social.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controle de Tela*****************************-->
<input type='hidden' name='nao_atualizar' value='0'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='nao_exibir_menu' value='<?=$nao_exibir_menu;?>'>
<!--*************************************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
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
        <td colspan='2'>
            <b>PaÌs:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='darkblue' size='-1'>
                <b><?=strtoupper($campos_clientes[0]['pais']);?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            <b>Raz&atilde;o Social:</b>
        </td>
        <td width='50%'>
            Nome Fantasia:
        </td>

    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_razao_social" value="<?=$campos_clientes[0]['razaosocial'];?>" title="Digite a Raz&atilde;o Social" size="60" class='caixadetexto' maxlength="80">
        </td>
        <td>
            <input type='text' name="txt_nome_fantasia" value="<?=$campos_clientes[0]['nomefantasia'];?>" title="Digite o Nome Fantasia" size="35" class='caixadetexto' maxlength="50">
            &nbsp;		
            <input type="checkbox" name="matriz" id='lbl_matriz' value="S" <?if($campos_clientes[0]['matriz'] == 'S') echo "checked=\"true\"";?>>
            <label for='lbl_matriz'>
                Matriz
            </label>
        </td>
    </tr>
    <!--*******************Dados de Empresa*******************-->
    <?
            if(strlen($campos_clientes[0]['cnpj_cpf']) == 14) {//Se essa vari·vel for passada por par‚metro atravÈs do CNPJ ou CPF verifico se o Cliente j· existe no BD ...
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            PESSOA JURÕDICA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ:
        </td>
        <td>
            CCM:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue' size='-1'>
                <b><?=substr($campos_clientes[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_clientes[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_clientes[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_clientes[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_clientes[0]['cnpj_cpf'], 12, 2);?></b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_ccm' value='<?=$campos_clientes[0]['ccm'];?>' title='Digite o CCM' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            InscriÁ„o Estadual: 
        </td>
        <td>
            InscriÁ„o Municipal: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_insc_estadual' value='<?=$campos_clientes[0]['insc_estadual'];?>' title='Digite a InscriÁ„o Estadual' onkeyup="verifica(this, 'aceita', 'numeros', '', event);colorir_inscricao_estadual()" size='20' maxlength='15' class='caixadetexto'>
            <font color='red'>
                <br/><b>(CONFORME ß 2∫ DO ART. 155 DA CONSTITUI«√O FEDERAL E NO ART. 99 DO ATO DAS DISPOSI«’ES CONSTITUICIONAIS TRANSIT”RIAS - ADCT DA CONSTITUI«√O FEDERAL, BEM COMO NOS ARTS. 102 E 199 DO C”DIGO TRIBUT¡RIO NACIONAL (LEI N∫ 5.172, DE 25 DE OUTUBRO DE 1966), SE ESSE CAMPO ESTIVER V¡ZIO O SISTEMA BUSCA OS TRIBUTOS DE ICMS DO ESTADO DESSE CLIENTE E IGNORA O IVA DO PRODUTO)</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_insc_municipal' value='<?=$campos_clientes[0]['insc_municipal'];?>' title='Digite a InscriÁ„o Municipal' size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <?
            }else if(strlen($campos_clientes[0]['cnpj_cpf']) == 11) {//Se essa vari·vel for passada por par‚metro atravÈs do CNPJ ou CPF verifico se o Cliente j· existe no BD ...
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            PESSOA FÕSICA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            CPF:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='darkblue' size='-1'>
                <b><?=substr($campos_clientes[0]['cnpj_cpf'], 0, 3).'.'.substr($campos_clientes[0]['cnpj_cpf'], 3, 3).'.'.substr($campos_clientes[0]['cnpj_cpf'], 6, 3).'-'.substr($campos_clientes[0]['cnpj_cpf'], 9, 2);?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            RG:
        </td>
        <td>
            Org„o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_rg' value='<?=$campos_clientes[0]['rg'];?>' title='Digite o RG' size='20' maxlength='10' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_orgao' value='<?=$campos_clientes[0]['orgao'];?>' title='Digite o ”rg„o' size='20' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <?
            }else {
    ?>
    <tr class='linhanormal'>
        <td colspan='2'>
            CNPJ / CPF: 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_cnpj_cpf' title='Digite CNPJ / CPF' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='20' maxlength='18' class='caixadetexto'>
        </td>
    </tr>
    <?
            }
    ?>
    <!--*******************Dados de EndereÁo*******************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            DADOS DE ENDERE«O
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>CEP:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_cep' value='<?=$campos_clientes[0]['cep'];?>' title='Digite o Cep' onfocus="if(this.className == 'textdisabled') document.form.txt_endereco.focus()" onkeyup="verifica(this, 'cep', '', '', event)" onblur='buscar_cep()' size='20' maxlength='9' class='caixadetexto'>
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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>N.&#176; / Complemento:</b>
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco' value="<?=stripslashes($campos_clientes[0]['endereco']);?>" size='45' maxlength='50' title='Endere&ccedil;o' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento.focus()" class='textdisabled'>
            &nbsp;
            <input type='text' name='txt_num_complemento' value='<?=$campos_clientes[0]['num_complemento'];?>' title='Digite o N&uacute;mero, Complemento, ...' size='10' maxlength='50' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro' value="<?=stripslashes($campos_clientes[0]['bairro']);?>" title='Bairro' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento.focus()" size='35' class='textdisabled'>
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
            <input type='text' name='txt_cidade' value="<?=stripslashes($campos_clientes[0]['cidade']);?>" title='Cidade' onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento.focus()" size='35' class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_estado' value='<?=$campos_clientes[0]['sigla'];?>' title='Estado' onfocus="document.form.txt_num_complemento.focus()" size='35' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDI:&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
            <b>
                Tel. Comercial:
            </b>
        </td>
        <td>
            DDI:&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
            Tel. Fax:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_ddi_comercial" value="<?=$campos_clientes[0]['ddi_com'];?>" title="Digite o DDI comercial" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="3" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_ddd_comercial" value="<?=$campos_clientes[0]['ddd_com'];?>" title="Digite o DDD comercial" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_tel_comercial" value="<?=$campos_clientes[0]['telcom'];?>" title="Digite o Telefone Comercial" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
            &nbsp;
            <input type='button' name="cmd_copiar" value="Copiar Telefone =>" title="Copiar Telefone =>" onclick="copiar_telefone()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_ddi_fax" value="<?=$campos_clientes[0]['ddi_fax'];?>" title="Digite o DDI fax" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="3" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_ddd_fax" value="<?=$campos_clientes[0]['ddd_fax'];?>" title="Digite o DDD fax" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_tel_fax" value="<?=$campos_clientes[0]['telfax'];?>" title="Digite o Telefone Fax" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-Mail:</b>
        </td>
        <td>
            <font color='red'>
                <b>E-Mail NFe</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_email" value="<?=$campos_clientes[0]['email'];?>" size="50" maxlength="85" title="Digite o E-mail" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_email_nfe" value="<?=$campos_clientes[0]['email_nfe'];?>" title="Digite o E-mail NFe" size="35" maxlength="85" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-Mail Financeiro (Onde ser„o enviadas as duplicatas):</b>
        </td>
        <td>
            <font color='green' size='1'>
                <b>Data de Cadastro:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_email_financeiro" value="<?=$campos_clientes[0]['email_financeiro'];?>" title="Digite o E-mail Financeiro" size="50" maxlength="85" class='caixadetexto'>
        </td>
        <td>
            <font color='green' size='1'>
                <?=data::datetodata($campos_clientes[0]['data_cadastro'], '/');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            P&aacute;gina Web:
        </td>
        <td>
            Data de FundaÁ„o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_pagina_web' value='<?=$campos_clientes[0]['pagina_web'];?>' size='35' title='Digite a P&aacute;gina Web' class='caixadetexto'>
        </td>
        <td>
            <?
                $data_fundacao = ($campos_clientes[0]['data_fundacao'] != '0000-00-00') ? data::datetodata($campos_clientes[0]['data_fundacao'], '/') : '';
            ?>
            <input type='text' name='txt_data_fundacao' value='<?=$data_fundacao;?>' title='Digite a Data de FundaÁ„o' onkeyup="verifica(this, 'data', '', '', event)" size='10' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_fundacao&tipo_retorno=1', 'CALEND¡RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='2'>
            <!--*********Passo o par‚metro cmb_origem=15 para que no inÌcio sÛ carregue nessa parte 
            de Follow-Ups dados que s„o pertinentes a parte de cadastro*********-->
            <iframe name='detalhes' id='detalhes' src='../follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=15&cmb_origem=15' marginwidth='0' marginheight='0' frameborder='0' height='260' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
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
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
            }else {
        ?>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.parent.close()" class='botao'>
        <?		
            }
        ?>
        </td>
    </tr>
    <?
        /*SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, 
        afinal albafer È e-mail daqui da empresa ...*/
        if(strpos($campos_clientes[0]['email'], 'grupoalbafer') > 0 || strpos($campos_clientes[0]['email_nfe'], 'grupoalbafer') > 0 || strpos($campos_clientes[0]['email_financeiro'], 'grupoalbafer') > 0) {
    ?>   
    <tr class='erro' align='center'>
        <td colspan='2'>
            EXISTE(M) ENDERE«O(S) DE E-MAIL(S) INV¡LIDO(S) !!! <p/>E-MAIL(S) COM DADOS DAQUI DA EMPRESA !
        </td>
    </tr>
    <?        
    //FaÁo isso de propÛsito justamente para forÁar o usu·rio a preencher cada e-mail de seus contatos cadastrados ...
            $sql = "UPDATE `clientes` SET `data_atualizacao_emails` = '0000-00-00' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            bancos::sql($sql);
        }
    ?>
    <!--Aqui busco o EndereÁo atravÈs do Cep do Cliente ...-->
    <iframe name='cep' id='cep' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observa&ccedil;&atilde;o:</b></font>

<b>* Os campos em Negrito s&atilde;o obrigat&oacute;rios.</b>
</pre>