<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLONAGEM REALIZADA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT f.id_funcionario, f.nome, l.id_login, l.login, e.nomefantasia 
                    FROM `logins` l 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    WHERE l.`login` LIKE '%$txt_consultar%' ORDER BY l.login ";
        break;
        default:
            $sql = "SELECT f.id_funcionario, f.nome, l.id_login, l.login, e.nomefantasia 
                    FROM `logins` l 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` ORDER BY l.login ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'clonar_permissao.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Login(s) p/ Clonar Permissão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' cellpadding='1' cellspacing='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Login(s) p/ Clonar Permissão
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Usuário
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i ++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <a href='clonar_permissao.php?passo=2&id_login_loop=<?=$campos[$i]['id_login'];?>' class='link'>
                <?=$campos[$i]['login'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'clonar_permissao.php'" class='botao'>
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
    $sql = "SELECT DISTINCT (m.id_modulo), m.modulo 
            FROM `tipos_acessos` ta 
            INNER JOIN `modulos` m ON m.`id_modulo` = ta.`id_modulo` 
            WHERE ta.`id_login` = '$_GET[id_login_loop]' ORDER BY m.modulo ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Clonar Permissão dos Módulos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3'?>' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Clonar Permissão dos Módulos permitidos ao Login => 
            <font color='yellow'>
            <?
                $sql = "SELECT login 
                        FROM `logins` 
                        WHERE `id_login` = '$_GET[id_login_loop]' LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                echo $campos_login[0]['login'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Módulo
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_modulo[]' value="<?=$campos[$i]['id_modulo'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['modulo'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'clonar_permissao.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_login_loop' value='<?=$_GET['id_login_loop'];?>'>
</form>
</body>
</html>
<?
}else if($passo == 3) {
?>
<title>.:: Clonar Permissão dos Módulos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            if(document.form.elements[i].value == '') {
                alert('SELECIONE PELO MENOS UM LOGIN !')
                document.form.elements[i].focus()
                return false
            }
        }
    }
}
</Script>
<body onload='document.form.elements[0].focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=4';?>' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Clonar Permissão dos Módulos permitidos ao Login => 
            <font color='yellow'>
            <?
                $sql = "SELECT login 
                        FROM `logins` 
                        WHERE `id_login` = '$_POST[id_login_loop]' LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                echo $campos_login[0]['login'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Logins que irão receber a clonagem: 
        </td>
        <td align='center'>
            <select name='cmb_logins[]' class='combo' size='5' multiple>
            <?
                //Listagem de Funcionários independente da Empresa em "Férias ou Ativo" ...
                $sql = "SELECT l.id_login, l.login 
                        FROM `logins` l 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` AND f.`status` < '2' 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                        WHERE l.`id_login` <> '$_POST[id_login_loop]' ORDER BY l.login ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'clonar_permissao.php?passo=2&id_login_loop=<?=$_POST['id_login_loop'];?>'" class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_login_loop' value='<?=$_POST['id_login_loop'];?>'>
<?
    foreach($_POST['chkt_modulo'] as $id_modulo_loop) $modulos.= $id_modulo_loop.', ';
?>
<input type='hidden' name='hdd_modulos' value='<?=$modulos;?>'>
</form>
</body>
</html>
<?
}else if($passo == 4) {
    $modulos        = substr($_POST['hdd_modulos'], 0, strlen($_POST['hdd_modulos']) - 2);
    $vetor_modulos  = explode(',', $modulos);
    $id_logins      = implode(',', $_POST['cmb_logins']);
    
    foreach($vetor_modulos as $id_modulo_loop) {
        //Deleto todas as permissões dos logins selecionados, porque os mesmos irão receber as novas permissões clonadas ...
        $sql = "DELETE FROM `tipos_acessos` WHERE `id_modulo` = '$id_modulo_loop' AND `id_login` IN ($id_logins) ";
        bancos::sql($sql);
        /*Busco todas as permissões de Menu e Item de Menu do Login principal p/ atribuir aos logins selecionados 
        as novas permissões Clonadas ...*/
        $sql = "SELECT id_menu, id_menu_item 
                FROM `tipos_acessos` 
                WHERE `id_modulo` = '$id_modulo_loop' 
                AND `id_login` = '$_POST[id_login_loop]' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
            //Adiciono todas as permissões de menu para os logins selecionados que vão receber a clonagem das permissões ...
            foreach($_POST['cmb_logins'] as $id_login_loop) {
                $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, `id_login`, `id_modulo`, `id_menu`, `id_menu_item`) VALUES (NULL, '$id_login_loop', '$id_modulo_loop', '".$campos[$i]['id_menu']."', '".$campos[$i]['id_menu_item']."') ";
                bancos::sql($sql);
            }
        }
    }
    foreach($_POST['cmb_logins'] as $id_login_loop) {
        /*Busco o nome de Login do Usuário que está sofrendo a Alteração, senão o Sistema interpreta que Login é o 
        do Usuário Logado ferrando aí o Administrador rsrs ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$id_login_loop' LIMIT 1 ";
        $campos_login = bancos::sql($sql);
        /**************Quando se inclui uma Permissão, então tenho que recriar um Novo Cache**************/
        //Cada funcionário terá o seu respectivo menu ...
        $MenuCache = new Cache('menu_'.$campos_login[0]['login']);
        $MenuCache->Limpa_cache();
        /*************************************************************************************************/
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'clonar_permissao.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Login(s) p/ Clonar Permissão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
</script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Login(s) p/ Clonar Permissão
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Usuário por: Login' onclick='document.form.txt_consultar.focus()' id='label1' checked>
            <label for='label1'>Login</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos os Logins' class='checkbox' id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>