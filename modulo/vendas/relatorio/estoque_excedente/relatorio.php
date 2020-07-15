<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/custos.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmd_consultar 	= $_POST['cmd_consultar'];
    $cmb_familia 	= $_POST['cmb_familia'];
    $cmb_grupo_pa 	= $_POST['cmb_grupo_pa'];
}else {
    $cmd_consultar 	= $_GET['cmd_consultar'];
    $cmb_familia 	= $_GET['cmb_familia'];
    $cmb_grupo_pa 	= $_GET['cmb_grupo_pa'];
}
?>
<html>
<head>
<title>.:: Relatório de Estoque Excedente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relatório de Estoque Excedente acima de 
            <font color='yellow'>
                <?=(int)genericas::variavel(73);?> mes(es)
            </font>
            <br/>
            Família
            <select name="cmb_familia" title="Selecione a Família" class='combo'>
            <?
                $sql = "SELECT `id_familia`, `nome` 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql, $cmb_familia);
            ?>
            </select>
            Grupo PA
            <select name="cmb_grupo_pa" title="Selecione o Grupo P.A." class='combo'>
            <?
                $sql = "SELECT `id_grupo_pa`, `nome` 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql, $cmb_grupo_pa);
            ?>
            </select>
            <br/>
        <?
            if(empty($_POST['opt_tipo_filtro'])) {//Aqui é para o caso de quando carrega a Tela e não dar erro ...
                $_POST['opt_tipo_filtro']   = 1;
                $checkedr                   = 'checked';
            }else {
                if($_POST['opt_tipo_filtro'] == 1) {
                    $checkedr   = 'checked';
                }else {
                    $checkedd   = 'checked';
                }
            }
        ?>
            <input type='radio' name='opt_tipo_filtro' value='1' title='Selecione o Tipo de Filtro' id='tipo_filtro1' <?=$checkedr;?>>
            <label for='tipo_filtro1'>Referência</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_tipo_filtro' value='2' title='Selecione o Tipo de Filtro' id='tipo_filtro2' <?=$checkedd;?>>
            <label for='tipo_filtro2'>Discriminação</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="text" name='txt_consultar' value="<?=$_POST['txt_consultar'];?>" size='40' class="caixadetexto">
            &nbsp;
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">					
        </td>
    </tr>
<?
if(!empty($cmd_consultar)) {
    if(empty($cmb_familia))  $cmb_familia = '%';
    if(empty($cmb_grupo_pa)) $cmb_grupo_pa = '%';

    if(!empty($_POST['txt_consultar'])) {
        if($_POST['opt_tipo_filtro'] == 1) {//Se consultou por Referência ...
            $condicao_referencia    = " AND pa.referencia LIKE '$_POST[txt_consultar]%' ";
        }else {//Se consultou por Discriminação ...
            $condicao_discriminacao = " AND pa.discriminacao LIKE '$_POST[txt_consultar]%' ";
        }
    }
    //Busca de todos os PA(s) que possuem Estoque Excedente e que não são Componentes ...
    $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`qtde_queima_estoque` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
            WHERE pa.`ativo` = '1' 
            AND pa.`qtde_queima_estoque` > '0.00' 
            $condicao_referencia $condicao_discriminacao ORDER BY pa.`referencia`, pa.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    //Se retornar pelo menos 1 registro ...
    if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Estoque Excedente
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <a href = '../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' title='Visualizar Estoque' class='html5lightbox'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['qtde_queima_estoque'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:purple' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Essa tela pode demorar um pouco p/ a exibição dos Dados, devido rodar uma função que faz a atualização do Estoque Excedente.
</pre>
<?	
    }else {
?>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
<?	
    }
}
?>