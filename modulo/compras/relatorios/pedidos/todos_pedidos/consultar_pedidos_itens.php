<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/relatorios/pedidos/consultar_relatorio_pedido.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ ITEM(NS) PENDENTE(S) NESSE MÊS.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ PEDIDO(S) PENDENTE(S) NESSE MÊS.</font>";


if($passo == 1) {
///// Parte de Pedidos
	$sql = "select p.*, f.razaosocial, e.nomefantasia as nomefantasia 
                from pedidos p, fornecedores f, empresas e 
                where p.ativo = 1 and p.id_empresa = e.id_empresa and p.id_fornecedor = f.id_fornecedor and substring(p.data_emissao,6,2) = '$mes' and substring(p.data_emissao,1,4) = '$ano' order by p.data_emissao DESC ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
        if($linhas == 0) {
?>
	<script language='JavaScript'>
		window.location = 'consultar_pedidos_itens.php?valor=2'
	</script>
<?
        }else {
?>
<html>
<head>
<title>.:: Pedidos Pendentes ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language='JavaScript' Src='../../../../../js/tabela.js'></Script>
<Script Language='JavaScript' Src='../../../../../js/validar.js'></Script>
<Script Language='JavaScript' Src='../../../../../js/geral.js'></Script>
<Script Language='JavaScript' Src='../../../../../js/nova_janela.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' action='<?=$PHP_SELF.'?passo=2';?>' method='post' onsubmit='return validar()'>
<table width='80%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr class="atencao" align="center">
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='8'>
            <font color='#FFFFFF' size='-1'>
                Pedido(s) Pendente(s)
            </font>
        </td>
    </tr>
    <tr class="linhadestaque">
      <td colspan='2'> <font color='#FFFFFF' size='-1'>
        <center>
          N. &ordm; Pedido
        </center>
        </font> </td>
      <td width='70'> <font color='#FFFFFF' size='-1'>
        <center>
          Emiss&atilde;o
        </center>
        </font> </td>
      <td colspan="2" align="center"> <font color='#FFFFFF' size='-1'>
        <center>
          Fornecedor
        </center>
        </font> <font color='#FFFFFF' size='-1'>&nbsp; </font> </td>
      <td width='75' align="center"> <div align="center">Pend&ecirc;ncia</div></td>
      <td width='100' align="center"> <div align="center">Empresa</div></td>
    </tr>
    <?
			for ($i=0;  $i < $linhas; $i++) {
				$id_pedido = $campos[$i]["id_pedido"];
				$url="javascript:nova_janela('../../../pedidos/consultar_itens_pedidos.php?id_pedido=$id_pedido&popup=1','DETALHES','','','','',600,800, 'c', 'c')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='14'>
            <a href="<?=$url;?>">
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
      <td width='92' align='center'>
		<a href="<?=$url;?>">
			<?=$campos[$i]["id_pedido"];?>
		</a>
      </td>
      <td width='70' align='center'> <?echo data::datetodata(substr($campos[$i]["data_emissao"],0,10),'/');?>
      </td>
      <td colspan="2" align="left"> <?echo $campos[$i]["razaosocial"];?>
      </td>
      <td width='75' align="center">
        <?
				$sql = "Select id_item_pedido 
                                        from itens_pedidos 
                                        where id_pedido=".$campos[$i]['id_pedido']." 
                                        and status = 1 limit 1";
				$campos2 = bancos::sql($sql);
				$sql = "Select id_item_pedido 
                                        from itens_pedidos 
                                        where id_pedido=".$campos[$i]['id_pedido']." 
                                        and status = 2 limit 1";
				$campos3 = bancos::sql($sql);
				if(count($campos2) > 0) {
					echo '<font color="FF0000">Total</font>';
				}else if(count($campos3) > 0) {
					echo '<font color="#006600">Concluído</font>';
				} else {
					echo '<font color="0000FF">Parcial</font>';
				}
		?>
      </td>
      <td width='100' align="left"> <?echo $campos[$i]["nomefantasia"];?>
      </td>
    </tr>
    <?
			}
	?>
    <tr class="linhacabecalho">
      <td colspan='8' align="center"> <input style="color:red" type='button' name='cmd_fechar' value='Fechar' title='Fechar' class='botao' onclick="window.close()">
      </td>
    </tr>
  </table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
} else if($passo == 2) {
	//Parte de Itens Pendentes
	$sql = "select ui.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                from pedidos p, itens_pedidos ip, produtos_insumos pi, fornecedores f, empresas e, unidades ui, grupos g 
                where pi.id_unidade = ui.id_unidade and pi.id_produto_insumo = ip.id_produto_insumo and g.id_grupo = pi.id_grupo and ip.id_pedido = p.id_pedido and substring(p.data_emissao,6,2) = '$mes' and substring(p.data_emissao,1,4) = '$ano' and p.ativo = 1 and p.id_empresa = e.id_empresa and p.id_fornecedor = f.id_fornecedor  order by p.data_emissao DESC ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
	<Script Language = 'JavaScript'>
            window.location = 'consultar_pedidos_itens.php?valor=1'
	</Script>
<?
        }else {
?>
<html>
<head>
	<title>.:: Itens ::.</title>
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
		<meta http-equiv='cache-control' content='no-store'>
		<meta http-equiv='pragma' content='no-cache'>
		<link href='../../../../../css/layout.css' type=text/css rel=stylesheet>
		<Script Language='JavaScript' Src='../../../../../js/validar.js'></Script>
		<Script Language='JavaScript' Src='../../../../../js/nova_janela.js'></Script>
		<Script Language='JavaScript' Src='../../../../../js/tabela.js'></Script>
	</head>
	<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
		<form name='form' action='<?echo $PHP_SELF.'?passo=3';?>' method='post' onsubmit='return validar(1)'>
			<table width='80%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
                            <tr>
                            </tr>
                            <tr class="linhacabecalho" align='center'>
                                <td colspan="11">
                                    <font color='#FFFFFF' size='-1'>
                                        Itens de Pedido
                                    </font>
                                </td>
                            </tr>
                            <tr class='linhadestaque' align='center'>
                                <td>Qtde Solicitado</td>
    				<td>Qtde Recebido</td>
                                <td>Qtde Restante</td>
                                <td><b>Un.</b></td>
                                <td><b>Referência</b></td>
                                <td><b>Discriminação</b></td>
                                <td><b>Preço Unitário</b></td>
                                <td><b>Valor Total</b></td>
                                <td><b>N.º Pedido</b></td>
                            </tr>
                    <?
			$pular = 0;
			for ($i=0;  $i < $linhas; $i++) {
			$id_pedido = $campos[$i]["id_pedido"];
				$url="javascript:nova_janela('../../../pedidos/consultar_pedidos_iframe.php?id_pedido=$id_pedido','POP','','','','',600,800, 'c', 'c', '','','s','s','','','')";
				//$url="javascript:nova_janela('../detalhes_pedidos.php?id_pedido=".mysql_result($executa, $i, "ip.id_pedido")."','POP','','','','',550,780, 'c', 'c','','','s','s','','','')";
				$totalqtde = 0;
				//$totalvalor = 0;
				$totalvalorcomipi = 0;
/////////////////////////////////////////////////////////////////////
						$totalqtde = $totalqtde + $campos[$i]['qtde'];
						$totalqtde2=str_replace('.',',',$campos[$i]['qtde']);
/////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////
						$sql = "SELECT sum(nh.qtde_entregue) as total_entregue 
                                                        from nfe_historicos nh, itens_pedidos ip, pedidos p 
                                                        where ip.id_item_pedido = '".$campos[$i]['id_item_pedido']."' and ip.id_pedido = p.id_pedido and ip.id_item_pedido = nh.id_item_pedido";
						$campos2 = bancos::sql($sql);
						$total_entregue = $campos2[0]['total_entregue'];
/////////////////////////////////////////////////////////////////////

						$total_restante =  $totalqtde - $total_entregue;
?>
				<tr class='linhanormal' onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')">
					<td  align='center'><font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>&nbsp;
					</font><?=$totalqtde2;?></td>
					<td align='center'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>&nbsp;
					</font><?=number_format($total_entregue, 2, ',', '.');?></td>
					<td align='center'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>&nbsp;
					</font><?=str_replace('.', ',', $total_restante);?></td>
					<td align='left'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
						<?echo $campos[$i]['sigla'];?>
					</font></td>
					<td align='left'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
						<?echo $campos[$i]['referencia'];?>
					</font></td>
					<td align='left'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
						<?echo $campos[$i]['discriminacao'];?>
					</font></td>
					<td align='right'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?
					$tipo_moeda = $campos[$i]["tp_moeda"];
					if($tipo_moeda == 1) {
						$tipo_moeda = "R$ ";
					}
					else if($tipo_moeda == 2) {
						$tipo_moeda = "U$ ";
					}
					else {
						$tipo_moeda = "&euro;";
					}
					?>
						<?echo $tipo_moeda.str_replace('.',',',$campos[$i]['preco_unitario']);?>
					</font></td>
					<td align='right'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?
					$tipo_moeda = $campos[$i]["tp_moeda"];
					if($tipo_moeda == 1) {
						$tipo_moeda = "R$ ";
					}
					else if($tipo_moeda == 2) {
						$tipo_moeda = "U$ ";
					}
					else {
						$tipo_moeda = "&euro;";
					}
				$valortotal = $valortotal + $campos[$i]['valor_total'];
				echo $tipo_moeda.str_replace('.',',',$campos[$i]['valor_total']);
			?>
					</font></td>
					<td onclick="<?=$url;?>" width='50' align='center'> <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
						<a href="<?=$url?>";><?echo $campos[$i]['id_pedido'];?></a>
					</font></td>
				</tr>
<?							$pular ++;
						//}
			}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan="11">
            <input type="button" name="cmd_fechar" value="Fechar" style="color:red" class="botao" onclick="window.close()">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
	}
} else {
?>
<html>
	<head>
		<title>Consultar Pedidos / Itens</title>
		<link href="../../../../../css/layout.css" type="text/css" rel="stylesheet">
	</head>
	<body>
		<form name="form" method="POST" action="">
			<table width="60%" align="center" cellpadding="1" cellspacing="1">
				<tr class="linhacabecalho">
					<td align="center">
						Consultar Pedidos / Itens
					</td>
				</tr>
				<tr class='linhanormal'>
					<td><input type="radio" name="opt_opcao" value="1" id="opt1" checked><label for="opt1">Visualizar Pedidos</label></td>
				</tr>
				<tr class='linhanormal'>
					<td><input type="radio" name="opt_opcao" value="3" id="opt2"><label for="opt2">Visualizar Itens</label></td>
				</tr>
				<tr class="linhacabecalho">
					<td align="center">
						<input type="button" name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" class="botao" title="Botão" onclick="relatorio()">
						<input type="button" name="cmd_fechar" style="color:red" value="Fechar" class="botao" title="Botão" onclick="window.close()"></td>
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>
<Script Language = 'Javascript'>
function relatorio() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location="consultar_pedidos_itens.php?passo=1&mes=<?=$mes;?>&ano=<?=$ano;?>"
    } else if(document.form.opt_opcao[1].checked == true) {
        window.location="consultar_pedidos_itens.php?passo=2&mes=<?=$mes;?>&ano=<?=$ano;?>"
    }
}
</Script>
<?
}
?>