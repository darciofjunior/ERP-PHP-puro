<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/excluir.php', '../../../');

$sql = "SELECT pa.referencia, pa.discriminacao 
        FROM `pacs_vs_maquinas` pm 
        INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pm.`id_produto_acabado_custo` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
        WHERE pm.id_maquina = '$_GET[id_maquina]' ";
$campos1 = bancos::sql($sql);
$linhas1 = count($campos1);

$sql = "SELECT f.nome 
        FROM `maquinas_vs_funcionarios` mf 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = mf.`id_funcionario` 
        WHERE mf.`id_maquina` = '$_GET[id_maquina]' ";
$campos2 = bancos::sql($sql);
$linhas2 = count($campos2);
?>
<html>
<head>
<title>.:: Locais Atrelados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <font color='yellow'>
                Nome da Máquina: 
            </font>
            <?
                $sql = "SELECT nome 
                        FROM `maquinas` 
                        WHERE `id_maquina` = '$_GET[id_maquina]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                echo $campos[0]['nome'];
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Locais Atrelados
        </td>
    </tr>
<?
    if($linhas1 > 0) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>* Essa máquina está atrelada na 4&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s): </b>
            <?
                for($i = 0; $i < $linhas1; $i++) echo '<br>'.$campos1[$i]['referencia'].' - '.$campos1[$i]['discriminacao'].';';
            ?>
        </td>
    </tr>
<?
    }

    if($linhas2 > 0) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>* Essa máquina está atrelada p/ o(s) seguinte(s) Funcionário(s): </b>
            <?
                for($i = 0; $i < $linhas2; $i++) echo '<br>'.$campos2[$i]['nome'];
            ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>