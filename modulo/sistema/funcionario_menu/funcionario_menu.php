<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('/var/www/erp/albafer/lib/cache/Cache.class.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">PERMISSÃO INCLUÍDA COM SUCESSO PARA ESTE FUNCIONÁRIO.</font>';
$mensagem[2] = '<font class="erro">PERMISSÃO JÁ EXISTENTE PARA ESTE FUNCIONÁRIO.</font>';
$mensagem[3] = '<font class="confirmacao">PERMISSÃO EXCLUÍDA COM SUCESSO PARA ESTE FUNCIONÁRIO.</font>';

if($passo == 1) {
    //Permissão de "id_menu" ou "id_item_menu" p/ o Login que foi selecionado na combo ...
    if($_GET['incluir_permissao'] == 1) {
        /*********************Permissões de Menu*********************/
        if(!empty($_GET['id_menu'])) {
            //Verifico se o Login selecionado já possui acesso no "id_menu" que foi passado por parâmetro ...
            $sql = "SELECT id_tipo_acesso 
                    FROM `tipos_acessos` 
                    WHERE `id_login` = '$_GET[id_login_current]' 
                    AND `id_modulo` = '$_GET[cmb_modulo]' 
                    AND `id_menu` = '$_GET[id_menu]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não possui acesso ainda ...
                //Aqui dou a permissão p/ o Login no respectivo "id_menu" passado por parâmetro ...
                $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, id_login, id_modulo, id_menu, id_menu_item) VALUES (NULL, '$_GET[id_login_current]', '$_GET[cmb_modulo]', '$_GET[id_menu]', '0') ";
                bancos::sql($sql);
            }
        }
        /******Permissões de Item / Sub / Sub Sub Item de Menu*******/
        if(!empty($_GET['id_menu_item'])) {
            /*Aqui eu verifico em qual categoria que se enquadra esse "id_item_menu" que foi passado por parâmetro
            se ele é id_item, sub_item, sub_sub_item ...*/
            $sql = "SELECT nivel 
                    FROM `menus_itens` 
                    WHERE `id_menu_item` = '$_GET[id_menu_item]' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $menu           = substr($campos[0]['nivel'], 0, 3);
            $item           = substr($campos[0]['nivel'], 3, 3);
            $sub_item       = substr($campos[0]['nivel'], 6, 3);
            $sub_sub_item   = substr($campos[0]['nivel'], 9, 3);

            if($sub_sub_item != 000) {//Significa que esse "id_item_menu" é um "sub_sub_item" ...
                $estrutura_inicial_nivel    = substr($campos[0]['nivel'], 0, 9);//Tanto o Menu, Item e Sub-Item possui o início com essa numeração em comum ...
                $id_sub_sub_item_menu       = $_GET['id_menu_item'];

                //Busca do "sub_item" acima do "sub_item" ...
                $sql = "SELECT id_menu_item 
                        FROM `menus_itens` 
                        WHERE SUBSTRING(`nivel`, 1, 9) = '$estrutura_inicial_nivel' 
                        AND `endereco` = '' 
                        AND `id_menu_item` < '$id_sub_sub_item_menu' ORDER BY id_menu_item DESC LIMIT 1 ";
                $campos             = bancos::sql($sql);
                $id_sub_item_menu   = $campos[0]['id_menu_item'];

                //Busca do "item" acima do "sub_item" ...
                $sql = "SELECT id_menu_item 
                        FROM `menus_itens` 
                        WHERE SUBSTRING(`nivel`, 1, 6) = '".substr($estrutura_inicial_nivel, 0, 6)."' 
                        AND `endereco` = '' 
                        AND `tipo` = '1' 
                        AND `id_menu_item` < '$id_sub_item_menu' ORDER BY id_menu_item DESC LIMIT 1 ";
                $campos         = bancos::sql($sql);
                $id_item_menu   = $campos[0]['id_menu_item'];
            }else if($sub_item != 000) {//Significa que esse "id_item_menu" é um sub_item ...
                $estrutura_inicial_nivel    = substr($campos[0]['nivel'], 0, 6);//Tanto o Menu e Item possui o início com essa numeração em comum ...
                $id_sub_item_menu           = $_GET['id_menu_item'];

                //Busca do "item" acima do "sub_item" ...
                $sql = "SELECT id_menu_item 
                        FROM `menus_itens` 
                        WHERE SUBSTRING(`nivel`, 1, 6) = '$estrutura_inicial_nivel' 
                        AND `endereco` = '' 
                        AND `id_menu_item` < '$id_sub_item_menu' ORDER BY id_menu_item DESC LIMIT 1 ";
                $campos         = bancos::sql($sql);
                $id_item_menu   = $campos[0]['id_menu_item'];
            }else if($item != 000) {//Significa que esse "id_item_menu" é um item ...
                $id_item_menu   = $_GET['id_menu_item'];//Aqui é o próprio "$_GET['id_menu_item']" que foi passado por parâmetro ...
            }
            /*********************Permissões de Item*********************/
            if(!empty($id_item_menu)) {//Permissão no "$id_item_menu" ...
                //Verifico se o Login selecionado já possui acesso no "$id_item_menu" ...
                $sql = "SELECT id_tipo_acesso 
                        FROM `tipos_acessos` 
                        WHERE `id_login` = '$_GET[id_login_current]' 
                        AND `id_modulo` = '$_GET[cmb_modulo]' 
                        AND `id_menu` = '$_GET[id_menu]' 
                        AND `id_menu_item` = '$id_item_menu' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//Não possui acesso ainda ...
                    $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, id_login, id_modulo, id_menu, id_menu_item) VALUES (NULL, '$_GET[id_login_current]', '$_GET[cmb_modulo]', '$_GET[id_menu]', '$id_item_menu') ";
                    bancos::sql($sql);
                }
            }
            /*******************Permissões de Sub-Item*******************/
            if(!empty($id_sub_item_menu)) {//Permissão no "$id_sub_item_menu" ...
                //Verifico se o Login selecionado já possui acesso no "$id_sub_item_menu" ...
                $sql = "SELECT id_tipo_acesso 
                        FROM `tipos_acessos` 
                        WHERE `id_login` = '$_GET[id_login_current]' 
                        AND `id_modulo` = '$_GET[cmb_modulo]' 
                        AND `id_menu` = '$_GET[id_menu]' 
                        AND `id_menu_item` = '$id_sub_item_menu' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//Não possui acesso ainda ...
                    $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, id_login, id_modulo, id_menu, id_menu_item) VALUES (NULL, '$_GET[id_login_current]', '$_GET[cmb_modulo]', '$_GET[id_menu]', '$id_sub_item_menu') ";
                    bancos::sql($sql);
                }
            }
            /*****************Permissões de Sub-Sub-Item*****************/
            if(!empty($id_sub_sub_item_menu)) {//Permissão no "$id_sub_sub_item_menu" ...
                //Verifico se o Login selecionado já possui acesso no "$id_sub_item_menu" ...
                $sql = "SELECT id_tipo_acesso 
                        FROM `tipos_acessos` 
                        WHERE `id_login` = '$_GET[id_login_current]' 
                        AND `id_modulo` = '$_GET[cmb_modulo]' 
                        AND `id_menu` = '$_GET[id_menu]' 
                        AND `id_menu_item` = '$id_sub_sub_item_menu' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) {//Não possui acesso ainda ...
                    $sql = "INSERT INTO `tipos_acessos` (`id_tipo_acesso`, id_login, id_modulo, id_menu, id_menu_item) VALUES (NULL, '$_GET[id_login_current]', '$_GET[cmb_modulo]', '$_GET[id_menu]', '$id_sub_sub_item_menu') ";
                    bancos::sql($sql);
                }
            }
        }
        /*Busco o nome de Login do Usuário que está sofrendo a Alteração, senão o Sistema interpreta que Login é o 
        do Usuário Logado ferrando aí o Administrador rsrs ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_GET[id_login_current]' LIMIT 1 ";
        $campos_login = bancos::sql($sql);
        /**************Quando se inclui uma Permissão, então tenho que recriar um Novo Cache**************/
        //Cada funcionário terá o seu respectivo menu ...
        $MenuCache = new Cache('menu_'.$campos_login[0]['login']);
        $MenuCache->Limpa_cache();
        /*************************************************************************************************/
        $valor = 1;
    }
    //Exclusão do "id_menu" ou "id_item_menu" p/ o Login que foi selecionado na combo ...
    if($_GET['excluir_pemissao'] == 1) {
        $sql = "DELETE 
                FROM `tipos_acessos` 
                WHERE `id_login` = '$_GET[id_login_current]' 
                AND `id_modulo` = '$_GET[cmb_modulo]' 
                AND (`id_menu` = '$_GET[id_menu]' OR `id_menu_item` = '$_GET[id_menu_item]') ";
        bancos::sql($sql);
        /*Busco o nome de Login do Usuário que está sofrendo a Alteração, senão o Sistema interpreta que Login é o 
        do Usuário Logado ferrando aí o Administrador rsrs ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_GET[id_login_current]' LIMIT 1 ";
        $campos_login = bancos::sql($sql);
        /**************Quando se exclui uma Permissão, então tenho que recriar um Novo Cache**************/
        //Cada funcionário terá o seu respectivo menu ...
        $MenuCache = new Cache('menu_'.$campos_login[0]['login']);
        $MenuCache->Limpa_cache();
        /*************************************************************************************************/
        $valor = 3;
    }
/*****************************************************************************************************/

//Procedimento normal de quando se carrega a Tela ...
    if(!empty($id_menu_item)) {
        $condicao = " AND ta.`id_menu_item` = '$id_menu_item' ";
    }else {
        $condicao = " AND ta.`id_menu` = '$id_menu' ";
    }
    $sql = "SELECT l.id_login, l.login, f.id_funcionario, f.nome, e.id_empresa, e.nomefantasia 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
            INNER JOIN `tipos_acessos` ta ON ta.`id_login` = l.`id_login` $condicao ORDER BY f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Funcionário(s) vs Menu(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Eu tenho que renomear essa variável para que não dar conflito com a variável id_login da Sessão ...
function excluir_permissao(id_login_current, id_menu, id_menu_item) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR A PERMISSÃO DESSE FUNCIONÁRIO ?')
    if(resposta == true) window.location = 'funcionario_menu.php?passo=1&excluir_pemissao=1&id_login_current='+id_login_current+'&id_menu='+id_menu+'&id_menu_item='+id_menu_item+'&cmb_modulo=<?=$cmb_modulo;?>'
}

//Eu tenho que renomear essa variável para que não dar conflito com a variável id_login da Sessão ...
function incluir_permissao(id_login_current, id_menu, id_menu_item) {
//Funcionário ...
    if(!combo('form', 'cmb_funcionarios_sem_permissao', '', 'SELECIONE UM FUNCIONÁRIO !')) {
        return false
    }
//Verificação ...
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA DAR PERMISSÃO PARA ESTE FUNCIONÁRIO ?')
    if(resposta == true) window.location = 'funcionario_menu.php?passo=1&incluir_permissao=1&id_login_current='+id_login_current+'&id_menu='+id_menu+'&id_menu_item='+id_menu_item+'&cmb_modulo=<?=$cmb_modulo;?>'
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Funcionário(s) vs Menu(s) 
            <font color='yellow'>
                <br/>Funcionários sem Permissão neste Menu =>
            </font> 
            <?
                for($i = 0; $i < $linhas; $i++) $id_logins_com_permissao.= $campos[$i]['id_login'].', ';
                $id_logins_com_permissao = substr($id_logins_com_permissao, 0, strlen($id_logins_com_permissao) - 2);
            ?>
            <select name='cmb_funcionarios_sem_permissao' title='Selecione um Funcionário sem Permissão' class='combo'>
            <?
                //Aqui eu listo todos os funcionários que não possuem permissão no Menu corrente ...
                $sql = "SELECT l.id_login, f.nome 
                        FROM `funcionarios` f 
                        INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` AND l.`id_login` NOT IN ($id_logins_com_permissao) 
                        WHERE f.`status` < '3' ORDER BY f.nome ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_incluir_permissao' value='Incluir Permissão' title='Incluir Permissão' onclick="incluir_permissao(document.form.cmb_funcionarios_sem_permissao.value, '<?=$id_menu;?>', '<?=$id_menu_item;?>')" class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
        <td>
            Login
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' title='Excluir Permissões' alt='Excluir Permissões'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align="left">
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['login'];?>
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' title='Excluir Permissão' alt='Excluir Permissão' style='cursor:pointer' onclick="excluir_permissao('<?=$campos[$i]['id_login'];?>', '<?=$id_menu;?>', '<?=$id_menu_item;?>')">
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
    require('../../../lib/menu/menu.php');
?>
<html>
<head>
<head>
<title>.:: Funcionário(s) vs Menu(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.cmb_modulo.value == '') {
        alert('SELECIONE O MÓDULO !')
        document.form.cmb_modulo.focus()
        return false
    }
    return true
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Funcionário(s) vs Menu(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='3'>
            M&oacute;dulo.:
            <select name='cmb_modulo' title='Selecione o Módulo' onchange="enviar('form', 'passo', '0')" class='combo'>
            <?
                $sql = "SELECT id_modulo, modulo 
                        FROM `modulos` 
                        ORDER BY modulo ";
                echo combos::combo($sql, $cmb_modulo);
            ?>
            </select>&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
<?
	if(!empty($cmb_modulo)) {
//TRAZ TODOS MENUS
            $sql = "SELECT * 
                    FROM `menus` 
                    WHERE `id_modulo` = '$cmb_modulo' ORDER BY menu ";
            $campos = bancos::sql($sql);
            for ($i = 0; $i < count($campos); $i++) {
//VERIFICA SE ESTAR CHECADO O MENU
			if($campos[$i]['endereco'] != '')  {
				$id_menu	= $campos[$i]['id_menu'];
				$item 		= $campos[$i]['menu'];
				$endereco 	= $campos[$i]['endereco'];
				if($endereco == '') {
                                    $link = '';
                                    $fechar_link = '';
				}else {
                                    $link = '<a href="';
                                    $url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&cmb_modulo='.$cmb_modulo.'"';
                                    $fim_link = ' class="html5lightbox">';
                                    $link = $link.$url.$fim_link;
                                    $fechar_link = "</a>";
				}
				echo '<tr class="linhanormal"><td colspan="3"><font color="#FF0000" size="10"><b>'.$link.$campos[$i]['menu'].$fechar_link.'</b></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
			}else {
//VERIFICAR SE ESTAR CHECADO O MENU  E FAZ SQL PARA BUSCAR O ITEN
				$sql = "Select id_tipo_acesso 
						from tipos_acessos 
						where id_modulo = '$cmb_modulo' 
						and id_login = '$cmb_login' 
						and id_menu = '$id_menu' limit 1 ";
				$campos_menu = bancos::sql($sql);
				echo '<tr class="linhanormal"><td colspan="3"><font color="#FF0000"><b>'.$campos[$i]['menu'].'</b></font></td></tr>'."\n";
//TRAZ TODOS ITENS DO MENU
				$sql = "Select * 
						from menus_itens 
						where id_menu = ".$campos[$i]['id_menu']." 
						and substring(nivel, 7, 6) = '000000' order by id_menu_item asc ";
				$campos2 = bancos::sql($sql);
				for ($y = 0; $y < count($campos2); $y ++) {
//VERIFICA SE ESTAR CHECADO O ITEM
					$sql = "Select * 
							from menus_itens 
							where id_menu = ".$campos[$i]['id_menu']. " 
							and substring(nivel, 7, 3)!='000' 
							and substring(nivel, 1, 6)= " . substr($campos2[$y]['nivel'], 0, 6). " 
							and substring(nivel, 10, 3) = '000' ";
					$campos3 = bancos::sql($sql);
					if(count($campos3) > 0) {
						$sql = "Select id_tipo_acesso 
								from tipos_acessos 
								where id_modulo = '$cmb_modulo' 
								and id_login = '$cmb_login' 
								and id_menu = ".$campos[$i]['id_menu']." 
								and id_menu_item = ".$campos2[$y]['id_menu_item']." limit 1 ";
						$campos_item  = bancos::sql($sql);
						$id_menu 		= $campos2[$y]['id_menu'];
						$item 			= $campos2[$y]['item'];
						$endereco 		= $campos2[$y]['endereco'];
						$id_menu_item 	= $campos2[$y]['id_menu_item'];
						if($endereco == '') {
							$link = '';
							$fechar_link = '';
						}else {
							$link = '<a href="';
							$url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&cmb_modulo='.$cmb_modulo.'"';
							$fim_link = ' class="html5lightbox">';
							$link = $link.$url.$fim_link;
							$fechar_link = "</a>";
						}
						echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="#0720FF" size="1">'.$link.$campos2[$y]['item'].$fechar_link.'</font></b></font></td></tr>'."\n";
					}else {
//VERIFICAR SE ESTAR CHECADO O ITEM E FAZ SQL PARA BUSCAR O SUB-ITEM
						$sql = "Select id_tipo_acesso 
								from tipos_acessos 
								where id_modulo = '$cmb_modulo' 
								and id_login = '$cmb_login' 
								and id_menu =".$campos[$i]['id_menu']." 
								and id_menu_item = ".$campos2[$y]['id_menu_item']." limit 1 ";
						$campos_item = bancos::sql($sql);
						$id_menu 		= $campos2[$y]['id_menu'];
						$item 			= $campos2[$y]['item'];
						$id_menu_item 	= $campos2[$y]['id_menu_item'];
						$endereco 		= $campos2[$y]['endereco'];
						if($endereco == '') {
							$link = '';
							$fechar_link = '';
						}else {
							$link = '<a href="';
							$url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&cmb_modulo='.$cmb_modulo.'"';
							$fim_link = ' class="html5lightbox">';
							$link = $link.$url.$fim_link;
							$fechar_link = "</a>";
						}
						echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="#0720FF">'.$link.$campos2[$y]['item'].$fechar_link.'</font></b></a></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
					}
//TRAZ TODOS SUB-ITENS DO MENU
					for ($k = 0; $k < count($campos3); $k++) {
//VERIFICAR SE ESTAR CHECADO O SUB-ITEM
						$sql = "Select * 
								from menus_itens 
								where id_menu = ".$campos[$i]['id_menu']." 
								and substring(nivel, 10, 3) != '000' 
								and substring(nivel, 1, 9) = ".substr($campos3[$k]['nivel'], 0, 9);
						$campos4 = bancos::sql($sql);
						if (count($campos4) > 0) {
							$id_menu		= $campos3[$k]['id_menu'];
							$item 			= $campos3[$k]['item'];
							$id_menu_item 	= $campos3[$k]['id_menu_item'];
							$endereco 		= $campos3[$k]['endereco'];
							if($endereco == '') {
								$link = '';
								$fechar_link = '';
							}else {
								$link = '<a href="';
								$url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&cmb_modulo='.$cmb_modulo.'"';
								$fim_link = ' class="html5lightbox">';
								$link = $link.$url.$fim_link;
								$fechar_link = "</a>";
							}
							echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="#006600">&nbsp;<b>'.$link.$campos3[$k]['item'].$fechar_link.'</b></font></td></tr>'."\n";
						}else {
//VERIFICAR SE ESTAR CHECADO O SUB-ITEM E FAZ SQL PARA BUSCAR O SUB-SUB-ITEM
							$sql = "Select id_tipo_acesso 
									from tipos_acessos 
									where id_modulo = '$cmb_modulo' 
									and id_login = '$cmb_login' 
									and id_menu =".$campos[$i]['id_menu']." 
									and id_menu_item = ".$campos3[$k]['id_menu_item']." limit 1 ";
							$campos_sub_item  = bancos::sql($sql);
							$id_menu		= $campos3[$k]['id_menu'];
							$item 			= $campos3[$k]['item'];
							$id_menu_item 	= $campos3[$k]['id_menu_item'];
							$endereco 		= $campos3[$k]['endereco'];
							if($endereco == '') {
								$link = '';
								$fechar_link = '';
							}else {
								$link = '<a href="';
								$url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&cmb_modulo='.$cmb_modulo.'"';
								$fim_link = ' class="html5lightbox">';
								$link = $link.$url.$fim_link;
								$fechar_link = "</a>";
							}
							echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="#006600">&nbsp;<b>'.$link.$campos3[$k]['item'].$fechar_link.'</b></font>&nbsp;&nbsp;&nbsp;</td></tr>'."\n";
						}
						for ($w = 0; $w < count($campos4); $w ++) {
//VERIFICAR SE ESTA CHECADO O SUB-SUB-ITEM
							$id_menu 		= $campos4[$w]['id_menu'];
							$item 			= $campos4[$w]['item'];
							$id_menu_item 	= $campos4[$w]['id_menu_item'];
							$endereco 		= $campos4[$w]['endereco'];
							if($endereco == '') {
								$link = '';
								$fechar_link = '';
							}else {
								$link = '<a href="';
								$url = 'funcionario_menu.php?passo=1&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&cmb_modulo='.$cmb_modulo.'"';
								$fim_link = ' class="html5lightbox">';
								$link = $link.$url.$fim_link;
								$fechar_link = "</a>";
							}
							echo '<tr class="linhanormal"><td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><font color="#010000">'.$link.$campos4[$w]['item'].$fechar_link.'</font></b></font></td></tr>'."\n";
						}
					}
				}
			}
		}
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>