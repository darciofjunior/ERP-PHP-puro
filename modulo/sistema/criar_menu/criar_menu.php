<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = '<font class="confirmacao">OPÇÃO DE MENU EXCLUÍDA COM SUCESSO.</font>';

//Procedimento normal de quando se carrega a Tela ...
$cmb_modulo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_modulo'] : $_GET['cmb_modulo'];

/*******************************************************************************************************************/
/***************************Exclusão de Módulos, Menus, Itens, Sub-Itens ou Sub-Sub-Itens***************************/
/*******************************************************************************************************************/
if(!empty($_GET['opt_opcao'])) {
    switch($_GET['opt_opcao']) {
        case 1://Deleta os menus e os itens atrelados
            //Exclui todos os Itens de Menu do menu passado por parâmetro ...
            $sql = "DELETE FROM `menus_itens` WHERE `id_menu` = '$_GET[id_menu]' ";
            bancos::sql($sql);
            //Exclui todos os Funcionários "Permissões" que estão pendurados no Menu passado por parâmetro ...
            $sql = "DELETE FROM `tipos_acessos` WHERE `id_menu` = '$_GET[id_menu]' ";
            bancos::sql($sql);
            //Exclui o menu passado por parâmetro ...
            $sql = "DELETE FROM `menus` WHERE `id_menu` = '$_GET[id_menu]' ";
            bancos::sql($sql);
        break;
        case 2://Deleta os Itens e os Sub-itens atrelados
            $sql = "DELETE FROM `menus_itens` WHERE SUBSTRING(`nivel`, 1, 6) = '$_GET[nivel_item]' AND `id_menu` = '$_GET[id_menu]' ";
            bancos::sql($sql);
            $sql = "DELETE FROM `menus_itens` WHERE `id_menu_item` = '$_GET[id_menu_item]' ";
            bancos::sql($sql);
        break;
        case 3://Deleta os Sub-Itens e os Sub-Sub-itens atrelados
            $sql = "DELETE FROM `menus_itens` WHERE SUBSTRING(`nivel`, 1, 9) = '$_GET[nivel_item]' AND `id_menu` = '$_GET[id_menu]'";
            bancos::sql($sql);
            $sql = "DELETE FROM `menus_itens` WHERE `id_menu_item` = '$_GET[id_menu_item]' ";
            bancos::sql($sql);
        break;
        case 4://Deleta os Sub-Sub Itens
            $sql = "DELETE FROM `menus_itens` WHERE `id_menu_item` = '$_GET[id_menu_item]' ";
            bancos::sql($sql);
        break;
        default://Deleta os Módulos e todos os Menus
            $sql = "SELECT id_menu 
                    FROM `menus` 
                    WHERE `id_modulo` = '$_GET[cmb_modulo]' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) {
                $sql = "DELETE FROM `menus_itens` WHERE `id_menu` = '".$campos[$i]['id_menu']."' ";
                bancos::sql($sql);
            }
            //Exclui todos os Funcionários "Permissões" que estão pendurados no Módulo passado por parâmetro ...
            $sql = "DELETE FROM `tipos_acessos` WHERE `id_modulo` = '$_GET[cmb_modulo]' ";
            bancos::sql($sql);
            $sql = "DELETE FROM `menus` WHERE `id_modulo` = '$_GET[cmb_modulo]' ";
            bancos::sql($sql);
            $sql = "DELETE FROM `modulos` WHERE `id_modulo` = '$_GET[cmb_modulo]' ";
            bancos::sql($sql);
        break;
    }
    $valor = 1;
}
/*******************************************************************************************************************/
?>
<html>
<head>
<title>.:: Gerar Menu ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if (document.form.cmb_modulo.value == '') {
        alert('SELECIONE O MÓDULO !')
        document.form.cmb_modulo.focus()
        return false
    }
}

function excluir_modulo() {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE MÓDULO ?')
    if(resposta == true) window.location = 'criar_menu.php?opt_opcao=5&cmb_modulo=<?=$_POST['cmb_modulo'];?>'
}
</script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Gerar Menu
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='3'>
            Módulo.: 
            <select name='cmb_modulo' onchange='document.form.submit()' class='combo'>
            <?
                $sql = "SELECT id_modulo, modulo 
                        FROM `modulos` 
                        ORDER BY modulo ";
                if(empty($cmb_modulo)) {
                    echo combos::combo($sql);
                    $desabilitar = 'disabled';
                }else {
                    echo combos::combo($sql, $cmb_modulo);
                    $desabilitar = '';
                }
            ?>
            </select>
            &nbsp;&nbsp;&nbsp; 
            <input type='button' name='cmd_gerar_modulo' value='Gerar Módulo' title='Gerar Módulo' onclick="html5Lightbox.showLightbox(7, 'gerar_modulo.php')" class='botao'>
            &nbsp;&nbsp;&nbsp;
<!--Tem que passar o nome do Parâmetro como id_mod, para não dar conflito com a variável id_modulo da sessão-->
            <input type='button' name='cmd_alterar_modulo' value='Alterar Módulo' title='Alterar Módulo' onclick="html5Lightbox.showLightbox(7, 'alterar_modulo.php?id_mod='+document.form.cmb_modulo.value)" <?=$desabilitar;?> class='botao'>
            &nbsp;&nbsp;&nbsp;
            <input type='button' name='cmd_criar_menu' value='Gerar Menu' title='Gerar Menu' onclick="html5Lightbox.showLightbox(7, 'gerar_menu.php?cod_modulo=<?=$cmb_modulo;?>')" <?=$desabilitar;?> class='botao'>
            &nbsp;&nbsp;&nbsp; 
            <input type='button' name='cmd_excluir_modulo' value='Excluir Módulo' title='Excluir Módulo' onclick="return excluir_modulo()" <?=$desabilitar;?> class='botao'>
        </td>
    </tr>
<?
    if(!empty($cmb_modulo)) {//Traz todos os Menus do Módulo selecionado pelo Usuário ...
?>
    <tr class='linhanormaldestaque'>
        <td colspan='3' align='center'>
            <img src='../../../imagem/bloco_vermelho.gif' width='8' height='8' border='0'>
            <font color='#FF0000'>
                Menu&nbsp;&nbsp;
            </font>
            <img src='../../../imagem/bloco_azul.gif' width='8' height='8' border='0'>
            <font color='#0000FF'>
                Item de Menu&nbsp;&nbsp;
            </font>
            <img src='../../../imagem/bloco_verde.gif' width='8' height='8' border='0'>
            <font color='#006600'>
                Sub Item do Item&nbsp;&nbsp;
            </font>
            <img src='../../../imagem/bloco_negro.gif' width='8' height='8' border='0'>
            <font color='#000000'>
                Sub do Sub Item
            </font>
        </td>
    </tr>
<?
        //Aqui traz todos os Menus cadastrados do Módulo selecionado pelo Usuário ...
        $sql = "SELECT * 
                FROM `menus` 
                WHERE `id_modulo` = '$cmb_modulo' ORDER BY menu ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for ($i = 0; $i < $linhas; $i++) {
            //Verifica se o Menu já possui Endereço direto ...
            if($campos[$i]['endereco'] != '') {//Possui endereço direto, então não possui Itens, Sub-Itens, etc ...
                $endereco   = $campos[$i]['endereco'];
                $item       = $campos[$i]['menu'];
                $id_menu    = $campos[$i]['id_menu'];
                if($endereco == '') {
                    $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a  href="';
                    $url = 'gerar_item.php?cod_modulo='.$cmb_modulo.'&id_menu='.$id_menu;
                    $fim_link = '" class="html5lightbox"><font  color="#FF0000">';
                    $link = $link.$url.$fim_link;
                    $fechar_link = "</a></font>";
                }else {
                    $link = '';
                    $fechar_link = '';
                }
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <font color='#FF0000'>
                <b><?=$link.$campos[$i]['menu'].$fechar_link;?></b>
            </font>
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' alt='Alterar Menu' style='cursor:hand' onclick="html5Lightbox.showLightbox(7, 'alterar_menu.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>')" width='20' height='20'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt='Excluir Menu' style='cursor:hand' width='20' height='20' onclick="window.location = 'criar_menu.php?opt_opcao=1&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>'">
        </td>
    </tr>
	<?
            }else {//Não possui endereço direto, então pode possuir Itens, Sub-Itens, etc ...
                $id_menu        = $campos[$i]['id_menu'];
                $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a  href="';
                $url = 'gerar_item.php?cod_modulo='.$cmb_modulo.'&id_menu='.$id_menu;
                $fim_link = '" class="html5lightbox"><font color="#FF0000" >';
                $link = $link.$url.$fim_link;
                $fechar_link = "</font></a>";
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <font color='#FF0000'>
                <b><?=$link.$campos[$i]['menu'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <td>
            <img src='../../../imagem/menu/alterar.png' alt='Alterar Menu' style='cursor:hand' onclick="html5Lightbox.showLightbox(7, 'alterar_menu.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>')" width='20' height='20'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt='Excluir Menu' style='cursor:hand' width='20' height='20' onclick="window.location = 'criar_menu.php?opt_opcao=1&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>'">
        </td>
    </tr>
	<?
                //Trago todos os Itens do Menu do Loop ...
                $sql = "SELECT * 
                        FROM `menus_itens` 
                        WHERE `id_menu` = ".$campos[$i]['id_menu']." 
                        AND SUBSTRING(`nivel`, 7, 6) = '000000' ORDER BY id_menu_item ";
                $campos_item = bancos::sql($sql);
                $linhas_item = count($campos_item);
                for($j = 0; $j < $linhas_item; $j++) {
                    //Trago todos os Sub-Itens do Item de Menu do Loop ...
                    $sql = "SELECT * 
                            FROM `menus_itens` 
                            WHERE `id_menu` = '".$campos[$i]['id_menu']."' 
                            AND SUBSTRING(`nivel`, 7, 3) !='000' 
                            AND SUBSTRING(`nivel`, 1, 6) = ".substr($campos_item[$j]['nivel'], 0, 6)." 
                            AND SUBSTRING(`nivel`, 10, 3) = '000' ";
                    $campos_sub_item = bancos::sql($sql);
                    $linhas_sub_item = count($campos_sub_item);
                    if(count($linhas_sub_item) > 0) {//Significa que existe pelo menos 1 Sub-Item p/ o Item do Loop ...
                        $item           = $campos_item[$j]['item'];
                        $endereco       = $campos_item[$j]['endereco'];
                        $id_menu_item   = $campos_item[$j]['id_menu_item'];
                        $nivel_item     = substr($campos_item[$j]['nivel'], 0, 6);
                        if($endereco == '') {
                            $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a href="';
                            $url = 'gerar_sub_item.php?cod_modulo='.$cmb_modulo.'&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&nivel_item='.$nivel_item;
                            $fim_link = '" class="html5lightbox"><font color="#0000FF">';
                            $link = $link.$url.$fim_link;
                            $fechar_link = "</a></font>";
                        }else {
                            $link = '';
                            $fechar_link = '';
                        }
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0720FF'>
                <b><?=$link.$campos_item[$j]['item'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' alt='Alterar Item' width='20' height='20' onclick="html5Lightbox.showLightbox(7, 'alterar_item.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>')" style='cursor:hand'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt='Excluir Item' width='20' height='20' style='cursor:hand' onclick="window.location = 'criar_menu.php?opt_opcao=2&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>&nivel_item=<?=$nivel_item;?>'">
        </td>
    </tr>
        <?
                    }else {//Não existe nenhum 1 Sub-Item p/ o Item do Loop ...
                        $item           = $campos_item[$j]['item'];
                        $id_menu_item   = $campos_item[$j]['id_menu_item'];
                        $endereco       = $campos_item[$j]['endereco'];
                        $nivel_item     = substr($campos_item[$j]['nivel'], 0, 6);
                        if($endereco == '') {
                            $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a href="';
                            $url = 'gerar_sub_item.php?cod_modulo='.$cmb_modulo.'&id_menu='.$id_menu.'&nivel_item='.$nivel_item;
                            $fim_link = '" class="html5lightbox"><font color="#0000FF">';
                            $link = $link.$url.$fim_link;
                            $fechar_link = "</a></font>";
                        }else {
                            $link = '';
                            $fechar_link = '';
                        }
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0720FF'>
                <b><?=$link.$campos_item[$j]['item'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' alt='Alterar Item' width='20' height='20' onclick="html5Lightbox.showLightbox(7, 'alterar_item.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>')" style='cursor:hand'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt='Excluir Item' width='20' height='20' style='cursor:hand' onclick="window.location = 'criar_menu.php?opt_opcao=2&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>&nivel_item=<?=$nivel_item;?>'">
        </td>
    </tr>
        <?
                    }
                    //Lista todos os Sub-Item(ns) do Item do Menu do Loop ...
                    for($z = 0; $z < $linhas_sub_item; $z++) {
                        //Trago todos os Sub-Sub-Itens do Sub-Item de Menu do Loop ...
                        $sql = "SELECT * 
                                FROM `menus_itens` 
                                WHERE `id_menu` = ".$campos[$i]['id_menu']." 
                                AND SUBSTRING(`nivel`, 10, 3) != '000' 
                                AND SUBSTRING(`nivel`, 1, 9) = ".substr($campos_sub_item[$z]['nivel'], 0, 9);
                        $campos_sub_sub_item = bancos::sql($sql);
                        $linhas_sub_sub_item = count($campos_sub_sub_item);
                        if($linhas_sub_sub_item > 0) {//Significa que existe pelo menos 1 Sub-Sub-Item p/ o Sub-Item do Loop ...
                            $endereco       = $campos_sub_item[$z]['endereco'];
                            $id_menu_item   = $campos_sub_item[$z]['id_menu_item'];
                            $nivel_item     = substr($campos_sub_item[$z]['nivel'], 0, 9);
                            $item           = $campos_sub_item[$z]['item'];
                            if($endereco == '') {
                                $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a  href="';
                                $url = 'gerar_sub_sub_item.php?cod_modulo='.$cmb_modulo.'&nivel_item='.$nivel_item.'&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&nivel_item='.$nivel_item;
                                $fim_link = '" class="html5lightbox"><font color="#006600">';
                                $link = $link.$url.$fim_link;
                                $fechar_link = "</font></a>";
                            }else {
                                $link = '';
                                $fechar_link = '';
                            }
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#006600'>
                &nbsp;
                <b><?=$link.$campos_sub_item[$z]['item'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' alt='Alterar Sub-Item' width='20' height='20' onclick="html5Lightbox.showLightbox(7, 'alterar_sub_item.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>')" style='cursor:hand'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt="Excluir Sub-Item" width='20' height='20' style='cursor:hand' onclick="window.location = 'criar_menu.php?opt_opcao=3&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>&nivel_item=<?=$nivel_item;?>'">
        </td>
    </tr>
	<?
                        }else {//Não existe nenhum 1 Sub-Sub-Item p/ o Sub-Item do Loop ...
                            $endereco       = $campos_sub_item[$z]['endereco'];
                            $item           = $campos_sub_item[$z]['item'];
                            $nivel_item     = substr($campos_sub_item[$z]['nivel'], 0, 9);
                            $id_menu_item   = $campos_sub_item[$z]['id_menu_item'];
                            if($endereco == '') {
                                $link = '<img src = "../../../imagem/seta_direita.gif" width=12 height=12 border=0>&nbsp;<a class="link" href="';
                                $url = 'gerar_sub_sub_item.php?cod_modulo='.$cmb_modulo.'&id_menu='.$id_menu.'&id_menu_item='.$id_menu_item.'&nivel_item='.$nivel_item;
                                $fim_link = '" class="html5lightbox"><font color="#006600">';
                                $link = $link.$url.$fim_link;
                                $fechar_link = "</a></font>";
                            }else {
                                $link = '';
                                $fechar_link = '';
                            }
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#006600'>
                &nbsp;
                <b><?=$link.$campos_sub_item[$z]['item'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' alt='Alterar Sub-Item' width='20' height='20' onclick="html5Lightbox.showLightbox(7, 'alterar_sub_item.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>')" style='cursor:hand'>
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt='Excluir Sub-Item' width='20' height='20' style='cursor:hand' onclick="window.location = 'criar_menu.php?opt_opcao=3&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>&nivel_item=<?=$nivel_item;?>'">
        </td>
    </tr>
	<?
                        }
                        for($a = 0; $a < $linhas_sub_sub_item; $a++) {
                            $endereco       = $campos_sub_sub_item[$a]['endereco'];
                            $item           = $campos_sub_sub_item[$a]['item'];
                            $id_menu_item   = $campos_sub_sub_item[$a]['id_menu_item'];
                            $link           = '';
                            $fechar_link    = '';
	?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#010000'>
                <b><?=$link.$campos_sub_sub_item[$a]['item'].$fechar_link;?></b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' alt="Alterar Sub-Item" width='20' height='20' style='cursor:hand' onclick="html5Lightbox.showLightbox(7, 'alterar_sub_sub_item.php?cod_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>')">
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' alt="Excluir Sub-Item" width='20' height='20' style='cursor:hand' onclick="window.location = 'criar_menu.php?opt_opcao=4&cmb_modulo=<?=$cmb_modulo;?>&id_menu=<?=$id_menu;?>&id_menu_item=<?=$id_menu_item;?>'">
        </td>
    </tr>
        <?
                        }//Fim do FOR da Listagem dos Sub-Sub-Itens do Sub-Item do Loop ...
                    }//Fim do FOR da Listagem dos Sub-Itens do Item do Loop ...
                }//Fim do FOR da Listagem dos Itens do Menus do Loop ...
            }//Fim do IF dos Menus que não possuem endereço direto, sendo assim podem possuir Itens, Sub-Itens, etc ...
        }//Fim do FOR da Listagem dos Menus do Módulos ...
    }//Fim do Módulo selecionado ...
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