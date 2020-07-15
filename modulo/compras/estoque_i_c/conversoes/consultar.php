<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>N�O H� CONVERS�O(�ES) CADASTRADA(S).</font>";
$mensagem[2] = "<font class='atencao'>CONVERS�O(�ES) EXCLU�DA(S) COM SUCESSO.</font>";

if(!empty($_GET['incluir_conversao'])) {
    //Verifica se j� est� no limite o n. de convers�es permitidas p/ o usu�rio logado ...
    if($_GET['total_conversoes'] == 5) {
        //Busca a convers�o mais antiga para poder apagar
        $sql = "SELECT `id_conversoes_temps` 
                FROM `conversoes_temps` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' ORDER BY `id_conversoes_temps` LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_conversoes_temps    = $campos[0]['id_conversoes_temps'];
        //Deleta os itens de convers�o ...
        $sql = "DELETE FROM `itens_conversoes_temps` WHERE `id_conversoes_temps` = '$id_conversoes_temps' ";
        bancos::sql($sql);
        //Deleta a convers�o ...
        $sql = "DELETE FROM `conversoes_temps` WHERE `id_conversoes_temps` = '$id_conversoes_temps' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Inclus�o de uma nova convers�o p/ o usu�rio logado ...
    $sql = "INSERT INTO `conversoes_temps` (`id_conversoes_temps`, `id_funcionario`) VALUES (NULL, '$_SESSION[id_funcionario]') ";
    bancos::sql($sql);
    $id_conversoes_temps = bancos::id_registro();
?>
    <Script Language= 'Javascript'>
        window.location = 'itens/index.php?id_conversoes_temps=<?=$id_conversoes_temps;?>'
    </Script>
<?
}

$sql = "SELECT DISTINCT(ct.`id_conversoes_temps`), f.`nome` 
        FROM `conversoes_temps` ct 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = ct.`id_funcionario` 
        WHERE ct.`ativo` = '1' ORDER BY ct.`id_conversoes_temps` DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Convers&otilde;es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function informar(total_registro) {
    if(total_registro == 5) {
        var pergunta = confirm('O SISTEMA PERMITE APENAS A INCLUS�O DE 5 CONVERS�ES POR USU�RIO !\nAP�S A INCLUS�O DA PR�XIMA O SISTEMA IR� APAGAR UMA EXISTENTE !\nDESEJA INCLUIR UMA NOVA CONVERS�O ASSIM MESMO ?')
        if(pergunta == true) {
            window.location = 'consultar.php?incluir_conversao=1&total_registro='+total_registro
        }else {
            return false
        }
    }else {//Como o N.� de Convers�es � inferior a 5, posso incluir outra normalmente ...
        window.location = 'consultar.php?incluir_conversao=1&total_registro='+total_registro
    }
}
</Script>
</head>
<body>
<?
    if($linhas == 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_incluir_conversao' value='Incluir Convers�o' title='Incluir Convers�o' onclick="window.location = 'consultar.php?incluir_conversao=1'" class='botao'>
        </td>
    </tr>
</table>
<?
    }else {
        //Verifico a qtde de convers�es existentes do funcion�rio que est� logado ...
        $sql = "SELECT COUNT(`id_conversoes_temps`) AS total_registro 
                FROM `conversoes_temps` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' ";
        $campos_total_registro  = bancos::sql($sql);
        $total_registro         = $campos_total_registro[0]['total_registro'];
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Consultar Convers�o(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.� Convers�o
        </td>
        <td>
            Funcion�rio
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'itens/index.php?id_conversoes_temps='.$campos[$i]['id_conversoes_temps'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['id_conversoes_temps'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_nova_conversao' value='Nova Convers�o' title='Nova Convers�o' onclick="informar('<?=$total_registro;?>')" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>
<pre>
<font color='red'><b>Aviso:</b></font>

O sistema permite apenas a inclus�o de 5 convers�es por usu�rio.
</pre>