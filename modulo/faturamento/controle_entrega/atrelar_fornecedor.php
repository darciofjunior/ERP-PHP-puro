<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    if($opt_internacional == 1) $condicao_pais = " AND `id_pais` <> '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais 
                    ORDER BY `razaosocial` ";
        break;
        case 2:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
                
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais 
                    ORDER BY `razaosocial` ";
        break;
        case 3:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `produto` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais 
                    ORDER BY `razaosocial` ";
        break;
        case 4:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `codigo` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais 
                    ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais 
                    ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'atrelar_fornecedor.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Fornecedor(es) p/ Atrelar à Controle de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_fornecedor) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATRELAR ESSE FORNECEDOR ?')
    if(resposta == true) {
        window.opener.document.form.hdd_fornecedores_atrelados.value = window.opener.document.form.hdd_fornecedores_atrelados.value + id_fornecedor + ','
        window.opener.document.form.action = ''
        window.opener.document.form.target = ''
        window.opener.document.form.submit()
        window.close()
    }else {
        return false
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Fornecedor(es) p/ Atrelar à Controle de Entrega
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endereço
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="javascript:avancar('<?=$campos[$i]['id_fornecedor'];?>')">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:avancar('<?=$campos[$i]['id_fornecedor'];?>')" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['endereco'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'atrelar_fornecedor.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
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
<title>.:: Consular Fornecedor(es) p/ Atrelar à Controle de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4; i++) document.form.opt_opcao[i].disabled = false
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
            Consular Fornecedor(es) p/ Atrelar à Controle de Entrega
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Fornecedor(es) por: Razão Social' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Fornecedor(es) por: CNPJ ou CPF' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Fornecedor(es) por: Produto' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Produto</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Consultar Fornecedor(es) por: Código' onclick='document.form.txt_consultar.focus()' id='label4'>
            <label for='label4'>Código</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title='Consultar Fornecedor(es) Internacional(is)' id='label5' class='checkbox'>
            <label for='label5'>Internacionais</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todo(s) o(s) Fornecedor(es)' onclick='limpar()' id='label6' class='checkbox'>
            <label for='label6'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>