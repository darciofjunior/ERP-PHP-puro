<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
//Essa segurança é porque esse arquivo pode ser requisitado por outro arquivo ...
if(empty($_GET['nao_chamar_biblioteca'])) {
    segurancas::geral('/erp/albafer/modulo/producao/oes/alterar.php', '../../../');
}
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ENTRADA(S) REGISTRADA(S) PARA ESSA OE.</font>";

/*Verifico o Total de Entrada(s) Registrada(s) p/ esse OE $id_oe passador por parâmetro:

M -> "Manipulação" e Tipo seja: 

1) -> Manipulação p/ Substituição;
2) -> Manipulação p/ Substituição com Ordem de Embalagem;
3) -> Manipulação p/ Montagem de Jogos;

/* Aqui controlamos tanto as Entradas como PA(s) enviados da OE ... Sinal Positivo representa entrada, 
negativo PA(s) enviados ou correção de Entrada ...*/
$sql = "SELECT bmp.`qtde`, SUBSTRING(bmp.`data_sys`, 1, 10) AS data, bmp.`observacao`, f.`nome`, 
        gpa.`id_familia`, pa.`pecas_por_jogo` 
        FROM `baixas_manipulacoes_pas` bmp 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        LEFT JOIN `funcionarios` f ON f.`id_funcionario` = bmp.`id_funcionario` 
        WHERE bmp.`id_oe` = '$_GET[id_oe]' 
        AND bmp.`acao` = 'M' 
        AND bmp.`tipo_manipulacao` IN (1, 2, 3) ORDER BY bmp.`data_sys` DESC ";
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
        <td colspan='8'>
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
    /****************************************************************************************************/
    /*Aqui eu busco o Peças por Jogo do Produto de Retorno dessa OE porque esta será utilizada mais abaixo 
    caso a Família do PA da qual foi dada Entrada seja = 9 "Família de Machos" ...*/
    $sql = "SELECT pa.`pecas_por_jogo` 
            FROM `oes` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oes.`id_produto_acabado_e` 
            WHERE oes.`id_oe` = '$_GET[id_oe]' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    /****************************************************************************************************/

    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($campos[$i]['qtde'] == '0.00') {
                echo '&nbsp;';
            }else {
                if($campos[$i]['id_familia'] == 9) {//Nesse caso específico, o procedimento será um pouquinho diferenciado ...
                    $qtde = $campos[$i]['qtde'] * $campos[$i]['pecas_por_jogo'] / $campos_pa[0]['pecas_por_jogo'];
                }else {
                    $qtde = $campos[$i]['qtde'];
                }
                echo number_format($qtde, 2, ',', '.');
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
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
        $total_qtde+= $qtde;
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font color='yellow'>
                Total: 
            </font>
            <?=number_format($total_qtde, 2, ',', '.');?>
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