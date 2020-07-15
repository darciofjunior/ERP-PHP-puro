<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up']))  require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">EMPRESA ALTERADA COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">EMPRESA JÁ EXISTENTE.</font>';

if ($passo == 1) {
    //Coloquei esse nome de id_empresa_loop, por causa que já existe id_empresa na sessão, daí iria dar conflito
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `id_empresa` = '$_GET[id_empresa_loop]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $cnpj           = $campos[0]['cnpj'];
    $cnpj           = (empty($cnpj)) ? '' : substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);

    $ie             = $campos[0]['ie'];
    if(empty($ie)) $ie = '';
    if(strlen($ie) == 12)   $ie = substr($ie, 0, 3).'.'.substr($ie, 3, 3).'.'.substr($ie, 6, 3).'.'.substr($ie, 9, 3);
    
    $ddd_com        = $campos[0]['ddd_comercial'];
    if(empty($ddd_com)) $ddd_com = '';
    
    $tel_com = $campos[0]['telefone_comercial'];
    if(empty($tel_com)) {
        $tel_com = '';
    }elseif(strlen($tel_com) == 7) {
        $tel_com = substr($tel_com, 0, 3).'-'.substr($tel_com, 3, 4);
    }elseif(strlen($tel_com) == 8) {
        $tel_com = substr($tel_com, 0, 4).'-'.substr($tel_com, 4, 4);
    }
    
    $ddd_fax = $campos[0]['ddd_fax'];
    if(empty($ddd_fax)) $ddd_fax = '';
    
    $tel_fax = $campos[0]['telefone_fax'];
    if(empty($tel_fax)) {
        $tel_fax = '';
    }elseif(strlen($tel_fax) == 7) {
        $tel_fax = substr($tel_fax, 0, 3).'-'.substr($tel_fax, 3, 4);
    }elseif(strlen($tel_fax) == 8) {
        $tel_fax = substr($tel_fax, 0, 4).'-'.substr($tel_fax, 4, 4);
    }
?>
<html>
<head>
<title>.:: Alterar Empresa(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
    document.form.txt_nome_fantasia.focus()
    if (document.form.cmb_pais.value == '31') {
        document.form.cmb_uf.disabled = false
    }else {
        document.form.cmb_uf.disabled = true
    }
}

//Função que habilita a unidade federal
function pais_habilita() {
    if (document.form.cmb_pais.value == '31') {
        document.form.cmb_uf.disabled = false
        document.form.cmb_uf.focus()
    }else {
        document.form.cmb_uf.disabled = true
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
    nro = document.form.txt_cnpj.value
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
    if(!combo('form', 'cmb_uf', '', 'SELECIONE A UNIDADE FEDERAL !')) {
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='id_empresa_loop' value='<?=$_GET['id_empresa_loop'];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Empresa(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome Fantasia:</b>
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' value='<?=$campos[0]['nomefantasia'];?>' title='Digite o Nome Fantasia' size='55' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Razão Social:</b>
        </td>
        <td>
            <input type='text' name='txt_razao_social' value='<?=$campos[0]['razaosocial'];?>' title='Digite a Razão Social' size='60' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CNPJ:</b>
        </td>
        <td>
            <input type='text' name='txt_cnpj' value='<?=$cnpj;?>' title='Digite o CNPJ' maxlength='18' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IE:</b>
        </td>
        <td>
            <input type='text' name='txt_insc_estadual' value='<?=$ie;?>' title='Digite a Inscrição Estadual' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Endereço:</b>
        </td>
        <td>
            <input type='text' name='txt_endereco' value='<?=$campos[0]['endereco'];?>' title='Digite o Endereço' size='30' maxlength='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Bairro:</b>
        </td>
        <td>
            <input type='text' name='txt_bairro' value='<?=$campos[0]['bairro'];?>' title='Digite o Bairro' size='30' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Número:</b>
        </td>
        <td>
            <input type='text' name='txt_numero' value='<?=$campos[0]['numero'];?>' title='Digite o Número' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <input type='text' name='txt_cidade' value='<?=$campos[0]['cidade'];?>' title="Digite a Cidade" size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cep:</b>
        </td>
        <td>
            <input type='text' name='txt_cep' value='<?=$campos[0]['cep'];?>' title="Digite o Cep" size='20' maxlength='9' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Complemento:
        </td>
        <td>
            <input type='text' name='txt_complemento' value='<?=$campos[0]['complemento'];?>' title='Digite o Complemento' size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>País:</b>
        </td>
        <td>
            <select name='cmb_pais' onchange="return pais_habilita()" title="Selecione o País" class='combo'>
            <?
                $sql = "SELECT id_pais, pais 
                        FROM `paises` ";
                echo combos::combo($sql, $campos[0]['id_pais']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Unidade Federal:</b>
        </td>
        <td>
            <select name='cmb_uf' title='Selecione a Unidade Federal' class='combo'>
                <?
                    $sql = "SELECT id_uf, sigla 
                            FROM `ufs` 
                            WHERE `ativo` = '1' ORDER BY sigla ";
                    echo combos::combo($sql, $campos[0]['id_uf']);
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>DDD Com:</b>
        </td>
        <td>
            <input type='text' name='txt_ddd_comercial' value='<?=$ddd_com;?>' title="Digite o DDD Comercial" maxlength='3' size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            <b>Tel Com:</b>
        </td>
        <td>
            <input type='text' name='txt_telefone_comercial' value='<?=$tel_com;?>' title="Digite o Telefone Comercial" maxlength='9' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD FAX:
        </td>
        <td>
            <input type='text' name='txt_ddd_fax' value='<?=$ddd_fax;?>' title="Digite o DDD Fax" maxlength='3' size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            Tel FAX:
        </td>
        <td>
            <input type='text' name='txt_telefone_fax' value='<?=$tel_fax;?>' title="Digite o telefone fax" maxlength='9' size='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Home Page:
        </td>
        <td>
            <input type='text' name='txt_home_page' value='<?=$campos[0]['home'];?>' title="Digite a Home Page" size='30' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            <b>Email:</b>
        </td>
        <td>
            <input type='text' name='txt_email' value='<?=$campos[0]['email'];?>' title='Digite o E-mail' size='30' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>        
        <td>
            IP Externo:
        </td>
        <td>
            <input type='text' name='txt_ip_externo' value='<?=$campos[0]['ip_externo'];?>' title='Digite o IP Externo' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Empresa:</b>
        </td>
        <td colspan='3'>
            <?
                if($campos[0]['id_tipo_empresa'] == 1) $selected1 = 'selected';
            ?>
            <select name='cmb_tipo_empresa' title='Selecione o Tipo de Empresa' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected1;?>>Indústria</option>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
        <?
            if($_GET['pop_up'] == 1) {//Significa que essa Tela foi aberta como sendo Pop-UP ...
                echo '&nbsp;';
            }else {//Significa que essa Tela foi aberta de forma Normal ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');iniciar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    //Verifica se já existe uma Empresa cadastrada com esse nome diferente da Atual passada por parâmetro ...
    $sql = "SELECT id_empresa 
            FROM `empresas` 
            WHERE `cnpj` = '$_POST[txt_cnpj]' 
            AND `id_empresa` <> '$_POST[id_empresa_loop]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe
        //Tratamento para os Campos p/ gravar no BD ...
        $insc_estadual  = str_replace('.', '', $_POST['txt_insc_estadual']);
        $insc_estadual  = str_replace('/', '', $insc_estadual);
        $insc_estadual  = str_replace('-', '', $insc_estadual);
        $telefone1      = str_replace('-', '', $_POST['txt_telefone_comercial']);
        $telefone2      = str_replace('-', '', $_POST['txt_telefone_fax']);
//Atualizando os campos ...
        $sql = "UPDATE `empresas` SET `nomefantasia` = '$_POST[txt_nome_fantasia]', `razaosocial` = '$_POST[txt_razao_social]', `cnpj` = '$_POST[txt_cnpj]', `ie` = '$insc_estadual', `endereco` = '$_POST[txt_endereco]', `complemento` = '$_POST[txt_complemento]', `numero` = '$_POST[txt_numero]', `bairro` = '$_POST[txt_bairro]', `cep` = '$_POST[txt_cep]', `cidade` = '$_POST[txt_cidade]', `id_uf` = '$_POST[cmb_uf]', `id_pais` = '$_POST[cmb_pais]', `ddd_comercial` = '$_POST[txt_ddd_comercial]', `telefone_comercial` = '$telefone1', `ddd_fax` = '$_POST[txt_ddd_fax]', `telefone_fax` = '$telefone2', `home` = '$_POST[txt_home_page]', `email` = '$_POST[txt_email]', `id_tipo_empresa` = '$_POST[cmb_tipo_empresa]', `ip_externo` = '$_POST[txt_ip_externo]' WHERE `id_empresa` = '$_POST[id_empresa_loop]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT * 
            FROM `empresas` 
            WHERE `ativo` = '1' ORDER BY nomefantasia ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=3'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Empresa(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Alterar Empresa(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Nome Fantasia
        </td>
        <td>
            Razão Social
        </td>
        <td>
            CNPJ
        </td>
        <td>
            IE
        </td>
        <td>
            IP Externo
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de id_empresa_loop, por causa que já existe id_empresa na sessão, daí iria dar conflito
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='alterar.php?passo=1&id_empresa_loop=<?=$campos[$i]['id_empresa'];?>' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='alterar.php?passo=1&id_empresa_loop=<?=$campos[$i]['id_empresa'];?>' class='link'>
                <?=$campos[$i]['nomefantasia'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['cnpj'])) {
                echo '&nbsp;';
            }else {
                echo substr($campos[$i]['cnpj'], 0, 2).'.'.substr($campos[$i]['cnpj'], 2, 3).'.'.substr($campos[$i]['cnpj'], 5, 3).'/'.substr($campos[$i]['cnpj'], 8, 4).'-'.substr($campos[$i]['cnpj'], 12, 2);
            }
        ?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['ie'])) {
                echo '&nbsp;';
            }elseif(strlen($campos[$i]['ie'] == 12)) {
                echo substr($campos[$i]['ie'], 0, 3).'.'.substr($campos[$i]['ie'], 3, 3).'.'.substr($campos[$i]['ie'], 6, 3).'.'.substr($campos[$i]['ie'], 9, 3);
            }else {
                echo $campos[$i]['ie'];
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['ip_externo'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            &nbsp;
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