<?
require('../../../lib/segurancas.php');
session_start('funcionarios');
$linhas = 0;

//Se essa variável "$_GET['txt_cnpj_cpf']" for passada por parâmetro através do CNPJ ou CPF verifico se o Fornecedor já existe no BD ...
if(!empty($_GET['txt_cnpj_cpf']) || $_GET['clonar_cliente'] == 1) {
    if(!empty($_GET['txt_cnpj_cpf'])) {
        $sql = "SELECT `id_fornecedor`, IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS fornecedor, `ativo` 
                FROM `fornecedores` 
                WHERE `cnpj_cpf` = '$_GET[txt_cnpj_cpf]' LIMIT 1 ";
        $campos_fornecedor  = bancos::sql($sql);
        $linhas             = count($campos_fornecedor);
    }
    $class_dados_endereco   = 'textdisabled';
    $class_cep              = 'caixadetexto';
}else {
    $class_dados_endereco   = 'caixadetexto';
    $class_cep              = 'textdisabled';
}
?>
<table width='850' border='0' align='center' cellspacing ='1' cellpadding='1'>
<?
if($linhas == 1) {
    if($campos_fornecedor[0]['ativo'] == 1) {
?>
    <tr><td></td></tr>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='red'>
                FORNECEDOR 
                <font face='Verdana, Arial, Helvetica, sans-serif' color="darkblue">
                    "<?=utf8_encode($campos_fornecedor[0]['fornecedor']);?>"
                </font>
                J&Aacute; EXISTENTE !
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='atencao' align='center' height='50'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='red'>
                ESSE FORNECEDOR J&Aacute; EXISTE E SE ENCONTRA INATIVO ! DESEJA REATIV&Aacute;-LO ?
            </font>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            <a href="../../classes/fornecedor/incluir_dados_basicos.php?id_fornecedor=<?=$campos_fornecedor[0]['id_fornecedor'];?>" style="border-style:solid; border-width:1px; text-decoration:none">
                <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-1'>
                    &nbsp;SIM
                </font>
            </a>
            &nbsp;
            <a href="../../classes/fornecedor/incluir_dados_basicos.php" style="border-style:solid; border-width:1px; text-decoration:none">
                <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-1'>
                    &nbsp;N&Atilde;O&nbsp;
                </font>
            </a>
        </td>
    </tr>
<?
    }
    exit;
}
?>
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
            <input type='text' name="txt_razao_social" title="Digite a Raz&atilde;o Social" size="50" class='caixadetexto' maxlength="80">
        </td>
        <td>
            <input type='text' name="txt_nome_fantasia" title="Digite o Nome Fantasia" size="35" class='caixadetexto' maxlength="50">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>CEP:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name="txt_cep" size="20" maxlength="9" title="Digite o Cep" onfocus="if(this.className == 'textdisabled') document.form.txt_endereco.focus()" onkeyup="verifica(this, 'cep', '', '', event)" onblur="buscar_cep()" class="<?=$class_cep;?>">
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
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>N.&#176; / Complemento</b>
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_endereco" size="45" maxlength="50" title="Endere&ccedil;o" onfocus="if(this.className == 'textdisabled') document.form.txt_num_complemento.focus()" class="<?=$class_dados_endereco;?>">
            &nbsp;
            <input type='text' name="txt_num_complemento" title="Digite o N&uacute;mero, Complemento, ..." size="10" maxlength="50" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_bairro" size="35" title="Bairro" onfocus="if(this.className == 'textdisabled') document.form.txt_ddd_comercial.focus()" class="<?=$class_dados_endereco;?>">
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
            <input type='text' name="txt_cidade" size="35" title="Cidade" onfocus="if(this.className == 'textdisabled') document.form.txt_ddd_comercial.focus()" class="<?=$class_dados_endereco;?>">
        </td>
        <td>
            <input type='text' name="txt_estado" size="35" title="Estado" onfocus="document.form.txt_ddd_comercial.focus()" class='textdisabled'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
            <b>Tel. Comercial:</b>
        </td>
        <td>
            DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
            Tel. Comercial 2:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_ddd_comercial" title="Digite o DDD comercial" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_tel_comercial" title="Digite o Telefone Comercial" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
            &nbsp;
            <input type="button" name="cmd_copiar" value="Copiar Telefone =>" title="Copiar Telefone =>" onclick="copiar_telefone()" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_ddd_comercial2' title='Digite o DDD Comercial 2' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_tel_comercial2' title='Digite o Telefone Comercial 2' size='15' maxlength='13' class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD:&nbsp;&nbsp;&nbsp;/&nbsp;
            Tel. Fax:
        </td>
        <td>
            E-Mail:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_ddd_fax" title="Digite o DDD fax" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_tel_fax" title="Digite o Telefone Fax" size="15" maxlength="13" class='caixadetexto'>&nbsp;S/ Restri&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_email' title='Digite o E-mail' size='50' maxlength='85' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            P&aacute;gina Web:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name="txt_pagina_web" size="35" title="Digite a P&aacute;gina Web" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observa&ccedil;&atilde;o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea rows='2' cols='100' name='txt_observacao' title='Digite a Observa&ccedil;&atilde;o' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');" style='color:#ff9900' class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" onclick="return validar()" style='color:green' class='botao'>
        </td>
    </tr>
    <!--Aqui busco o Endereço através do Cep do Fornecedor ...-->
    <iframe name='cep' id='cep' marginwidth='0' marginheight='0' frameborder='0' height='0' width='0'></iframe>
</table>
<pre>
<font color='red'><b>Observa&ccedil;&atilde;o:</b></font>

<b>* Os campos em Negrito s&atilde;o obrigat&oacute;rios.</b>
</pre>