<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/alterar.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
/*Alguns dos dados buscados pelo SQL são exibidos em tela e outros são levados para a tela abaixo 
de incluir ou alterar representante ...

Aqui eu só listo os funcionários que são funcionários nos cargos de (Supervisor Externo de Vendas "25" 
e Supervisor Interno de Vendas "109") ou em exclusivo o Wilson porque é diretor Comercial e chefe de Vendas ...*/
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`endereco`, CONCAT(f.`numero`, ' ', f.`complemento`) AS num_comp, f.`cep`, f.`bairro`, f.`cidade`, 
                    u.`sigla`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, 
                    f.`ddd_celular`, f.`telefone_celular`, f.`cpf`, f.`email_externo`, 
                    e.`nomefantasia` AS empresa, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND (f.`id_cargo` IN (25, 109) OR f.`id_funcionario` = '68') 
                    INNER JOIN `ufs` u ON u.`id_uf` = f.`id_uf` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
        case 2:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`endereco`, CONCAT(f.`numero`, ' ', f.`complemento`) AS num_comp, f.`cep`, f.`bairro`, f.`cidade`, 
                    u.`sigla`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, 
                    f.`ddd_celular`, f.`telefone_celular`, f.`cpf`, f.`email_externo`, 
                    e.`nomefantasia` AS empresa, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND (f.`id_cargo` IN (25, 109) OR f.`id_funcionario` = '68') 
                    INNER JOIN `ufs` u ON u.`id_uf` = f.`id_uf` 
                    WHERE e.`nomefantasia` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
        case 3:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`endereco`, CONCAT(f.`numero`, ' ', f.`complemento`) AS num_comp, f.`cep`, f.`bairro`, f.`cidade`, 
                    u.`sigla`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, 
                    f.`ddd_celular`, f.`telefone_celular`, f.`cpf`, f.`email_externo`, 
                    e.`nomefantasia` AS empresa, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND (f.`id_cargo` IN (25, 109) OR f.`id_funcionario` = '68') 
                    INNER JOIN `ufs` u ON u.`id_uf` = f.`id_uf` 
                    WHERE c.`cargo` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
        case 4:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`endereco`, CONCAT(f.`numero`, ' ', f.`complemento`) AS num_comp, f.`cep`, f.`bairro`, f.`cidade`, 
                    u.`sigla`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, 
                    f.`ddd_celular`, f.`telefone_celular`, f.`cpf`, f.`email_externo`, 
                    e.`nomefantasia` AS empresa, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND (f.`id_cargo` IN (25, 109) OR f.`id_funcionario` = '68') 
                    INNER JOIN `ufs` u ON u.`id_uf` = f.`id_uf` 
                    WHERE d.`departamento` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`endereco`, CONCAT(f.`numero`, ' ', f.`complemento`) AS num_comp, f.`cep`, f.`bairro`, f.`cidade`, 
                    u.`sigla`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, 
                    f.`ddd_celular`, f.`telefone_celular`, f.`cpf`, f.`email_externo`, 
                    e.`nomefantasia` AS empresa, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND (f.`id_cargo` IN (25, 109) OR f.`id_funcionario` = '68') 
                    INNER JOIN `ufs` u ON u.`id_uf` = f.`id_uf` 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar_funcionario.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function retornar_dados(id_func_selecionado, representante, cargo, cep, endereco, num_comp, bairro, cidade, estado, fone, celular, cpf, empresa, email) {
    parent.document.form.hdd_funcionario_selecionado.value  = id_func_selecionado
    parent.document.form.txt_nome_representante.value       = representante
    parent.document.form.txt_cargo.value                    = cargo
    parent.document.form.cmb_pais.value                     = 31//Sempre 31 porque ... se é nosso funcionário, conseqüentemente este é do Brasil ...
    parent.document.form.txt_cep.value                      = cep
    parent.document.form.txt_endereco.value                 = endereco
    parent.document.form.txt_num_complemento.value          = num_comp
    parent.document.form.txt_bairro.value                   = bairro
    parent.document.form.txt_cidade.value                   = cidade
    parent.document.form.txt_estado.value                   = estado
    parent.document.form.txt_fone.value                     = fone
    parent.document.form.txt_cel_fax.value                  = celular
    parent.document.form.txt_cnpj_cpf.value                 = cpf
    parent.document.form.txt_empresa.value                  = empresa
    parent.document.form.txt_email.value                    = email
    parent.html5Lightbox.finish()
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Depto.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $ddd_cel = ($campos[$i]['ddd_celular'] == 0) ? '' : $campos[$i]['ddd_celular'];
            $tel_cel = ($campos[$i]['telefone_celular'] == 0) ? '' : $campos[$i]['telefone_celular'];
            $cel_fax = $ddd_cel.' '.$tel_cel;
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:retornar_dados('<?=$campos[$i]['id_funcionario'];?>', '<?=strtoupper($campos[$i]['nome']);?>', '<?=$campos[$i]['cargo'];?>', '<?=$campos[$i]['cep'];?>', '<?=$campos[$i]['endereco'];?>', '<?=$campos[$i]['num_comp'];?>', '<?=$campos[$i]['bairro']?>', '<?=$campos[$i]['cidade'];?>', '<?=$campos[$i]['sigla'];?>', '<?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>', '<?=$cel_fax;?>', '<?=$campos[$i]['cpf'];?>', '<?=$campos[$i]['empresa'];?>', '<?=$campos[$i]['email_externo'];?>')" align='left'>
            <a href="javascript:retornar_dados('<?=$campos[$i]['id_funcionario'];?>', '<?=strtoupper($campos[$i]['nome']);?>', '<?=$campos[$i]['cargo'];?>', '<?=$campos[$i]['cep'];?>', '<?=$campos[$i]['endereco'];?>', '<?=$campos[$i]['num_comp'];?>', '<?=$campos[$i]['bairro']?>', '<?=$campos[$i]['cidade'];?>', '<?=$campos[$i]['sigla'];?>', '<?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>', '<?=$cel_fax;?>', '<?=$campos[$i]['cpf'];?>', '<?=$campos[$i]['empresa'];?>', '<?=$campos[$i]['email_externo'];?>')" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['empresa'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_funcionario.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value   = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4 ;i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Funcionário' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title="Consultar Funcionário por: Nome" onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Nome</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title="Consultar Funcionário por: Empresa" onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>Empresa</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Funcionário por: Cargo' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Cargo</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Consultar Funcionário por: Departamento' onclick='document.form.txt_consultar.focus()' id='label4'>
            <label for='label4'>Departamento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='5' title='Consultar todos os funcionários' onclick='limpar()' class='checkbox' id='label5'>
            <label for='label5'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>