<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>NÃO HÁ RELATÓRIO DE MOVIMENTAÇÃO DE ESTOQUE PARA ESTE P.A.</font>";

//Busca de Todos os Dados da tabela de Relatório de Saldo de Estoque ...
$sql = "SELECT * 
        FROM `rel_saldos_estoques` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' ORDER BY data_acao DESC ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relátorio de Movimentação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<body>
<?
    if($linhas == 0) {//Não encontrou nenhum Registro de Relatório p/ o PA específico ...
?>

    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {//Encontrou pelo menos um Registro de Relatório p/ o PA específico ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relátorio de Movimentação de Estoque => 
            <font color='yellow'>
                <?=intermodular::pa_discriminacao($_GET['id_produto_acabado']);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Data de Ação
        </td>
        <td>
            Ação
        </td>
        <td>
            Observação Ação
        </td>
        <td>
            <font title='Funcionário' style='cursor:help'>
                Func.
            </font>
        </td>
        <td>
            <font title='Qtde Manipulada' style='cursor:help'>
                Qtde Man.
            </font>
        </td>
        <td>
            <font title='Saldo do Estoque Real' style='cursor:help'>
                Saldo E.R.
            </font>
        </td>
    </tr>
<?
        //Defino esse vetor aki para ficar mais fácil na hora de apresentação na Tela ...
        $vetor_acao = array('INDEFINIDO', 'MANIPULAR / OPS / SUBSTITUIR', 'COMPRAS', 'FATURAMENTO', 'VALE');
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($campos[$i]['data_acao'] != '0000-00-00 00:00:00') echo data::datetodata(substr($campos[$i]['data_acao'], 0, 10), '/').' - '.substr($campos[$i]['data_acao'], 11, 8);
        ?>
        </td>
        <td>
            <?=$vetor_acao[$campos[$i]['acao']];//Apresentação da Ação ...?>
        </td>
        <td>
            <?=$campos[$i]['obs_acao'];?>
        </td>
        <td>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo strtok($campos_funcionario[0]['nome'], ' ');
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_manipulada'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['saldo_est_real'], 2, ',', '.');?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>