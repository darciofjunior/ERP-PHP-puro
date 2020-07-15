<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
//Essa segurança é porque esse arquivo pode ser requisitado por outro arquivo ...
if(empty($_GET['nao_chamar_biblioteca'])) {
    segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');
}
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ENTRADA(S) REGISTRADA(S) PARA ESSA OP.</font>";

//Aki só lista todas as Entradas do P.A ...
$sql = "SELECT bop.*, bop.`data_sys` AS data_lancamento, f.`nome` 
        FROM `baixas_manipulacoes_pas` bmp 
        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$id_op' 
        LEFT JOIN `funcionarios` f ON f.`id_funcionario` = bmp.`id_funcionario` 
        WHERE bmp.`acao` = 'E' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Entrada(s) Registrada(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
}else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Entrada(s) Registrada(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde de Entrada
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Data
        </td>
        <td>
            Justificativa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($campos[$i]['qtde_baixa'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo number_format($campos[$i]['qtde_baixa'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_lancamento'], '/');?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['observacao'] == '') {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['observacao'];
            }
        ?>
        </td>
    </tr>
<?
        $total_qtde_baixa+= $campos[$i]['qtde_baixa'];
    }
?>
    <tr class='linhacabecalho' lign='center'>
        <td>
            <font color='yellow'>
                Total: 
            </font>
            <?=number_format($total_qtde_baixa, 2, ',', '.');?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
<?
}
?>
</table>
</body>
</html>