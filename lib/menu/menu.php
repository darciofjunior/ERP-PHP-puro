<?
/*Essa Biblioteca "Cache" é chamada dentro do Mural, e como o Mural chama esse arquivo 'menu.php' 
que por sua vez também chama essa Biblioteca "Cache", faço esse tratamento abaixo p/ que não dê problema 
de redeclare por causa do require ...*/
//if(!class_exists('Cache')) require('/var/www/erp/albafer/lib/cache/Cache.class.php');
session_start('funcionarios');

//Cada funcionário terá o seu respectivo menu ...
//$MenuCache = new Cache('menu_'.$_SESSION[login]);//A cada 5 minutos, recrio o Menu do Usuário ...
//if($MenuCache->Start_cache()) {//A partir daqui envolvo o conteúdo que eu quero que fique no Cache ...
    //Busca da Empresa, Login e Módulo que o usuário se encontra logado ...
    $sql = "SELECT e.`nomefantasia`, l.`login`, m.`modulo` 
            FROM `empresas` e 
            INNER JOIN `logins` l ON l.`id_login` = '$_SESSION[id_login]' 
            INNER JOIN `modulos` m ON m.`id_modulo` = '$_SESSION[id_modulo]' 
            WHERE e.`id_empresa` = '$_SESSION[id_empresa]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
<head>
<title>.:: GRUPO ALBAFER (ERP) - Enterprise Resource Planning ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'/>
<meta http-equiv = 'cache-control' content = 'yes-store'/>
<meta http-equiv = 'pragma' content = 'yes-cache'/>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=UTF-8'/>
<!--<meta http-equiv="X-UA-Compatible" content="IE=8"/><!--Essa Tag é p/ que o Sistema interprete o Menu no IE 10-->
<link rel='stylesheet' type='text/css' href='/erp/albafer/lib/menu/menu.css'/>
<Script Language = 'JavaScript' Src = '/erp/albafer/lib/menu/menu.js'></Script>
<Script Language = 'JavaScript' Src = '/erp/albafer/js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '/erp/albafer/js/jquery.js'></Script>
<Script Language = 'JavaScript'>
$(document).ready(function() {
    var y_fixo = $('#menu').offset().top;
    $(window).scroll(function () {
        $('#menu').animate({
            top: y_fixo+$(document).scrollTop()+'px'
        },{duration:0,queue:false});
    });
});
</Script>
</head>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml2/DTD/xhtml1-strict.dtd">
<div id='menu' style='position: absolute; right:0px; left:0px;'>
<body leftmargin='0' rightmargin='0' topmargin='0'>
<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' valign='center' align='center'>
        <td width='17%'>
            <font size='2'>
                <b>Empresa:</b> <?=$campos[0]['nomefantasia'];?>
            </font>
        </td>
        <td width='17%'>
            <font size='2'>
                <b>Módulo</b> <?=$campos[0]['modulo'];?>
            </font>
        </td>
        <td width='17%'>
            <font color='#CCCCCC' size='2'>
                <b>Login: </b><?=$campos[0]['login'].' - '.$_SERVER['REMOTE_ADDR'];?>
            </font>
        </td>
        <td width='17%'>
            <font size='2'>
                <b>Data: </b><?=date('d/m/Y');?>
            </font>
        </td>
        <td width='32%'>
            <iframe src='/erp/albafer/lib/menu/relogio_sessao.php?renovar_sessao=S' name='relogio_sessao' id='relogio_sessao' width='105' height='30' frameborder='0' scrolling='no'></iframe>
            <a href = "javascript:nova_janela('/erp/albafer/mural/telefonia/telefonia.php', 'TELEFONIA', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src="/erp/albafer/imagem/icones2/27.gif" title="Telefonia (Ramais)" style='cursor:help' border='0'>&nbsp;
            </a>
            &nbsp;
            <a href = "javascript:nova_janela('/erp/albafer/mural/emails/emails.php', 'LISTA_EMAILS', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src="/erp/albafer/imagem/icones2/36.gif" title="Lista de E-mail(s) Albafer" style='cursor:help' border='0'>&nbsp;
            </a>
            &nbsp;
            <a href = '/erp/albafer/mural/mural.php' target='_parent' class='top_link'>
                <img src='/erp/albafer/imagem/mural.gif'>
            </a>
            &nbsp;
            <a href = '/erp/albafer/logoff.php?passo=1' target='_parent' class='top_link'>
                <img src='/erp/albafer/imagem/sair.gif'>
            </a>
        </td>
    </tr>
</table>
<ul id='nav'><!--Configuração Inicial do Menu, puxa de dentro do arquivo CSS-->
<?
        //Se a Sessão já caiu, então eu deslogo o usuário do Sistema ...
        if(!isset($_SESSION['ultimo_acesso'])) {
            echo "<Script Language='JavaScript'>
                        alert('SESSÃO EXPIRADA POR FALTA DE USO !!! FAVOR LOGAR NOVAMENTE !')
                        window.top.parent.location = 'http://".$_SERVER['SERVER_ADDR']."/erp/albafer/default.php?deslogar=s&valor=3&largura='+screen.width
                  </Script>";
        }

	if($_SESSION['id_modulo'] == 18) {//Significa que o usuário optou em querer os Menus pelos módulos do Sistema ...
		/***********************************************************************************/
		/**************************************Módulos**************************************/
		/***********************************************************************************/
		$sql = "SELECT distinct(m.id_modulo), m.modulo 
				FROM `modulos` m 
				INNER JOIN `tipos_acessos` ta ON ta.id_modulo = m.id_modulo 
				INNER JOIN `logins` l ON l.id_login = ta.id_login AND l.id_login = '$_SESSION[id_login]' 
				WHERE m.`id_modulo`<> '18' 
				GROUP BY m.id_modulo ORDER BY m.modulo ";
		$campos_modulos = bancos::sql($sql);
		$linhas_modulos = count($campos_modulos);
		for ($i = 0; $i < $linhas_modulos; $i++) {
?>
	<li class="top"><a href="#" class="top_link"><span>&nbsp;&nbsp;<?=ucfirst($campos_modulos[$i]['modulo']);?></span></a>
<?
	/*********************************************************************************/
	/**************************************Menus**************************************/
	/*********************************************************************************/
			$sql = "SELECT m.* 
					FROM `menus` m 
					INNER JOIN `tipos_acessos` ta ON ta.id_menu = m.id_menu AND ta.id_menu_item = '0' AND ta.id_login = '$_SESSION[id_login]' 
					WHERE m.id_modulo = '".$campos_modulos[$i]['id_modulo']."' ORDER BY m.menu ";
		    $campos_menus 	= bancos::sql($sql);
		    $linhas_menus	= count($campos_menus);
		    if($linhas_menus > 0) {
		    	echo '<ul class="sub">';//Início de Menu ...
		    	for ($j = 0; $j < $linhas_menus; $j++) {
					if($campos_menus[$j]['endereco'] != '') {//Aqui eu exibo os Menus que possuem Itens diretamente ...
?>
			<li><a href="<?=$niveis_url.$campos_menus[$j]['endereco'].$campos_menus[$j]['parametro'];?>" target="_parent"><?=$campos_menus[$j]['menu'];?></a></li>
<?
			}else {//Aqui eu exibo os menus com item, Sub-item e Sub-sub-item ...
?>
			<li><a href="#" class="fly"><?=$campos_menus[$j]['menu'];?></a><!--Início do Marcador de Item de Menu ...-->
<?				
						/*********************************************************************************/
						/**********************************Itens de Menus*********************************/
						/*********************************************************************************/		
						$sql = "SELECT mi.* 
								FROM `menus_itens` mi 
								INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND ta.id_menu_item = mi.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
								WHERE ta.`id_menu` = '".$campos_menus[$j]['id_menu']."' 
								AND SUBSTRING(mi.nivel, 7, 6) = '000000' ORDER BY mi.item ";
						$campos_itens_menus = bancos::sql($sql);
		    			$linhas_itens_menus	= count($campos_itens_menus);
		    			if($linhas_itens_menus > 0) {
		    				echo '<ul>';//Início de Item de Menu ...
		    				for ($k = 0; $k < $linhas_itens_menus; $k++) {
			    				/*************************************************************************************/
								/**********************************Sub-Itens de Menus*********************************/
								/*************************************************************************************/
			    				$sql = "SELECT mi.* 
			    						FROM `menus_itens` mi 
			    						INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND mi.id_menu_item = ta.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
			    						WHERE mi.id_menu = '".$campos_menus[$j]['id_menu']."' 
			    						AND SUBSTRING(mi.nivel, 7, 3) != '000' 
			    						AND SUBSTRING(mi.nivel, 1, 6) = '".substr($campos_itens_menus[$k]['nivel'], 0, 6)."' 
			    						AND SUBSTRING(mi.nivel, 10, 3) = '000' ORDER BY mi.item ";
			    				$campos_sub_itens_menus = bancos::sql($sql);
		    					$linhas_sub_itens_menus	= count($campos_sub_itens_menus);
		    					if($linhas_sub_itens_menus > 0) {//Existem sub-Itens de Menu, sendo assim eu não coloco Link na URL para abrir ...
?>
					<li><a href="#" class="fly"><?=$campos_itens_menus[$k]['item'];?></a><!--Início do Marcador de Sub-Item de Menu ...-->
<?
									echo '<ul>';//Início de Sub-Item de Menu ...
									for ($l = 0; $l < $linhas_sub_itens_menus; $l++) {
				    					/*************************************************************************************/
										/*********************************SubSubItens de Menus********************************/
										/*************************************************************************************/
			    						$sql = "SELECT mi.* 
			    								FROM `menus_itens` mi 
			    								INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND ta.id_menu_item  = mi.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
			    								WHERE mi.id_menu = '".$campos_menus[$j]['id_menu']."' 
			    								AND SUBSTRING(mi.nivel, 10, 3) != '000' 
			    								AND SUBSTRING(mi.nivel, 1, 9) = '".substr($campos_sub_itens_menus[$l]['nivel'], 0, 9)."' ORDER BY mi.item "; 
			                   	        $campos_sub_sub_itens_menus = bancos::sql($sql);
			    						$linhas_sub_sub_itens_menus	= count($campos_sub_sub_itens_menus);
			    						if($linhas_sub_sub_itens_menus > 0) {//Existem sub-sub-Itens de Menu, sendo assim eu não coloco Link na URL para abrir ...
?>
			<li><a href="#" class="fly"><?=$campos_sub_itens_menus[$l]['item'];?></a><!--Início do Marcador de Sub-Sub-Item de Menu ...-->
<?
											echo '<ul>';//Início de Sub-Sub-Item de Menu ...
											for ($m = 0; $m < $linhas_sub_sub_itens_menus; $m++) {
?>
			<li><a href="<?=$niveis_url.$campos_sub_sub_itens_menus[$m]['endereco'].$campos_sub_sub_itens_menus[$m]['parametro'];?>" target="_parent"><?=$campos_sub_sub_itens_menus[$m]['item'];?></a></li>
<?
	 										}
    										echo '</ul>';//Fim de Sub-Sub-Item de Menu ...
    									}else {//Não existem sub-sub-itens de Menu, sendo assim coloco Link na URL para abrir ...
?>
			<li><a href="<?=$niveis_url.$campos_sub_itens_menus[$l]['endereco'].$campos_sub_itens_menus[$l]['parametro'];?>" target="_parent"><?=$campos_sub_itens_menus[$l]['item'];?></a></li>
<?
    									}
    								}
		    						echo '</li>';//Fim do Marcador de Sub-Item de Menu ... 
									echo '</ul>';//Fim de Sub-Item de Menu ...
    							}else {//Não existem sub-itens, sendo assim coloco Link na URL para abrir ...
?>
			<li><a href="<?=$niveis_url.$campos_itens_menus[$k]['endereco'].$campos_itens_menus[$k]['parametro'];?>" target="_parent"><?=$campos_itens_menus[$k]['item'];?></a></li>
<?
    							}
    						}
    						echo '</ul>';//Fim de Item de Menu ...
    					}
    					echo '</li>';//Fim do Marcador de Item de Menu ...
					}
				}
				echo '</ul>';//Fim de Menu ...
			}
			echo '</li>';//Fim de Módulo ...
		}
	}else {//Significa que o usuário optou em querer os Menus pelos Itens do Módulo ...
                /*********************************************************************************/
		/**************************************Menus**************************************/
		/*********************************************************************************/
		$sql = "SELECT m.* 
				FROM `menus` m 
				INNER JOIN `tipos_acessos` ta ON ta.id_menu = m.id_menu AND ta.id_menu_item = '0' AND ta.id_login = '$_SESSION[id_login]' 
				WHERE m.id_modulo = '$_SESSION[id_modulo]' ORDER BY m.menu ";
	    $campos_menus 	= bancos::sql($sql);
	    $linhas_menus	= count($campos_menus);
	    if($linhas_menus > 0) {
	    	for ($j = 0; $j < $linhas_menus; $j++) {
				if($campos_menus[$j]['endereco'] != '') {//Aqui eu exibo os Menus que possuem Itens diretamente ...
?>
			<li class="top"><a href="<?=$niveis_url.$campos_menus[$j]['endereco'].$campos_menus[$j]['parametro'];?>" target="_parent" class="top_link"><span>&nbsp;&nbsp;<?=ucfirst($campos_menus[$j]['menu']);?></span></a></li>
<?
				}else {//Aqui eu exibo os menus com item, Sub-item e Sub-sub-item ...
?>
			<li class="top"><a href="#" class="top_link"><span>&nbsp;&nbsp;<?=ucfirst($campos_menus[$j]['menu']);?></span></a><!--Início do Marcador de Item de Menu ...-->
<?				
					/*********************************************************************************/
					/**********************************Itens de Menus*********************************/
					/*********************************************************************************/		
					$sql = "SELECT mi.* 
							FROM `menus_itens` mi 
							INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND ta.id_menu_item = mi.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
							WHERE ta.`id_menu` = '".$campos_menus[$j]['id_menu']."' 
							AND SUBSTRING(mi.nivel, 7, 6) = '000000' ORDER BY mi.item ";
					$campos_itens_menus = bancos::sql($sql);
	    			$linhas_itens_menus	= count($campos_itens_menus);
	    			if($linhas_itens_menus > 0) {
	    				echo '<ul class="sub">';//Início de Item de Menu ...
	    				for ($k = 0; $k < $linhas_itens_menus; $k++) {
		    				/*************************************************************************************/
							/**********************************Sub-Itens de Menus*********************************/
							/*************************************************************************************/
		    				$sql = "SELECT mi.* 
		    						FROM `menus_itens` mi 
		    						INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND mi.id_menu_item = ta.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
		    						WHERE mi.id_menu = '".$campos_menus[$j]['id_menu']."' 
		    						AND SUBSTRING(mi.nivel, 7, 3) != '000' 
		    						AND SUBSTRING(mi.nivel, 1, 6) = '".substr($campos_itens_menus[$k]['nivel'], 0, 6)."' 
		    						AND SUBSTRING(mi.nivel, 10, 3) = '000' ORDER BY mi.item ";
		    				$campos_sub_itens_menus = bancos::sql($sql);
	    					$linhas_sub_itens_menus	= count($campos_sub_itens_menus);
	    					if($linhas_sub_itens_menus > 0) {//Existem sub-Itens de Menu, sendo assim eu não coloco Link na URL para abrir ...
?>
					<li><a href="#" class="fly"><?=$campos_itens_menus[$k]['item'];?></a><!--Início do Marcador de Sub-Item de Menu ...-->
<?
								echo '<ul>';//Início de Sub-Item de Menu ...
								for ($l = 0; $l < $linhas_sub_itens_menus; $l++) {
			    					/*************************************************************************************/
									/*********************************SubSubItens de Menus********************************/
									/*************************************************************************************/
		    						$sql = "SELECT mi.* 
		    								FROM `menus_itens` mi 
		    								INNER JOIN `tipos_acessos` ta ON ta.id_menu = mi.id_menu AND ta.id_menu_item  = mi.id_menu_item AND ta.id_login = '$_SESSION[id_login]' 
		    								WHERE mi.id_menu = '".$campos_menus[$j]['id_menu']."' 
		    								AND SUBSTRING(mi.nivel, 10, 3) != '000' 
		    								AND SUBSTRING(mi.nivel, 1, 9) = '".substr($campos_sub_itens_menus[$l]['nivel'], 0, 9)."' ORDER BY mi.item "; 
		                   	        $campos_sub_sub_itens_menus = bancos::sql($sql);
		    						$linhas_sub_sub_itens_menus	= count($campos_sub_sub_itens_menus);
		    						if($linhas_sub_sub_itens_menus > 0) {//Existem sub-sub-Itens de Menu, sendo assim eu não coloco Link na URL para abrir ...
?>
			<li><a href="#" class="fly"><?=$campos_sub_itens_menus[$l]['item'];?></a><!--Início do Marcador de Sub-Sub-Item de Menu ...-->
<?
										echo '<ul>';//Início de Sub-Sub-Item de Menu ...
										for ($m = 0; $m < $linhas_sub_sub_itens_menus; $m++) {
?>
			<li><a href="<?=$niveis_url.$campos_sub_sub_itens_menus[$m]['endereco'].$campos_sub_sub_itens_menus[$m]['parametro'];?>" target="_parent"><?=$campos_sub_sub_itens_menus[$m]['item'];?></a></li>
<?
	 									}
    									echo '</ul>';//Fim de Sub-Sub-Item de Menu ...
									}else {//Não existem sub-sub-itens de Menu, sendo assim coloco Link na URL para abrir ...
?>
			<li><a href="<?=$niveis_url.$campos_sub_itens_menus[$l]['endereco'].$campos_sub_itens_menus[$l]['parametro'];?>" target="_parent"><?=$campos_sub_itens_menus[$l]['item'];?></a></li>
<?
    								}
								}
		    					echo '</li>';//Fim do Marcador de Sub-Item de Menu ... 
								echo '</ul>';//Fim de Sub-Item de Menu ...
    						}else {//Não existem sub-itens, sendo assim coloco Link na URL para abrir ...
?>
			<li><a href="<?=$niveis_url.$campos_itens_menus[$k]['endereco'].$campos_itens_menus[$k]['parametro'];?>" target="_parent"><?=$campos_itens_menus[$k]['item'];?></a></li>
<?
    						}
						}
    					echo '</ul>';//Fim de Item de Menu ...
					}
    				echo '</li>';//Fim do Marcador de Item de Menu ...
				}
			}
		}
	}
?>

    <li class='top'>
        
    </li>
    <li class='top'>
        
    </li>
</ul><!--Fim da Configuração Inicial do Menu, puxa de dentro do arquivo CSS-->
</body>
</div>
<br><br><br><br><br><!--Arranjo Técnico Master-->
</html>
<?
    //$MenuCache->End_cache();//Finaliza o Cache ...
//}
?>