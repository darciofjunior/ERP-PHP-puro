<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
session_start('funcionarios');

if(!empty($_GET['id_produto_insumo'])) {
    $sql = "SELECT discriminacao 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $discriminacao  = $campos[0]['discriminacao'];
//Condição acrescentada no SQL abaixo ...
    $condicao_pi = " AND bop.`id_produto_insumo` = ".$_GET['id_produto_insumo'];
}

/*Faço uma verificação de Toda(s) as OP(s) que foram baixadas e estornadas p/ o PA atrelado 
dessa OP e PI(s) em questão, mas somente listo as OP(s) que estão em aberto ...*/
$sql = "SELECT bop.*, ops.qtde_produzir 
        FROM `ops` 
        INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` AND bop.`status` IN (2, 3) $condicao_pi 
        WHERE ops.`id_produto_acabado` = '$_GET[id_produto_acabado]' 
        AND ops.`status_finalizar` = '0' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: OP(s) Baixada(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            OP(s) Baixada(s) -
            <font color='yellow'> 
                P.A.: 
            </font>
        <?
            echo intermodular::pa_discriminacao($_GET['id_produto_acabado'], 0);
/****************************************************************************************/
//Se existir PI, então ...
            if(!empty($_GET['id_produto_insumo'])) {
                echo '<br><font color="yellow">P.I.: </font>';
                echo $discriminacao;
            }
/****************************************************************************************/
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OP
        </td>
        <td>
            Qtde Baixada
        </td>
        <td>
            Qtde a Produzir
        </td>
        <td>
            Data e Hora da Baixa
        </td>
        <td>
            Baixa
        </td>
        <td>
            Obs
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            /*******Esse parâmetro é para que essa tela seja aberta como Pop-UP e não mostre os botões 
            do fim da Tela*******/
            $url = "javascript:nova_janela('../../producao/ops/alterar.php?passo=2&id_op=".$campos[$i]['id_op']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c') ";
?>
    <tr class='linhanormal' align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href="#" title='Detalhes de OP' alt='Detalhes de OP' class='link'>
                <?=$campos[$i]['id_op'];?>
            </a>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_baixa'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['qtde_produzir'];?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' - '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
        <?
            if($campos[$i]['status'] == 0) {
                echo '<font color="darkblue"><b>EM ABERTO</b></font>';
            }else if($campos[$i]['status'] == 1) {
                echo '<font color="darkblue"><b>PARCIAL</b></font>';
            }else if($campos[$i]['status'] == 2) {
                echo '<font color="darkblue"><b>TOTAL</b></font>';
            }else if($campos[$i]['status'] == 3) {
                echo '<font color="darkblue"><b>ESTORNADO</b></font>';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só exibe as OP(s) que estão <b>EM ABERTO</b>.
</pre>