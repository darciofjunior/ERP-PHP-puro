<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">EMPRESA INCLUIDA COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">EMPRESA JÁ EXISTENTE.</font>';

if(!empty($_POST['txt_nome_fantasia'])) {
//Aqui verifico se essa Empresa já foi cadastrada
    $sql = "SELECT id_empresa 
            FROM `empresas` 
            WHERE `cnpj` = '$_POST[txt_cnpj]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe
        //Tratamento para os Campos p/ gravar no BD ...
        $insc_estadual      = str_replace('.', '', $_POST['txt_insc_estadual']);
        $insc_estadual      = str_replace('/', '', $insc_estadual);
        $insc_estadual      = str_replace('-', '', $insc_estadual);
        $telefone_comercial = str_replace('-', '', $_POST['txt_telefone_comercial']);
        $telefone_fax       = str_replace('-', '', $_POST['txt_telefone_fax']);
//Gravando os campos ...
        $sql = "INSERT INTO `empresas` (`id_empresa`, `nomefantasia`, `razaosocial`, `cnpj`, `ie`, `endereco`, `complemento`, `numero`, `bairro`, `cep`, `cidade`, `id_uf`, `id_pais`, `ddd_comercial`, `telefone_comercial`, `ddd_fax`, `telefone_fax`, `home`, `email`, `id_tipo_empresa`, `ip_externo`, `ativo`) VALUES (NULL, '$_POST[txt_nome_fantasia]', '$_POST[txt_razao_social]', '$_POST[txt_cnpj]', '$insc_estadual', '$_POST[txt_endereco]', '$_POST[txt_complemento]', '$_POST[txt_numero]', '$_POST[txt_bairro]', '$_POST[txt_cep]', '$_POST[txt_cidade]', '$_POST[cmb_federal]', '$_POST[cmb_pais]', '$_POST[txt_ddd_comercial]', '$telefone_comercial', '$_POST[txt_ddd_fax]', '$telefone_fax', '$_POST[txt_home_page]', '$_POST[txt_email]', '$_POST[cmb_tipo_empresa]', '$_POST[txt_ip_externo]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Empresa(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
    document.form.txt_nome_fantasia.focus()
    if (document.form.cmb_pais.value == '31') {
        document.form.cmb_federal.disabled = false
    }else {
        document.form.cmb_federal.disabled = true
    }
}

//Função que habilita a unidade federal
function pais_habilita() {
    if (document.form.cmb_pais.value == '31') {
        document.form.cmb_federal.disabled = false
        document.form.cmb_federal.focus()
    }else {
        document.form.cmb_federal.disabled = true
    }
}

function validar() {
//Nome Fantasia
    if(!texto('form', 'txt_nome_fantasia', '3', ' _.()-|àÀãõÃÕáéíóúÁÉÍÓÚçÇabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890', 'EMPRESA', '1')) {
        return false
    }
//Razão Social
    if(!texto('form', 'txt_razao_social', '7', ' _.()-àÀãõÃÕ|áéíóúÁÉÍÓÚçÇabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890', 'RAZÃO SOCIAL', '1')) {
        return false
    }
//cnpj
    nro= document.form.txt_cnpj.value
    if(nro.length > 14) {
        for(i = 0; i < nro.length; i++) {
            letra = nro.charAt(i)
            if((letra == '.') || (letra == '/') || (letra == '-')) nro = nro.replace(letra,'')
        }
        document.form.txt_cnpj.value = nro
        if (!cnpj('form','txt_cnpj')) {
            return false
        }
    }else {
        if (!cnpj('form','txt_cnpj')) {
            return false
        }
    }
//Inscrição Estadual
    if(!texto('form', 'txt_insc_estadual', '3', '0123456789.', 'INSCRIÇÃO ESTADUAL OU MUNICIPAL', '1')) {
        return false
    }
//Endereço
    if(!texto('form', 'txt_endereco', '5', 'abcdefghijklmnopqrstuvwxyzçáéíóú ÁÉÍÓÚÂÊÎÔÛãõÃÕàÀABCDEFGHIJKLMNOPRSTUVWXYZ0123456789:,.', 'ENDEREÇO', '2')) {
        return false
    }
//Bairro
    if(!texto('form', 'txt_bairro', '3', 'abcdefghijklmnopqrstuvwxyzçáéíóú ÁÉÍÓÚÂÊÎÔÛãõÃÕàÀABCDEFGHIJKLMNOPRSTUVWXYZ0123456789:,.', 'BAIRRO', '2')) {
        return false
    }
//Número
    if(!texto('form', 'txt_numero', '1', '0123456789', 'NÚMERO', '2')) {
        return false
    }
//Cidade
    if(!texto('form', 'txt_cidade', '3', 'abcdefghijklmnopqrstuvwxyzçáéíóú ÁÉÍÓÚÂÊÎÔÛãõÃÕàÀABCDEFGHIJKLMNOPRSTUVWXYZ0123456789.', 'CIDADE', '2')) {
        return false
    }
//Cep
    if(!texto('form', 'txt_cep', '3', '-0123456789', 'CEP', '2')) {
        return false
    }
//Complemento
    if(document.form.txt_complemento.value != '') {
        if(!texto('form', 'txt_complemento', '', 'abcdefghijklmnopqrstuvwxyzçáéíóú ÁÉÍÓÚÂÊÎÔÛãõÃÕàÀABCDEFGHIJKLMNOPRSTUVWXYZ0123456789.', 'COMPLEMENTO', '2')) {
            return false
        }
    }
//Unidade Federal
    if(!combo('form', 'cmb_federal', '', 'SELECIONE A UNIDADE FEDERAL !')) {
        return false
    }
//País
    if(!combo('form', 'cmb_pais', '', 'SELECIONE O PAÍS !')) {
        return false
    }
//Telefone Comercial
    if(!texto('form', 'txt_telefone_comercial', '3', '-0123456789', 'TELEFONE COMERCIAL', '2')) {
        return false
    }else {
//DDD Telefone Comercial
        if(!texto('form', 'txt_ddd_comercial', '2', '0123456789','DDD DO TELEFONE COMERCIAL', '2')) {
            return false
        }
    }
//Telefone Fax
    if(document.form.txt_telefone_fax.value != '') {
        if(!texto('form', 'txt_telefone_fax', '3', '-0123456789', 'TELEFONE FAX', '2')) {
            return false
        }else {
//DDD Telefone Fax
            if(!texto('form', 'txt_ddd_fax', '2', '0123456789', 'DDD DO TELEFONE FAX', '2')) {
                return false
            }
        }
    }
//Home Page
    if(document.form.txt_home_page.value != '') {
        if(!texto('form', 'txt_home_page', '3', 'abcdefghijklmnopqrstuvwxyzçáéíóú ÁÉÍÓÚÂÊÎÔÛãõÃÕàÀABCDEFGHIJKLMNOPRSTUVWXYZ1234567890._/','HOME PAGE', '1')) {
            return false
        }
    }
//E-mail
    if(!email('form', 'txt_email', '6', 'abcdefghijlmnopqrstuvwxyz ABCDEFGHIJLMNOPQRSTUVWXYZ@._', 'EMAIL')) {
        return false
    }
//Tipo de Empresa
    if(!combo('form', 'cmb_tipo_empresa', '', 'SELECIONE O TIPO DE EMPRESA !')) {
        return false
    }
}
</Script>
</head>
<body onload='iniciar()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir Empresa(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome Fantasia:</b>
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite o Nome Fantasia' size='55' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Razão Social:</b>
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' size='60' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CNPJ:</b>
        </td>
        <td>
            <input type='text' name='txt_cnpj' title='Digite o CNPJ' maxlength='18' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IE:</b>
        </td>
        <td>
            <input type='text' name='txt_insc_estadual' title='Digite a Inscrição Estadual' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Endereço:</b>
        </td>
        <td>
            <input type='text' name='txt_endereco' title='Digite o Endereço' size='30' maxlength='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Bairro:</b>
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' size='30' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Número:</b>
        </td>
        <td>
            <input type='text' name='txt_numero' title='Digite o Número' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <input type='text' name='txt_cidade' title="Digite a Cidade" size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cep:</b>
        </td>
        <td>
            <input type='text' name='txt_cep' title="Digite o Cep" size='20' maxlength='9' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Complemento:
        </td>
        <td>
            <input type='text' name='txt_complemento' title='Digite o Complemento' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>País:</b>
        </td>
        <td>
            <select name='cmb_pais' onchange="return pais_habilita()" title="Selecione o País" class="combo">
            <?
                $sql = "SELECT id_pais, pais 
                        FROM `paises` ";
                echo combos::combo($sql, 31);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Unidade Federal:</b>
        </td>
        <td>
            <select name='cmb_federal' title="Selecione a Unidade Federal" class="combo">
                <?
                    $sql = "SELECT id_uf, sigla 
                            FROM `ufs` 
                            WHERE `ativo` = '1' ORDER BY sigla ";
                    echo combos::combo($sql, 1);
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>DDD Com:</b>
        </td>
        <td>
            <input type='text' name='txt_ddd_comercial' title="Digite o DDD Comercial" maxlength='3' size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            <b>Tel Com:</b>
        </td>
        <td>
            <input type='text' name='txt_telefone_comercial' title="Digite o Telefone Comercial" maxlength='9' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD FAX:
        </td>
        <td>
            <input type='text' name='txt_ddd_fax' title="Digite o DDD Fax" maxlength='3' size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            Tel FAX:
        </td>
        <td>
            <input type='text' name='txt_telefone_fax' title="Digite o telefone fax" maxlength='9' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Home Page:
        </td>
        <td>
            <input type='text' name='txt_home_page' title="Digite a Home Page" size='30' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            <b>Email:</b>
        </td>
        <td>
            <input type='text' name='txt_email' title="Digite o E-mail" size='30' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            IP Externo:
        </td>
        <td>
            <input type='text' name='txt_ip_externo' title='Digite o IP Externo' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Empresa:</b>
        </td>
        <td colspan='3'>
            <select name='cmb_tipo_empresa' title='Selecione o Tipo de Empresa' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1'>Indústria</option>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');return iniciar()" style="color:#ff9900" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>