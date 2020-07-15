<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_pa_a_ser_atrelado   = $_POST['id_pa_a_ser_atrelado'];
        $txt_referencia         = $_POST['txt_referencia'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
    }else {
        $id_pa_a_ser_atrelado   = $_GET['id_pa_a_ser_atrelado'];
        $txt_referencia         = $_GET['txt_referencia'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
    }
    //Select Principal ...
    $sql = "SELECT pa.`id_produto_acabado`, pa.`ativo`, ed.`razaosocial`, gpa.`nome` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND (pa.`operacao` < '9' AND pa.operacao_custo < '9') 
            AND pa.`ativo` = '1' ORDER BY pa.`referencia` ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'atrelar_pa.php?id_pa_a_ser_atrelado=<?=$id_pa_a_ser_atrelado;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Atrelar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>  
function avancar(id_pa_a_ser_atrelado, id_produto_acabado) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATRELAR ESSE PRODUTO ACABADO ?')
    if(resposta == true) {
        window.location = 'atrelar_pa.php?passo=2&id_pa_a_ser_atrelado='+id_pa_a_ser_atrelado+'&id_produto_acabado='+id_produto_acabado
    }
}
</Script>
</head>
<body>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Produto(s) Acabado(s) p/ Atrelar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font title='Estoque Disponível' style='cursor:help'>
                Est Disp
            </font>
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Grupo P.A. / Empresa Divisão
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:avancar('".$id_pa_a_ser_atrelado."', '".$campos[$i]['id_produto_acabado']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='center'>
            <a href="<?=$url;?>" class='link'>
            <?
                $estoque_produto = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
                echo number_format($estoque_produto[3], 2, ',', '.');
            ?>
            </a>
        </td>
        <td>
        <?
            echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
//Se o PI já foi excluído, então o sistema mostra essa identificação
            if($campos[$i]['ativo'] == 0) echo '<font color="red"><b> (EXCLUÍDO) </b></font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'atrelar_pa.php?id_pa_a_ser_atrelado=<?=$id_pa_a_ser_atrelado;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
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
    //Verifico se o PA1 já está atrelado com o PA2 ...
    $sql = "SELECT `id_pa_substituir` 
            FROM `pas_substituires` 
            WHERE `id_produto_acabado_1` = '$_GET[id_pa_a_ser_atrelado]' AND `id_produto_acabado_2` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//O PA1 ainda não esta atrelado com o PA2 ...
        //Atrelando o P.A. Enviado na combo do P.A. Principal na Tabela relacional `pas_substituires` ...
        $sql = "INSERT INTO `pas_substituires` (`id_pa_substituir`, `id_produto_acabado_1`, `id_produto_acabado_2`) VALUES (NULL, '$_GET[id_pa_a_ser_atrelado]', '$_GET[id_produto_acabado]') ";
        bancos::sql($sql);
        $frase = 'PRODUTO ACABADO ATRELADO COM SUCESSO !';
    }else {//O PA1 já estava atrelado com o PA2 ...
        $frase = 'PRODUTO ACABADO JÁ EXISTENTE !';
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$frase;?>')
        opener.document.location = opener.document.location.href
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Atrelar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_pa_a_ser_atrelado' value='<?=$_GET[id_pa_a_ser_atrelado];?>'>
<!--*************************************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Atrelar
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' size='33' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discrimina&ccedil;&atilde;o' size='50' maxlength='100' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>