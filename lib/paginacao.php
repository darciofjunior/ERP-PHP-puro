<?
class paginacao extends bancos {
    function parametros() {
        if(empty($parametros) or !isset($parametros)) {
            if($GLOBALS['REQUEST_METHOD'] == 'GET') {//passagem por parametro
                global $HTTP_GET_VARS;
                while(list($nome)=each($HTTP_GET_VARS)) {
                    if (is_array($HTTP_GET_VARS[$nome])) {
                        foreach ($HTTP_GET_VARS[$nome] as $dados) {
                            //Mudan�a feita no dia 15/06/2015 ...
                            if(strpos($parametros, $nome) == 0) $parametros.= '&'.$nome.'[]='.urlencode($dados);
                            //$parametros.= '&'.$nome.'[]='.urlencode($dados);
                        }
                    }else if ($nome != 'pagina' && $nome != 'inicio' && $nome != 'valor' && !empty($GLOBALS[$nome]) && $GLOBALS[$nome] <> ' ') {
                        $parametros.= '&'.$nome.'='.urlencode($GLOBALS[$nome]);
                    }
                }
            }else { //quando foi submetido ou post
                global $HTTP_POST_VARS;
                while (list($nome) = each($HTTP_POST_VARS)) {
                    if(is_array($HTTP_POST_VARS[$nome])) {
                        foreach ($HTTP_POST_VARS[$nome] as $dados) {
                            //Mudan�a feita no dia 15/06/2015 ...
                            if(strpos($parametros, $nome) == 0) $parametros.= '&'.$nome.'[]='.urlencode($dados);
                            //$parametros.= '&'.$nome.'[]='.urlencode($dados);
                        }
                    }else if ($nome != 'pagina' && $nome != 'inicio' && $nome != 'valor' && !empty($GLOBALS[$nome])  && $GLOBALS[$nome] <> ' ') {
                        $parametros.= '&'.$nome.'='.urlencode($GLOBALS[$nome]); //nome e dados dos submits
                    }
                }
            }
        }
        return $parametros;
    }
    
    function paginar($total_registro, $valor_pagina, $pagina) {
        paginacao::paginacao_google($total_registro, $valor_pagina, $pagina);
    }
	
    function paginacao_google($total_registro, $qtde_por_pagina, $pagina_atual) {
        $parametros	= paginacao::parametros();
        if(empty($pagina_atual)) $pagina_atual = 1;
        $total_paginas 	= ceil($total_registro / $qtde_por_pagina);//C�lculo do Total de P�ginas ...
        $pagina_inicial     = $pagina_atual - 5;
        $pagina_final 	= ($total_paginas <= 5) ? $pagina_atual + ($total_paginas - $pagina_atual) : $pagina_atual + 5;
        //Controlando o Range de Registros ...
        if($pagina_atual <= 5) $pagina_inicial = 1;//Parte Inicial dos Registros na Pagina��o ...
        if($pagina_atual >= $total_paginas - 5) $pagina_final = $total_paginas;//Parte Final dos Registros na Pagina��o ...
        $primeiro_registro_pagina 	= ($pagina_atual * $qtde_por_pagina - $qtde_por_pagina + 1);
        $ultimo_registro_pagina 	= (($pagina_atual * $qtde_por_pagina + 1) > $total_registro) ? $total_registro : ($pagina_atual * $qtde_por_pagina);
        $print_paginacao = "<p><font color='darkgreen' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='4'><b>Total de Registro(s): $total_registro - Visualizando de $primeiro_registro_pagina &agrave; $ultimo_registro_pagina</b></font><p>";
        if($total_paginas > 1) {
            if($pagina_atual > 1) {//Parte Inicial da Pagina��o ...
                $print_paginacao.= "<a href='{$PHP_SELF}?pagina=1&inicio=0".$parametros."' class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='darkblue'>&lt; Primeira</font></a>&nbsp;";
                $print_paginacao.= "<a href='{$PHP_SELF}?pagina=".($pagina_atual - 1)."&inicio=".(($pagina_atual - 2) * $qtde_por_pagina).$parametros."' class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='black'>&lt;&lt; Anterior </font></a>&nbsp;&nbsp;";
            }
            for($i = $pagina_inicial; $i <= $pagina_final; $i++) {//Meio da Pagina��o ...	
                if($pagina_atual == $i) {//Na p�gina atual em que o login est�, eu n�o coloco LINK ...
                    $print_paginacao.= "<font color='red' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'><b>".$pagina_atual." </b></font>";
                }else {//Se o �ndice n�o corresponde com a p�gina mostrada atual, coloco um link para ir a essa p�gina ...				
                    $print_paginacao.= "<a href='{$PHP_SELF}?pagina=$i&inicio=".(($i - 1) * $qtde_por_pagina).$parametros."' class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'><b>".$i." </b></font></a>";
                }
            }
            if($pagina_atual < $total_paginas) {//Parte Final da Pagina��o ...
                $print_paginacao.= "&nbsp;&nbsp;<a href='{$PHP_SELF}?pagina=".($pagina_atual + 1)."&inicio=".($pagina_atual * $qtde_por_pagina).$parametros."' class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='black'>Pr&oacute;xima &gt;&gt;</font></a>&nbsp;";
                $print_paginacao.= "<a href='{$PHP_SELF}?pagina=$total_paginas&inicio=".(($total_paginas - 1) * $qtde_por_pagina).$parametros."' class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='darkblue'>&Uacute;ltima &gt;</font></a>";
            }
        }
        $inicio                 = ($pagina_atual == 1) ? 0 : intval(($pagina_atual - 1) * $qtde_por_pagina);
        //Infelizmente tive que utilizar $GLOBALS[] p/ n�o perder os par�metros da pagina��o ...
        $GLOBALS['parametro']   = '?pagina='.$pagina_atual.'&inicio='.$inicio.$parametros;
        session_start('funcionarios');
        //Sempre destr�i o Valor anterior que est� armazenado na Sess�o ...
        unset($_SESSION['parametro']);
        if(!session_is_registered('parametro')) {//Confirmo se realmente foi destru�do o Valor que estava na Sess�o ...
            //Registro o "par�metro" em Sess�o pq s� dessa maneira consigo fazer q o sys enxergue esse valor em todas as Telas ...
            $_SESSION['parametro'] = $GLOBALS['parametro'];
        }
        //Passo essa vari�vel como global para que essa seja enxergada na outra fun��o "print_paginacao" abaixo ...
        $GLOBALS['print_paginacao'] = $print_paginacao;
    }
	
    function print_paginacao($imprimir = 'sim') {
        if($imprimir == 'sim') echo $GLOBALS['print_paginacao'];
    }
	
    function paginar_ajax($total_registro, $qtde_por_pagina, $pagina_atual, $name_div) {
        $parametros	= paginacao::parametros();
        if(empty($pagina_atual)) $pagina_atual = 1;
        $total_paginas  = ceil($total_registro / $qtde_por_pagina);//C�lculo do Total de P�ginas ...
        $pagina_inicial = $pagina_atual - 5;
        $pagina_final 	= ($total_paginas <= 5) ? $pagina_atual + ($total_paginas - $pagina_atual) : $pagina_atual + 5;
        //Controlando o Range de Registros ...
        if($pagina_atual <= 5) $pagina_inicial = 1;//Parte Inicial dos Registros na Pagina��o ...
        if($pagina_atual >= $total_paginas - 5) $pagina_final = $total_paginas;//Parte Final dos Registros na Pagina��o ...
        $primeiro_registro_pagina 	= ($pagina_atual * $qtde_por_pagina - $qtde_por_pagina + 1);
        $ultimo_registro_pagina 	= (($pagina_atual * $qtde_por_pagina + 1) > $total_registro) ? $total_registro : ($pagina_atual * $qtde_por_pagina);
        $print_paginacao = "<p><font color='darkgreen' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='4'><b>Total de Registro(s): $total_registro - Visualizando de $primeiro_registro_pagina &agrave; $ultimo_registro_pagina</b></font><p>";
        if($total_paginas > 1) {
            if($pagina_atual > 1) {//Parte Inicial da Pagina��o ...
                $print_paginacao.= "<a href=\"javascript:ajax('".$GLOBALS['PHP_SELF']."?pagina=1&inicio=0".$parametros."', '$name_div', '', 'SIM', 'GET')\" class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='darkblue'>&lt; Primeira</font></a>&nbsp;";
                $print_paginacao.= "<a href=\"javascript:ajax('".$GLOBALS['PHP_SELF']."?pagina=".($pagina_atual - 1)."&inicio=".(($pagina_atual - 2) * $qtde_por_pagina).$parametros."', '$name_div', '', 'SIM', 'GET')\" class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='black'>&lt;&lt; Anterior </font></a>&nbsp;&nbsp;";
            }
            for($i = $pagina_inicial; $i <= $pagina_final; $i++) {//Meio da Pagina��o ...	
                if($pagina_atual == $i) {//Na p�gina atual em que o login est�, eu n�o coloco LINK ...
                    $print_paginacao.= "<font color='red' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'><b>".$pagina_atual." </b></font>";
                }else {//Se o �ndice n�o corresponde com a p�gina mostrada atual, coloco um link para ir a essa p�gina ...				
                    $print_paginacao.= "<a href=\"javascript:ajax('".$GLOBALS['PHP_SELF']."?pagina=$i&inicio=".(($i - 1) * $qtde_por_pagina).$parametros."', '$name_div', '', 'SIM', 'GET')\" class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'><b>".$i." </b></font></a>";
                }
            }
            if($pagina_atual < $total_paginas) {//Parte Final da Pagina��o ...
                $print_paginacao.= "&nbsp;&nbsp;<a href=\"javascript:ajax('".$GLOBALS['PHP_SELF']."?pagina=".($pagina_atual + 1)."&inicio=".($pagina_atual * $qtde_por_pagina).$parametros."', '$name_div', '', 'SIM', 'GET')\" class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='black'>Pr&oacute;xima &gt;&gt;</font></a>&nbsp;";
                $print_paginacao.= "<a href=\"javascript:ajax('".$GLOBALS['PHP_SELF']."?pagina=$total_paginas&inicio=".(($total_paginas - 1) * $qtde_por_pagina).$parametros."', '$name_div', '', 'SIM', 'GET')\" class='link'><font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='darkblue'>&Uacute;ltima &gt;</font></a>";
            }
        }
        $inicio                 = ($pagina_atual == 1) ? 0 : intval(($pagina_atual - 1) * $qtde_por_pagina);
        //Infelizmente tive que utilizar $GLOBALS[] p/ n�o perder os par�metros da pagina��o ...
        $GLOBALS['parametro']   = '?pagina='.$pagina_atual.'&inicio='.$inicio.$parametros;
        session_start('funcionarios');
        //Sempre destr�i o Valor anterior que est� armazenado na Sess�o ...
        unset($_SESSION['parametro']);
        if(!session_is_registered('parametro')) {//Confirmo se realmente foi destru�do o Valor que estava na Sess�o ...
            //Registro o "par�metro" em Sess�o pq s� dessa maneira consigo fazer q o sys enxergue esse valor em todas as Telas ...
            $_SESSION['parametro'] = $GLOBALS['parametro'];
        }
        //Passo essa vari�vel como global para que essa seja enxergada na outra fun��o "print_paginacao" abaixo ...
        $GLOBALS['print_paginacao'] = $print_paginacao;
    }
}
?>