<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
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
            
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
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
        window.location = 'consultar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Financeiro(s) de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Consultar Produto(s) Financeiro(s) de Fornecedor(es)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
        <?
            if($opt_internacional == 1) {
                echo 'Internacional(s)';
            }else {
                echo 'Nacional(s)';
            }
        ?>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">	
        <td width='10'>
            <a href="consultar.php?passo=2&id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>&razaosocial=<?=$campos[$i]['razaosocial'];?>&opt_internacional=<?=$opt_internacional;?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>" class="link">
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="consultar.php?passo=2&id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>&razaosocial=<?=$campos[$i]['razaosocial'];?>&opt_internacional=<?=$opt_internacional;?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>" class="link">
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
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
}else if($passo == 2) {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Financeiro(s) de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name="txt_consultar1" value="<?=$_GET['txt_consultar']?>">
<input type='hidden' name="opt_opcao1" value="<?=$_GET['opt_opcao']?>">
<input type='hidden' name="opt_internacional" value="<?=$_GET['opt_internacional'];?>">
<input type='hidden' name='passo' value='3'>
<input type='hidden' name='razaosocial' value='<?=$_GET['razaosocial'];?>'>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Consultar Produto(s) Financeiro(s) do Fornecedor
            <font color='yellow'>
                <br><?=$_GET['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size=45 maxlength=45 class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Fornecedores por: Razão Social" id='label'>
            <label for="label">Referência</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar Fornecedores por: Discriminação" id='label2' checked>
            <label for="label2">Discriminação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="2">
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os fornecedores" class="checkbox" id='label3'>
            <label for="label3">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php'" class='botao'>
            <input type="reset" name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar();' style='color:#ff9900;' class='botao'>
            <input type="submit" name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if ($passo == 3) {
/*********************************************************************/
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_consultar1     = $_POST['txt_consultar1'];
        $opt_opcao1         = $_POST['opt_opcao1'];
        $opt_internacional  = $_POST['opt_internacional'];
        $razaosocial        = $_POST['razaosocial'];
        $id_fornecedor      = $_POST['id_fornecedor'];
    }else {
        $txt_consultar1     = $_GET['txt_consultar1'];
        $opt_opcao1         = $_GET['opt_opcao1'];
        $opt_internacional  = $_GET['opt_internacional'];
        $razaosocial        = $_GET['razaosocial'];
        $id_fornecedor      = $_GET['id_fornecedor'];
    }
/*********************************************************************/
    switch ($opt_opcao) {
        case 1:
            $sql = "SELECT g.nome, pf.discriminacao, pf.observacao 
                    FROM `produtos_financeiros` pf 
                    INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                    INNER JOIN `produtos_financeiros_vs_fornecedor` pfv ON pfv.id_produto_financeiro = pf.id_produto_financeiro AND pfv.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = pfv.id_fornecedor AND f.`id_fornecedor` = '$id_fornecedor' 
                    WHERE pf.`referencia` LIKE '%$txt_consultar%' 
                    AND pf.`ativo` = '1' ";
        break;
        case 2:
            $sql = "SELECT g.nome, pf.discriminacao, pf.observacao 
                    FROM `produtos_financeiros` pf 
                    INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                    INNER JOIN `produtos_financeiros_vs_fornecedor` pfv ON pfv.id_produto_financeiro = pf.id_produto_financeiro AND pfv.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = pfv.id_fornecedor AND f.`id_fornecedor` = '$id_fornecedor' 
                    WHERE pf.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pf.`ativo` = '1' ";
        break;
        default:
            $sql = "SELECT g.nome, pf.discriminacao, pf.observacao 
                    FROM `produtos_financeiros` pf 
                    INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                    INNER JOIN `produtos_financeiros_vs_fornecedor` pfv ON pfv.id_produto_financeiro = pf.id_produto_financeiro AND pfv.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = pfv.id_fornecedor AND f.`id_fornecedor` = '$id_fornecedor' 
                    WHERE pf.`ativo` = '1' ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?passo=2&opt_internacional=<?=$opt_internacional;?>&txt_consultar=<?=$txt_consultar1;?>&opt_opcao=<?=$opt_opcao1;?>&id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Financeiro(s) de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Produto(s) Financeiro(s) do Fornecedor
            <font color='yellow'>
                <br><?=$razaosocial;?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Grupo
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php?passo=2&razaosocial=<?=$razaosocial;?>&id_fornecedor=<?=$id_fornecedor;?>&opt_internacional=<?=$opt_internacional;?>'" class="botao">
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
<title>.:: Consultar Produto(s) Financeiro(s) de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value   = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1'; ?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Financeiro(s) de Fornecedor(es)
        </td>
    </tr>
    <tr class='linhanormal' align="center">
            <td colspan='2'>
                Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" checked onclick="document.form.txt_consultar.focus()" title="Consultar fornecedores por: Razão Social" id='label'>
            <label for="label">Razão Social</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedores por: CNPJ ou CPF" id='label2'>
            <label for="label2">CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='checkbox' name='opt_internacional' value='1' title="Consultar fornecedores internacionais" class="checkbox" id='label3'>
            <label for="label3">Internacionais</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os Fornecedores" class="checkbox" id='label4'>
            <label for="label4">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>