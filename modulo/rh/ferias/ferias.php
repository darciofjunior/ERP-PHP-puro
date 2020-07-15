<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $txt_nome = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['txt_nome'] : $_GET['txt_nome'];
    
    /*Trago somente os funcionários registrados, por isso que só trago da empresa Albafer e Tool Master 
    que não estejam em férias
     
    Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
    Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
    no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT c.cargo, e.nomefantasia, f.id_funcionario, f.nome, 
            DATE_FORMAT(f.ultimas_ferias_data_inicial, '%d/%m/%Y') AS ultimas_ferias_data_inicial, 
            DATE_FORMAT(f.ultimas_ferias_data_final, '%d/%m/%Y') AS ultimas_ferias_data_final, 
            DATE_FORMAT(f.periodo_anual_data_inicial, '%d/%m/%Y') AS periodo_anual_data_inicial, 
            DATE_FORMAT(f.periodo_anual_data_final, '%d/%m/%Y') AS periodo_anual_data_final, 
            f.tipo_salario, f.salario_pd 
            FROM `funcionarios` f 
            INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`id_empresa` IN (1, 2) 
            AND f.`status` < '3' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`nome` LIKE '$txt_nome%' 
            ORDER BY f.nome ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhum registro ...
?>
    <Script Language = 'Javascript'>
        window.location = 'ferias.php?valor=1'
    </Script>
<?
    }else {//Encontrou pelo menos 1 registro ...
?>
<html>
<head>
<title>.:: Férias ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Férias
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Nome
        </td>
        <td>
            Empresa
        </td>
        <td>
            Cargo
        </td>
        <td>
            Tipo de Salário
        </td>
        <td>
            Últimas Férias
        </td>
        <td>
            Período Anual
        </td>
        <td>
            Salário PD
        </td>
        <td>
            Salário PD + 1/3
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href = '../funcionario/detalhes.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>' class='html5lightbox'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Salário Horista ...
                echo '<font color="darkblue" title="Horista" style="cursor:help"><b>Hs</b></font>';
            }else {//Salário Mensalista ...
                echo '<font title="Mensalista" style="cursor:help"><b>M</b></font>';
            }
        ?>
        </td>
        <td>
            <b>
                <?if($campos[$i]['ultimas_ferias_data_inicial'] != '00/00/0000') echo $campos[$i]['ultimas_ferias_data_inicial'].' à '.$campos[$i]['ultimas_ferias_data_final'];?>
            </b>
        </td>
        <td>
            <b>
                <?if($campos[$i]['periodo_anual_data_inicial'] != '00/00/0000') echo $campos[$i]['periodo_anual_data_inicial'].' à '.$campos[$i]['periodo_anual_data_final'];?>
            </b>
        </td>
        <td align='right'>
        <?
        //Se o Salário do funcionário for do Tipo Horista multiplico por 220 p/ transformar em mensal ...
            $salario_pd = ($campos[$i]['tipo_salario'] == 1) ? $campos[$i]['salario_pd'] * 220 : $campos[$i]['salario_pd'];
            echo number_format($salario_pd, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=number_format($salario_pd + ($salario_pd / 3), 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'ferias.php'" class='botao'>
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
<title>.:: Férias ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' action="<?=$PHP_SELF.'?passo=1'?>" method='post'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Férias
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' size='40' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nome.focus()" style="color:#ff9900;" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>