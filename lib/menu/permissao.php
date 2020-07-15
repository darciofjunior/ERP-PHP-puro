<?
require('../../lib/segurancas.php');
require('../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../');

if($passo == 1) {
    //Exclui todos os Menus do Usuário p/ o respectivo Módulo ...
    $sql = "DELETE 
            FROM `tipos_acessos` 
            WHERE `id_modulo` = '$_POST[cmb_modulo]' 
            AND `id_login` = '$_POST[cmb_login]' ";
    bancos::sql($sql);
    if(count($_POST['chkt_permissao']) <> 0) {
        foreach($_POST['chkt_permissao'] as $id_permissao) {
            if (strlen($id_permissao) < 4) {
                $id_menu      = $id_permissao;
                $id_menu_item = 0;
            }else {
                $id_menu      = substr($id_permissao, 0, 3);
                $id_menu_item = substr($id_permissao, 12, 10);
            }
            $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, `id_login`, `id_modulo`, `id_menu`, `id_menu_item`) VALUES (NULL, '$cmb_login', '$cmb_modulo', '$id_menu', '$id_menu_item') ";
            bancos::sql($sql);
        }
    }
    /*Busco o nome de Login do Usuário que está sofrendo a Alteração, senão o Sistema interpreta que Login é o 
    do Usuário Logado ferrando aí o Administrador rsrs ...*/
    /*$sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_POST[cmb_login]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);*/
    /**************Quando se inclui uma Permissão, então tenho que recriar um Novo Cache**************/
    //Cada funcionário terá o seu respectivo menu ...
    /*$MenuCache = new Cache('menu_'.$campos_login[0]['login']);
    $MenuCache->Limpa_cache();*/
    /*************************************************************************************************/
}
?>
<html>
<head>
<title>.:: Permissões de Usuários ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Empresa ...
    if(document.form.cmb_empresa.value == '') {
        alert('SELECIONE A EMPRESA !')
        document.form.cmb_empresa.focus()
        return false
    }
//Módulo ...
    if(document.form.cmb_modulo.value == '') {
        alert('SELECIONE O MÓDULO !')
        document.form.cmb_modulo.focus()
        return false
    }
//Login ...
    if(document.form.cmb_login.value == '') {
        alert('SELECIONE O USUÁRIO !')
        document.form.cmb_login.focus()
        return false
    }
    return true
}
</Script>
</head>
<body onload='document.form.cmb_empresa.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value="1">
<input type='hidden' name='tipo' value="<?=$tipo;?>">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <label for='label'>
                Permissões de Usuários Selecionar:
            </label>
            <input type='checkbox' name='chk_todos' title='Selecionar todos' onclick="selecionar_todos_checkbox('form', 'chk_todos')" id='label' class='checkbox'>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Empresa:
            <select name='cmb_empresa' onchange="enviar('form', 'passo', '0')" class='combo'>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                echo combos::combo($sql, $cmb_empresa);
            ?>
          </select>
        </td>
        <td align='center'>
            Módulo:
            <select name='cmb_modulo' onchange="enviar('form', 'passo', '0')" class='combo'>
            <?
                $sql = "SELECT id_modulo, modulo 
                        FROM `modulos` 
                        ORDER BY modulo ";
                echo combos::combo($sql, $cmb_modulo);
            ?>
            </select>
        </td>
        <td align='center'>
            Usuário:
            <select name='cmb_login' onchange="enviar('form', 'passo', '0')" class='combo'>
            <?
                if($cmb_empresa == 4) {//Empresa Grupo ...
                    /*Trago todos os funcionários que não possuem Registro e Logins que sejam cujo 
                    Tipo de Login seja diferente de Funcionário que estejam Ativos ...*/
                    $sql = "(SELECT l.`id_login`, l.`login` 
                            FROM `logins` l 
                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                            WHERE f.`status` < '3' 
                            AND f.`id_empresa` = '$cmb_empresa') 
                            UNION 
                            (SELECT `id_login`, `login` 
                            FROM `logins` 
                            WHERE `tipo_login` <> 'FUNCIONARIO' 
                            AND `ativo` >= '1') ORDER BY `login` ";
                }else {//Outras Empresas só trago aqueles que possuem Registro, conseqüentemente funcionários ...
                    $sql = "SELECT l.`id_login`, l.`login` 
                            FROM `logins` l 
                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                            WHERE f.`status` < '3' 
                            AND f.`id_empresa` = '$cmb_empresa' ORDER BY l.`login` ";
                }
                echo combos::combo($sql, $cmb_login);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='3'>
            <font face="Verdana, Arial, Helvetica, sans-serif" size="-1">
            <img src="../../imagem/bloco_vermelho.gif" width="8" height="8" alt="" border="0">
            <font color="#FF0000">Menu</font>
            &nbsp;&nbsp; <img src="../../imagem/bloco_azul.gif" width="8" height="8" alt="" border="0">
            <font color="#0000FF">Item de Menu</font>
            &nbsp;&nbsp;<img src="../../imagem/bloco_verde.gif" width="8" height="8" alt="" border="0">
            <font color="#006600">Sub Item do Item</font>
            &nbsp;&nbsp;<img src="../../imagem/bloco_negro.gif" width="8" height="8" alt="" border="0">
            <font color="#000000">Sub do Sub Item</font></font></div>
        </td>
    </tr>
<?
    if (!empty($cmb_empresa) && !empty($cmb_modulo)) {
        // TRAZ TODOS MENUS
        $sql = "SELECT * 
                FROM `menus` 
                WHERE `id_modulo` = '$cmb_modulo' ORDER BY menu ";
        $campos1 = bancos::sql($sql);
        $linhas1 = count($campos1);
	for($x = 0; $x < $linhas1; $x++) {
            //VERIFICAR SE ESTAR CHECADO O MENU
            if ($campo[$x]['endereco'] != '') {
                $sql = "select id_tipo_acesso from tipos_acessos where id_modulo = '$cmb_modulo' and id_login='$cmb_login' and id_menu = ".$campos1[$x]['id_menu']." limit 1";
                $campos_menu=bancos::sql($sql);
                $linhas_menu=count($campos_menu);
                if ($linhas_menu>0) {
                    $resultado = 'checked';
                }else {
                    $resultado = '';
                }
                echo '<tr class="linhanormal"><td colspan="3"><input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos1[$x]['id_menu'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><font color="#FF0000"><b>'.$campos1[$x]['menu'].'</b></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
            }else {
                //VERIFICAR SE ESTAR CHECADO O MENU  E FAZ SQL PARA BUSCAR O ITEN
                $sql= "select id_tipo_acesso from tipos_acessos where id_modulo = '$cmb_modulo' and id_login='$cmb_login' and id_menu = ".$campos1[$x]['id_menu']." limit 1";
                $campos_menu    = bancos::sql($sql);
                $linhas_menu    = count($campos_menu);
                $resultado      = ($linhas_menu > 0) ?  'checked' : '';
                echo '<tr class="linhanormal"><td colspan="3"><input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos1[$x]['id_menu'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><font color="#FF0000"><b>'.$campos1[$x]['menu'].'</b></font></td></tr>'."\n";
                // TRAZ TODOS ITENS DO MENU
		$sql = "select * from menus_itens where id_menu = ".$campos1[$x]['id_menu']." and substring(nivel, 7, 6) = '000000' order by id_menu_item ";
		$campos2=bancos::sql($sql);
		$linhas2=count($campos2);
		for ($y = 0; $y < $linhas2; $y++) { // VERIFICA SE ESTAR CHECADO O ITEN
                    $sql = "SELECT * 
                            FROM `menus_itens` 
                            WHERE id_menu = ".$campos1[$x]['id_menu']. " 
                            and substring(nivel, 7, 3)!='000' 
                            and substring(nivel, 1, 6)= ".substr($campos2[$y]['nivel'], 0, 6)." 
                            and substring(nivel, 10, 3) = '000'";
                    $campos3 = bancos::sql($sql);
                    $linhas3 = count($campos3);
                    if ($linhas3 > 0) {
                        $sql = "select id_tipo_acesso from tipos_acessos where id_modulo = '$cmb_modulo' and id_login = '$cmb_login' and id_menu = ".$campos1[$x]['id_menu']. " and id_menu_item = ".$campos2[$y]['id_menu_item']." limit 1";
                        $campos_item    = bancos::sql($sql);
                        $linhas_item    = count($campos_item);
                        $resultado      = ($linhas_item > 0) ? 'checked' : '';
                        echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkt_permissao[]" '.$resultado.' value='.$campos2[$y]['nivel'].$campos2[$y]['id_menu_item'].' class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><b><font color="#0720FF">'.$campos2[$y]['item'].'</font></b></font></td></tr>'."\n";
                    }else {
                        // VERIFICAR SE ESTAR CHECADO O ITEN E FAZ SQL PARA BUSCAR O SUBITEN
                        $sql = "SELECT id_tipo_acesso 
                                FROM `tipos_acessos` 
                                WHERE `id_modulo` = '$cmb_modulo' 
                                AND `id_login` = '$cmb_login' 
                                AND `id_menu` = ".$campos1[$x]['id_menu']." 
                                AND `id_menu_item` = ".$campos2[$y]['id_menu_item']." LIMIT 1 ";
                        $campos_item    = bancos::sql($sql);
                        $linhas_item    = count($campos_item);
                        $resultado      = ($linhas_item > 0) ? 'checked' : '';
                        echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos2[$y]['nivel'].$campos2[$y]['id_menu_item'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><b><font color="#0720FF">'.$campos2[$y]['item'].'</font></b></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
                    }
                    // TRAZ TODOS SUBITENS DO MENU
                    for($k = 0; $k < $linhas3; $k++) {
                        // VERIFICAR SE ESTAR CHECADO O SUBITEN
                        $sql = "SELECT * 
                                FROM `menus_itens` 
                                WHERE `id_menu` = ".$campos1[$x]['id_menu']." 
                                AND substring(nivel, 10, 3) != '000' 
                                AND substring(nivel, 1, 9) = ".substr($campos3[$k]['nivel'], 0, 9);
                        $campos4 = bancos::sql($sql);
                        $linhas4 = count($campos4);
                        if($linhas4 > 0) {
                            $sql = "select id_tipo_acesso from tipos_acessos where id_modulo='$cmb_modulo' and id_login='$cmb_login' and id_menu=".$campos1[$x]['id_menu']." and id_menu_item = ".$campos3[$k]['id_menu_item']." limit 1";
                            $campos_subitem = bancos::sql($sql);
                            $linhas_subitem = count($campos_subitem);
                            $resultado = ($linhas_subitem > 0) ? 'checked' : '';
                            echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos3[$k]['nivel'].$campos3[$k]['id_menu_item'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><font color="#006600">&nbsp;<b>'.$campos3[$k]['item'].'</b></font></td></tr>'."\n";
                        }else {
                            // VERIFICAR SE ESTAR CHECADO O SUBITEN E FAZ SQL PARA BUSCAR O SUBSUBITEN
                            $sql = "select id_tipo_acesso from tipos_acessos where id_modulo='$cmb_modulo' and id_login='$cmb_login' and id_menu =".$campos1[$x]['id_menu']." and id_menu_item = ".$campos3[$k]['id_menu_item']." limit 1";
                            $campos_subitem=bancos::sql($sql);
                            $linhas_subitem=count($campos_subitem);
                            $resultado = ($linhas_subitem > 0) ? 'checked' : '';
                            echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos3[$k]['nivel'].$campos3[$k]['id_menu_item'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><font color="#006600">&nbsp;<b>'.$campos3[$k]['item'].'</b></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
                        }
                        for($w = 0; $w < $linhas4; $w++) {
                            // VERIFICA SE ESTA CHECADO O SUBSUBITEN
                            $sql = "SELECT id_tipo_acesso 
                                    FROM `tipos_acessos` 
                                    WHERE `id_modulo` = '$cmb_modulo' 
                                    AND `id_login` = '$cmb_login' 
                                    AND `id_menu` = ".$campos1[$x]['id_menu']." 
                                    AND `id_menu_item` =".$campos4[$w]['id_menu_item'];
                            $campos_subsubitem  = bancos::sql($sql);
                            $linhas_subsubitem  = count($campos_subsubitem);
                            $resultado          = ($linhas_subsubitem>0) ? 'checked' : '';
                            echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="chkt_permissao[]" '.$resultado.' value="'.$campos4[$w]['nivel'].$campos4[$w]['id_menu_item'].'" class="checkbox" onClick="selecionar_checkbox'."('form', 'chk_todos')".'"><b><font color="#010000">'.$campos4[$w]['item'].'</font></b></font></td></tr>'."\n";
                        }
                    }
                }
            }
	}
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.cmb_empresa.focus()' class='botao'>
            <input type='submit' name='cmd_permitir' value='Permitir' title='Permitir' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>