<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');

if($id_empresa_menu == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
}else if($id_empresa_menu == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
}else if($id_empresa_menu == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `fone1`, `fone2`, `fax`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 2:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `fone1`, `fone2`, `fax`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 3:
            $sql = "SELECT `id_fornecedor`, `fone1`, `fone2`, `fax`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `produto` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 4:
            $sql = "SELECT `id_fornecedor`, `fone1`, `fone2`, `fax`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `codigo` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `fone1`, `fone2`, `fax`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar_fornecedor.php?id_empresa_menu=<?=$id_empresa_menu;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Fornecedor(es) p/ Incluir Conta Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Fornecedor(es) p/ Incluir Conta Automática
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Fone 1
        </td>
        <td>
            Fone 2
        </td>
        <td>
            Fax
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?id_empresa_menu='.$id_empresa_menu.'&id_fornecedor='.$campos[$i]['id_fornecedor'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <a href='<?=$url;?>' class='link'>
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        <td>
            <?=$campos[$i]['fone1'];?>
        </td>
        <td>
            <?=$campos[$i]['fone2'];?>
        </td>
        <td>
            <?=$campos[$i]['fax'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_fornecedor.php?id_empresa_menu=<?=$id_empresa_menu;?>'" class='botao'>
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
<title>.:: Consultar Fornecedor(es) p/ Incluir Conta Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/sessao.js'></Script>
<script Language = 'Javascript'>
function limpar() {
    document.form.txt_consultar.disabled = true
    
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<!--*******Controle de Tela*******-->
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_empresa_menu' value='<?=$id_empresa_menu;?>'>
<!--******************************-->
<table width='70%' cellspacing ='1' cellpadding='1' border='0' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fornecedor(es) p/ Incluir Conta Automática
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="1" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Razão Social" id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="2" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: CNPJ ou CPF" id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value="3" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Produto" id='label3'>
            <label for='label3'>Produto</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value="4" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Código" id='label4'>
            <label for='label4'>Código</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title="Consultar fornecedores internacionais" id='label5' class='checkbox'>
            <label for='label5'>Internacionais</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os fornecedores" id='label6' class='checkbox'>
            <label for='label6'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>