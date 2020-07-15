<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/opcs_projetados/opcs_projetados.php', '../../../../');

$data_inicial   = data::datetodata($_GET['data_inicial'], '/');
$data_final     = data::datetodata($_GET['data_final'], '/');
?>
<html>
<head>
<title>.:: Detalhes de OPC(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function imprimir_opc(id_opc) {
    var resposta = confirm('DESEJA IMPRIMIR ESTA OPC COM MARGEM DE LUCRO ?')
    if(resposta == true) {
        nova_janela('../projetar_opc/imprimir_opc_com_ml.php?id_opc='+id_opc, 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        nova_janela('../projetar_opc/imprimir_opc.php?id_opc='+id_opc, 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<table border="1" width="90%" align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
            <td colspan='8'>
                <font color='yellow' size='-1'>
                    Período: <?=$data_inicial.' à '.$data_final;?>
                </font>
                <br>
                <font color='yellow' size='-1'>
                    Cliente:
                </font>
                <?
                    $sql = "SELECT if(razaosocial = '', nomefantasia, razaosocial) as cliente 
                            FROM `clientes` 
                            WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
                    $campos_cliente = bancos::sql($sql);
                    echo $campos_cliente[0]['cliente'];
                ?>
            </td>
    </tr>
<?
//Aqui eu busco todos as OPC(s) que foram feitos para o Cliente...
	$sql = "SELECT opcs.id_opc, f.nome, opcs.tipo_nota, opcs.tipo_opc, opcs.qtde_anos, opcs.data_sys, SUM(oi.qtde_proposta * oi.preco_proposto) AS valor_total 
                FROM `opcs` 
                INNER JOIN `opcs_itens` oi ON oi.id_opc = opcs.id_opc 
                INNER JOIN `funcionarios` f ON f.id_funcionario = opcs.id_funcionario 
                WHERE opcs.`id_cliente` = '$_GET[id_cliente]' 
                AND SUBSTRING(opcs.data_sys, 1, 10) BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
                GROUP BY opcs.`id_opc` ORDER BY opcs.`id_opc` DESC ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
?>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' title="N.&ordm; Projeção" size='-1'>
				N.&ordm; Proj
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Data de Emissão" size='-1'>
				Funcionário Que Projetou
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Tipo de Nota
			</font>
		</td>
                <td>
			<font color='#FFFFFF' size='-1'>
				Tipo de OPC
			</font>
		</td>
		<td>
			<font color='#FFFFFF' title="Condição de Faturamento" size='-1'>
				Qtde de Anos
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor Total
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data Criação
			</font>
		</td>
	</tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td>
                    <font title="Pedido Concluído" color="red">
                        <?=$campos[$i]['id_opc'];?>
                    </font>
                    &nbsp;<img src="../../../../imagem/impressora.gif" border="0" onclick="imprimir_opc('<?=$campos[$i]['id_opc'];?>')" title="Imprimir OPC Projetado" alt="Imprimir OPC Projetado" style="cursor:pointer">
		</td>
		<td>
                    <?=$campos[$i]['nome'];?>
		</td>  
		<td>
                    <?=$campos[$i]['tipo_nota'];?>
		</td>
                <td>
                <?
                    if($campos[$i]['tipo_opc'] == 'C') {//Referente ao que o cliente compra ...
                        echo 'Normal';
                    }else {//O que o cliente não Compra ...
                        echo 'Curva ABC';
                    }
                ?>
		</td>
		<td>
                    <?=$campos[$i]['qtde_anos'];?>
		</td>                     
                <td>
                    <?=number_format($campos[$i]['valor_total'], 2, ',', '.');?>
		</td>
		<td>
                    <?=data::datetodata($campos[$i]['data_sys'], '/');?>
		</td>
	</tr>     
        <?}?>
        <tr class="linhacabecalho" align="right">
            <td colspan='8'>
                &nbsp;
            </td>
	</tr>   
</table>
</body>
</html>