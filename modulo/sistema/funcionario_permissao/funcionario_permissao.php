<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`nome`, l.`id_login`, l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' ORDER BY f.`nome` ";
        break;
        case 2:
            $sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`nome`, l.`id_login`, l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` AND l.`id_login` LIKE '%$txt_consultar%' 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    ORDER BY f.`nome` ";
        break;
        case 3:
            $sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`nome`, l.`id_login`, l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`nomefantasia` LIKE '%$txt_consultar%' 
                    ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`nome`, l.`id_login`, l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'funcionario_permissao?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Funcionário(s) p/ verificar Permissões ::.</title>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' cellpadding='1' cellspacing='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Funcionário(s) p/ verificar Permissões
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
        <td>
            Login
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'funcionario_permissao.php?passo=2&id_login_loop='.$campos[$i]['id_login'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['login'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'funcionario_permissao.php'" class='botao'>
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
<title>.:: Permissões de Funcionário ::.</title>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' cellpadding='1' cellspacing='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td>
            Permissões do Funcionário:
            <font color='yellow'>
            <?
                $sql = "SELECT `login` 
                        FROM `logins` 
                        WHERE `id_login` = '$_GET[id_login_loop]' LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                echo $campos_login[0]['login'];
            ?>
            </font>
        </td>
    </tr>
<?
    //Trago todos os módulos no qual o Login passado por parâmetro tem permissão ...
    $sql = "SELECT DISTINCT (m.`id_modulo`), m.`modulo` 
            FROM `tipos_acessos` ta 
            INNER JOIN `modulos` m ON m.`id_modulo` = ta.`id_modulo` 
            WHERE ta.`id_login` = '$_GET[id_login_loop]' ORDER BY m.`modulo` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='#6600cc'>
                <b><?=$campos[$i]['modulo'];?></b>
            </font>
        </td>
    </tr>
<?
        //Trago todos os menus dos módulos no qual o Login passado por parâmetro tem permissão ...
        $sql = "SELECT DISTINCT(m.`id_menu`), m.`menu` 
                FROM `tipos_acessos` ta 
                INNER JOIN `menus` m ON m.`id_menu` = ta.`id_menu` 
                WHERE ta.`id_modulo` = '".$campos[$i]['id_modulo']."' 
                AND ta.`id_login` = '$_GET[id_login_loop]' ";
        $campos_menus = bancos::sql($sql);
        $linhas_menus = count($campos_menus);
        // Exibe os menus ...
        for($j = 0;$j < $linhas_menus; $j++) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_menus[$j]['menu'];?></b>
            </font>
        </td>
    </tr>
<?
            //Trago todos os Itens do Menu do Loop ...
            $sql = "SELECT * 
                    FROM `menus_itens` 
                    WHERE `id_menu` = '".$campos_menus[$j]['id_menu']."' 
                    AND SUBSTRING(`nivel`, 7, 6) = '000000' ORDER BY `id_menu_item` ";
            $campos_itens_menus = bancos::sql($sql);
            $linhas_itens_menus = count($campos_itens_menus);
            for($k = 0; $k < $linhas_itens_menus; $k++) {
                $id_menu_item   = $campos_itens_menus[$k]['id_menu_item'];
                //Trago todos os Sub-Itens do Item de Menu do Loop ...
                $sql = "SELECT * 
                        FROM `menus_itens` 
                        WHERE `id_menu` = '".$campos_menus[$j]['id_menu']."' 
                        AND SUBSTRING(`nivel`, 7, 3) != '000' 
                        AND SUBSTRING(`nivel`, 1, 6) = '".substr($campos_itens_menus[$k]['nivel'], 0, 6)."' 
                        AND SUBSTRING(`nivel`, 10, 3) = '000' ";
                $campos_sub_itens_menus = bancos::sql($sql);
                $linhas_sub_itens_menus = count($campos_sub_itens_menus);
                if($linhas_sub_itens_menus > 0) {//Significa que existe pelo menos 1 Sub-Item p/ o Item do Loop ...
                    //Verifico se o Login do Loop tem permissão nesse Item ...
                    $sql = "SELECT `id_tipo_acesso` 
                            FROM `tipos_acessos` 
                            WHERE `id_login` = '$_GET[id_login_loop]' 
                            AND `id_menu_item` = '$id_menu_item' ";
                    $campos_permissao_sub_item = bancos::sql($sql);
                    if(count($campos_permissao_sub_item) > 0) {//Significa que o Login tem permissão nesse Item de Menu do Loop ...
?>
    <tr class='linhanormal'>
        <td>
            <font color='#0033ff'>
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_itens_menus[$k]['item'];?></b>
            </font>
        </td>
    </tr>
<?
                    }
                }else {//Não existe nenhum 1 Sub-Item p/ o Item do Loop ...
                    //Verifica se o Login do Loop tem permissão nesse Item ...
                    $sql = "SELECT `id_tipo_acesso` 
                            FROM `tipos_acessos` 
                            WHERE `id_login` = '$_GET[id_login_loop]' 
                            AND `id_menu_item` = '$id_menu_item' ";
                    $campos_permissao_item = bancos::sql($sql);
                    if(count($campos_permissao_item) > 0) {//Significa que o Login tem permissão nesse Item de Menu do Loop ...
?>
    <tr class='linhanormal'>
        <td>
            <font color='#0033ff'>
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_itens_menus[$k]['item'];?></b>
            </font>
        </td>
    </tr>
<?
                    }
                }
                //Exibe os Sub-Itens ...
                for($l = 0; $l < $linhas_sub_itens_menus; $l++) {
                    //Trago todos os Sub-Sub-Itens do Sub-Item de Menu do Loop ...
                    $sql = "SELECT * 
                            FROM `menus_itens` 
                            WHERE `id_menu` = ".$campos_menus[$j]['id_menu']." 
                            AND SUBSTRING(`nivel`, 10, 3) != '000' 
                            AND SUBSTRING(`nivel`, 1, 9) = ".substr($campos_sub_itens_menus[$l]['nivel'], 0, 9);
                    $campos_sub_sub_item = bancos::sql($sql);
                    $linhas_sub_sub_item = count($campos_sub_sub_item);
                    if($linhas_sub_sub_item > 0) {//Significa que existe pelo menos 1 Sub-Sub-Item p/ o Sub-Item do Loop ...
                        //Verifica se o Login do Loop tem permissão nesse Sub-Item do Loop ...
                        $sql = "SELECT `id_tipo_acesso` 
                                FROM `tipos_acessos` 
                                WHERE `id_login` = '$_GET[id_login_loop]' 
                                AND `id_menu_item` = '".$campos_sub_itens_menus[$l]['id_menu_item']."' ";
                        $campos_permissao_sub_item  = bancos::sql($sql);
                        if(count($campos_permissao_sub_item) > 0) {//Significa que o Login tem permissão nesse Sub-Item de Menu do Loop ...
?>
    <tr class='linhanormal'>
        <td>
            <font color="#669966">
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_sub_itens_menus[$l]['item'];?></b>
            </font>
        </td>
    </tr>
<?
                        }
                    }else {//Significa que NÃO existe 1 Sub-Sub-Item p/ o Sub-Item do Loop ...
                        //Verifica se o Login do Loop tem permissão nesse Sub-Item do Loop ...
                        $sql = "SELECT `id_tipo_acesso` 
                                FROM `tipos_acessos` 
                                WHERE `id_login` = '$_GET[id_login_loop]' 
                                AND `id_menu_item` = '".$campos_sub_itens_menus[$l]['id_menu_item']."' ";
                        $campos_permissao_sub_item = bancos::sql($sql);
                        if(count($campos_permissao_sub_item) > 0) {//Significa que o Login tem permissão nesse Sub-Item de Menu do Loop ...
?>
    <tr class='linhanormal'>
        <td>
            <font color='#669966'>
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_sub_itens_menus[$l]['item'];?></b>
            </font>
        </td>
    </tr>
<?
                        }
                    }
                    //Exibe os Sub-Sub-Itens ...
                    for($b = 0; $b < $linhas_sub_sub_item; $b++) {
                        //Verifica se o Login do Loop tem permissão nesse Sub-Sub-Item ...
                        $sql = "SELECT `id_tipo_acesso` 
                                FROM `tipos_acessos` 
                                WHERE `id_login` = '$_GET[id_login_loop]' 
                                AND `id_menu_item` = '".$campos_sub_sub_item[$j]['id_menu_item']."' ";
                        $campos_permissao_sub_sub_item = bancos::sql($sql);
                        if(count($campos_permissao_sub_sub_item) > 0) {//Significa que o Login tem permissão nesse Sub-Sub-Item de Menu do Loop ...
?>
    <tr class='linhanormal'>
        <td>
            <font color='#003300'>
                <b><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$campos_sub_sub_item[$j]['item'];?></b>
            </font>
        </td>
    </tr>
<?
                        }
                    }
                }
            }
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' onclick="window.location = 'funcionario_permissao.php<?=$parametro;?>'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) p/ verificar Permissões ::.</title>
<meta http-equiv ='Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
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
</script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s) p/ verificar Permissões
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Funcionarior por: Nome' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Funcionário</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Funcionário por: Login' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>Login</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' onclick='document.form.txt_consultar.focus()' title='Consultar funcionário por: Empresa' id='label3'>
            <label for='label3'>Empresa</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos os Funcionários' class='checkbox' id='label4'>
            <label for='label4'>Todos os registros</label>
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