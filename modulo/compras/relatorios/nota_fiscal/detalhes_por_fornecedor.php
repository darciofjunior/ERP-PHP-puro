<? 
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
session_start('funcionarios');

//Se o usuário já passou a Data então ...
if(!empty($txt_data_inicial)) {
	$campo_data = ($opt_data == 1) ? 'nfe.data_emissao' : 'nfe.data_entrega';
	$condicao_nf = " AND SUBSTRING($campo_data, 1, 10) BETWEEN '$txt_data_inicial' AND '$txt_data_final' ";
}
if($cmb_empresa == '') $cmb_empresa = '%';

/*Busca de todas as Compras do(s) Fornecedor(es) na Tabela de NFE(s) de acordo com o Fornec digitado 
pelo usuário e Empresa Selecionada no período passado por parâmetro ...

*** Aqui eu só pego o id_fornecedor, porque depois eu busco todas as NFE(s) daquele id_fornecedor em questão ...*/
$sql = "SELECT f.id_fornecedor, f.razaosocial 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor AND f.razaosocial LIKE '%$txt_consultar%' 
        WHERE nfe.`id_empresa` LIKE '$cmb_empresa' 
        $condicao_nf GROUP BY f.id_fornecedor ORDER BY f.razaosocial ";
$campos_nfe = bancos::sql($sql);
$linhas_nfe = count($campos_nfe);
if($linhas_nfe == 0) {//Se não encontrou nenhuma Compra ...
?>
    <Script Language = 'JavaScript'>
        window.location = 'relatorio_nota_fiscal.php?valor=1'
    </Script>
<?
}else {//Se encontrou alguma Compra ...
?>
<html>
<head>
<title>.:: Relatório de Nota Fiscal de Entrada ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name="form" method="post">
<table width="90%" cellpadding="1" cellspacing="1" align='center'>
<?
	for($i = 0; $i < $linhas_nfe; $i++) {
//Retorna todas as NFE(s) de acordo com o id_fornecedor do Loop ...
		$sql = "SELECT e.nomefantasia, nfe.*, tm.simbolo 
                        FROM `nfe` 
                        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                        INNER JOIN `empresas` e ON e.id_empresa = nfe.id_empresa 
                        WHERE nfe.`id_fornecedor` = '".$campos_nfe[$i]['id_fornecedor']."' 
                        AND nfe.`id_empresa` LIKE '$cmb_empresa' 
                        $condicao_nf ORDER BY nfe.data_emissao DESC ";
		$campos = bancos::sql($sql);
		$linhas = count($campos);
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan="7">
			<font color='yellow'>
				Fornecedor: 
			</font>
			<?=$campos_nfe[$i]['razaosocial'];?>
			<font color='yellow'>
				- Período de: 
			</font>
			<?=data::datetodata($txt_data_inicial, '/').' à '.data::datetodata($txt_data_final, '/');?>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td>
			<font color='#FFFFFF' size='-1'>
				Nº da Nota
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data de Emissão
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data de Entrega
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Tipo da Nota
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor da Nota s/ IPI
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Valor da Nota c/ IPI
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Empresa
			</font>
		</td>
	</tr>
<?
//Limpo essas variáveis p/ não ficar acumulando o Total da NFE do Loop Anterior ...
		$valor_todas_nfes_fornec = 0;
		$valor_todas_nfes_fornec_com_ipi = 0;
		for($j = 0; $j < $linhas; $j++) {
                    $moeda = $campos[$j]['simbolo'].' ';
/****************************************************************************************/
//Busca o Valor Total da NFE e o Valor Total da NFE com IPI ...
                    $sql = "SELECT (SUM(nfeh.qtde_entregue * nfeh.valor_entregue)) AS total_nfe, (SUM((nfeh.qtde_entregue * nfeh.valor_entregue)) + SUM((nfeh.qtde_entregue * nfeh.valor_entregue) * nfeh.ipi_entregue / 100)) AS total_nfe_com_ipi 
                            FROM `nfe` 
                            INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                            WHERE nfe.`id_nfe` = '".$campos[$j]['id_nfe']."' ";
                    $campos_total_nfe = bancos::sql($sql);
                    if(count($campos_total_nfe) > 0) {
                        $valor_total_nfe = $campos_total_nfe[0]['total_nfe'];
                        $valor_total_nfe_com_ipi = $campos_total_nfe[0]['total_nfe_com_ipi'];
                    }
/****************************************************************************************/
?>
	<tr class='linhanormal' align='center'>
		<td>
                    <a href = '../../pedidos/nota_entrada/itens/index.php?id_nfe=<?=$campos[$j]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                        <?=$campos[$j]['num_nota'];?>
                    </a>
		</td>
		<td>
                    <?=data::datetodata($campos[$j]['data_emissao'], '/');?>
		</td>
		<td>
                    <?=data::datetodata($campos[$j]['data_entrega'], '/');?>
		</td>
		<td>
		<?
                        if($campos[$j]['tipo'] == 1) {
                            echo 'NF';
                        }else {
                            echo 'SGD';
                        }
		?>
		</td>
		<td align='right'>
                    <?=$moeda.number_format($valor_total_nfe, 2, ',', '.');?>
		</td>
		<td align='right'>
                    <?=$moeda.number_format($valor_total_nfe_com_ipi, 2, ',', '.');?>
		</td>
		<td>
                    <?=$campos[$j]['nomefantasia'];?>
		</td>
	</tr>
<?
//Aqui eu acumulo o Total de Todas NFE(s) mas do Fornecedor do Loop ...
                    $valor_todas_nfes_fornec+= $valor_total_nfe;
                    $valor_todas_nfes_fornec_com_ipi+= $valor_total_nfe_com_ipi;
		}
?>
	<tr class='linhacabecalho'>
            <td colspan='3'>
                <font color='yellow'>
                    Valor Total s/ IPI: <?=$moeda.number_format($valor_todas_nfes_fornec, 2, ',', '.');?>
                </font>
            </td>
            <td colspan='4' align='right'>
                <font color='yellow'>
                    Valor Total c/ IPI: <?=$moeda.number_format($valor_todas_nfes_fornec_com_ipi, 2, ',', '.');?>
                </font>
            </td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
<?
//Aqui eu acumulo o Total de Todas NFE(s) de Todos os Fornecedores do Loop ...
            $valor_geral_todas_nfes+= $valor_todas_nfes_fornec;
            $valor_geral_todas_nfes_com_ipi+= $valor_todas_nfes_fornec_com_ipi;
	}
?>
</table>
<table width='80%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Valor Total Geral de Toda(s) NF(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Valor Total s/ IPI: 
            <font color='yellow'>
                <?=$moeda.number_format($valor_geral_todas_nfes, 2, ',', '.');?>
            </font>
        </td>
        <td>
            Valor Total c/ IPI: 
            <font color='yellow'>
                <?=$moeda.number_format($valor_geral_todas_nfes_com_ipi, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio_nota_fiscal.php'" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?}?>