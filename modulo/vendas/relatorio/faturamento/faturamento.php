<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$valor_dolar_dia    = genericas::moeda_dia('dolar');
$valor_euro_dia     = genericas::moeda_dia('euro');

/*Observação: Essa tela possui vários "forms" e para não dar conflito com um outro arquivo 
que é solicitado aqui "Pedidos Pendentes", cada um possui um nome diferente ... */
?>
<html>
<head>
<title>.:: Relatório de Pedidos Emitidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar_faturamento() {
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    /*Verifico se o intervalo entre Datas é > do que 5 anos. Faço essa verificação porque se o usuário 
    colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados*/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > (365 * 5)) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A CINCO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar_faturamento()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <font color='red'>
            ****
            </font>
            Relat&oacute;rio de Faturamento 
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {
                    $datas              = genericas::retornar_data_relatorio(1);
                    $txt_data_inicial   = $datas['data_inicial'];
                    $txt_data_final     = $datas['data_final'];
                }
                $data_inicial   = data::datatodate($txt_data_inicial,"-");
                $data_final     = data::datatodate($txt_data_final,"-");
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;&nbsp;&nbsp;
            <input type='submit' name='cmd_consultar' id='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
<?
//Esse botão só aparece p/ os funcionários Roberto 62, Sandra 66 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98) {
?>
            <input type='button' name='cmd_cadastro_caixa' value='Cadastro de Caixa' title='Cadastro de Caixa' onclick="nova_janela('cadastro_caixa.php', 'CALENDÁRIO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:black' class='botao'>
        </td>
    </tr>
<?
		}
                
if($_SERVER['REQUEST_METHOD'] != 'POST') {//Só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
<?        
    exit;
}

$valor_dolar_dia        = genericas::moeda_dia('dolar');
$valor_euro_dia         = genericas::moeda_dia('euro');

$txt_data_faturado_mes	= $data_inicial;
$txt_data_faturavel_mes	= $data_final;

/*Aqui eu busco tudo o que foi faturado nas respectivas Datas 

Nesse bolo de Notas, NÃO estão inclusas as Devoluções ...*/
$sql = "SELECT `id_nf` 
        FROM `nfs` 
        WHERE `data_emissao` BETWEEN '$txt_data_faturado_mes' AND '$txt_data_faturavel_mes' ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
if($linhas_nfs == 0) {//Não encontrou nenhum Item ...
    $vetor_nfs[] = 0;
}else {//Encontrou pelo menos 1 Item ...
    for($i = 0; $i < $linhas_nfs; $i++) $vetor_nfs[] = $campos_nfs[$i]['id_nf'];
}

/*******************************Sem Impostos*******************************/
$sql = "SELECT ed.`id_empresa_divisao`, ed.`id_empresa`, nfs.`id_nf`, nfs.`id_empresa`, nfsi.`qtde`, nfsi.`qtde_devolvida`, nfsi.`valor_unitario` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        WHERE nfsi.`id_nf` IN (".implode(',', $vetor_nfs).") ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
if($linhas_nfs > 0) {
    for($i = 0; $i < $linhas_nfs; $i++) {
        /*A partir daqui estão inclusos todos os Tipos de NF, tudo o que foi faturado "Devoluções também" totalizando um Valor Bruto Faturado, 
        ao lado já em sequência será abatido o $valor_devolvido calculado acima em cima desse Valor Bruto Faturado ...*/
        $valor_faturado = ($campos_nfs[$i]['qtde'] * $campos_nfs[$i]['valor_unitario']);
        if($campos_nfs[$i]['id_empresa'] == 1) {//Albafer ...
            $total_divisoes_s_imp_alba+= $valor_faturado;
        }else {//Tool Master ...
            $total_divisoes_s_imp_tool+= $valor_faturado;
        }
        $total_divisoes_s_imp+= $valor_faturado;
        //Guardo o Total Faturado aqui nessa variável por Empresa Divisão, porque a mesma será utilizada + abaixo ...
        $vetor_empresa_divisao[$campos_nfs[$i]['id_empresa_divisao']]+= $valor_faturado;
    }
}

/*******************************Com Impostos*******************************/
//Faço busca de dados nos campos de duplicatas da Tabela nfs porque essas já possuem o Imposto facilitando muito ...
$sql = "SELECT `id_nf` 
        FROM `nfs` 
        WHERE `id_nf` IN (".implode(',', $vetor_nfs).") 
        ORDER BY `id_nf` ";
$campos_nfs = bancos::sql($sql);
$linhas_nfs = count($campos_nfs);
for($i = 0; $i < $linhas_nfs; $i++) {
    $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nfs[$i]['id_nf'], 'NF');
    $total_divisoes_c_imp+= $calculo_total_impostos['valor_total_nota'];
}

//Busca de todas as Empresas Divisões que estão cadastradas no Sistema ...
$sql = "SELECT `id_empresa_divisao`, `razaosocial` 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
$campos_empresa_divisao = bancos::sql($sql);
$linhas_empresa_divisao = count($campos_empresa_divisao);
if($linhas_empresa_divisao > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Empresa Divis&atilde;o
        </td>
        <td>
            Faturado <font color='red'>**</font>&nbsp;R$
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_empresa_divisao; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_empresa_divisao[$i]['razaosocial'];?>
        </td>
        <td align='right'>
            <?=number_format($vetor_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?		
    }
?>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <font color='brown' size='2'>
                <b>TOTAL DA(S) DIVISÃO(ÕES) S/ DEVOLUÇÃO(ÕES) S/ IMPOSTOS:</b>
            </font>
        </td>
        <td bgcolor='#CECECE' align='right'>
            <font color='brown' size='2'>
                <b><?=number_format($total_divisoes_s_imp, 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <font color='brown' size='2'>
                <b>TOTAL DA(S) DIVISÃO(ÕES) S/ DEVOLUÇÃO(ÕES) C/ IMPOSTOS:</b>
            </font>
        </td>
        <td bgcolor='#CECECE' align='right'>
            <font color='brown' size='2'>
                <b><?=number_format($total_divisoes_c_imp, 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow' size='2'><b>Faturado Albafer (s/ Impostos) =></b>
                R$ <?=number_format($total_divisoes_s_imp_alba, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='yellow' size='2'><b>Faturado Tool Master (s/ Impostos) =></b>
                R$ <?=number_format($total_divisoes_s_imp_tool, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</form>
<pre>
    *** Este relatório leva em conta todas as NFs com data de emissão dentro do período, independente de status.
</pre>
<br>
<?
}

//Relatório de Pedidos Pendentes ...
$GLOBALS['txt_data_faturado_mes']   = $txt_data_inicial;
$GLOBALS['txt_data_faturavel_mes']  = $txt_data_final;
$GLOBALS['nivel']                   = '../pedidos_pendentes/';
$GLOBALS['nivel_emitidos']          = '../pedidos_emitidos/';
$GLOBALS['dt_emissao']              = 'não exibir notas vazias no relatorio de pedidos pendentes';
    
require('../pedidos_pendentes/pedidos_pendentes.php');

//Relatório de Pedidos Emitidos por Família ...
if(empty($txt_data_inicial)) {
    $datas              = genericas::retornar_data_relatorio();
    $txt_data_inicial   = $datas['data_inicial'];
    $txt_data_final     = $datas['data_final'];
}
$data_inicial           = data::datatodate($txt_data_inicial, '-');// ja tem que ir convertido em formato americano
$data_final             = data::datatodate($txt_data_final, '-');// ja tem que ir convertido em formato americano
?>
<form name='form2' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relat&oacute;rio de Pedidos Emitidos
        </td>
    </tr>
	<?require('../pedidos_emitidos/rel_familias.php');?>
</table>
<br>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relatório de Pedidos Emitidos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Divisões
        </td>
        <td>
            Percentagens
        </td>
        <td>
            Totais R$
        </td>
    </tr>
<?
        unset($vetor_empresa_divisao);//Destruo essa variável p/ não herdar valores que foram processados e armazenados acima ...
        
        //Tenho que zerar essas variáveis para não herdar os valores acima ...
        $total_geral        = 0;
        $total_albafer      = 0;
        $total_tool_master  = 0;
	
        //Busco todos os Pedidos Emitidos dentro do período digitado pelo Usuário, que estejam liberados ...
        $sql = "SELECT pvi.`id_produto_acabado`, 
                SUM(IF(c.`id_pais` = '31', pvi.`qtde` * pvi.`preco_liq_final`, pvi.`qtde` * pvi.`preco_liq_final` * $valor_dolar_dia)) AS total_pedidos_emitidos 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                WHERE pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.`liberado` = '1' 
                GROUP BY pvi.`id_produto_acabado` 
                ORDER BY pvi.`id_produto_acabado` ";
	$campos_pedidos_vendas  = bancos::sql($sql);
	$linhas_pedidos_vendas  = count($campos_pedidos_vendas);
        for($i = 0; $i < $linhas_pedidos_vendas; $i++) {
            //Verifico qual é a "Empresa" e "Empresa Divisão" que esse PA do Loop pertence ...
            $sql = "SELECT ed.id_empresa, ged.id_empresa_divisao 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.`id_produto_acabado` = '".$campos_pedidos_vendas[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_detalhes = bancos::sql($sql);
            $vetor_empresa_divisao[$campos_detalhes[0]['id_empresa_divisao']]+= $campos_pedidos_vendas[$i]['total_pedidos_emitidos'];
            if($campos_detalhes[0]['id_empresa'] == 1) {//Albafer ...
                $total_albafer+= $campos_pedidos_vendas[$i]['total_pedidos_emitidos'];
            }else {//Tool Master ...
                $total_tool_master+= $campos_pedidos_vendas[$i]['total_pedidos_emitidos'];
            }
            $total_geral+= $campos_pedidos_vendas[$i]['total_pedidos_emitidos'];
	}
	
        //Busco todas as Empresas Divisões cadastradas no Sistema ...
	$sql = "SELECT id_empresa_divisao, razaosocial 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ORDER BY razaosocial ";
	$campos_empresas_divisoes = bancos::sql($sql);
	$linhas_empresas_divisoes = count($campos_empresas_divisoes);
	
	for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
        <td align='center'>
            <?=number_format(($vetor_empresa_divisao[$campos_empresas_divisoes[$i]['id_empresa_divisao']] / $total_geral * 100), 2, ',', '.');?> %
        </td>
        <td align='right'>
            R$ <?=number_format($vetor_empresa_divisao[$campos_empresas_divisoes[$i]['id_empresa_divisao']], 2, ',', '.');?>
        </td>
    </tr>
<?
	}
	if(empty($total_geral) || $total_geral == 0) $total_geral = 0.001;
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresas
        </td>
        <td>
            Percentagens
        </td>
        <td align='right'>
            Totais R$
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='green'>ALBAFER</font>
        </td>
        <td align='center'>
            <?=number_format(($total_albafer / $total_geral * 100), 2, ',', '.');?> %
        </td>
        <td align='right'>
            R$ <?=number_format($total_albafer, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='green'>TOOL MASTER</font>
        </td>
        <td align='center'>
            <?=number_format(($total_tool_master / $total_geral * 100), 2, ',', '.');?> %
        </td>
        <td align='right'>
            R$ <?=number_format($total_tool_master, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='right'>
        <td colspan='3'>
            <font color='red'>
                <b>Total R$: </b><?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font color='red'>
                <b>Valor Dolar dia R$: </b><?=number_format($valor_dolar_dia, 4, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<pre> <font color="red">** Neste Relatório não consta os seguintes cálculos:</font>
<font color="blue">
 - Notas Fiscais sem Data de Emissão
 - Frete / IPI
 - Despesas Acessórias
 - Desconto de PIS + Cofins e ICMS = 7%
</font>
</pre>
</td>
</tr>
</table>
</form>
</body>
</html>
<!-- Relatório de Estoque P.A. -->
<html>
<body>
<form name='form3' method='post' action=''>
<table width='975' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
	$sql = "SELECT `data_atualizacao` 
                FROM `rel_estoques` 
                WHERE `status` = '1' ORDER BY divisao LIMIT 1 ";
	$campos_rel_estoque = bancos::sql($sql);
	$data_relatorio     = $campos_rel_estoque[0]['data_atualizacao'];
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan="7">
			<font color='#FFFFFF' size='-1'>
				Relat&oacute;rio de Estoque P.A. - Atualizado em <font color="yellow"><?=data::datetodata(substr($data_relatorio, 0, 10),'/').' '.substr($data_relatorio, 11, 8);?></font>
			</font>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
	  <td>Empresa Divis&atilde;o </td>
	  <td>Qtde E. Real </td>
	  <td>Total R$</td>
    </tr>
<?	
	$sql = "SELECT * 
                FROM `rel_estoques` 
		WHERE `status` = '1' ORDER BY divisao ";
	$campos_rel_estoque = bancos::sql($sql);
	$linhas_rel=count($campos_rel_estoque);
	for($i=0;$i<$linhas_rel;$i++) {
		$qtde_est_real	= $campos_rel_estoque[$i]['qtde_est_real'];	
		$total_reais	= $campos_rel_estoque[$i]['total_reais'];
		if(strtoupper($campos_rel_estoque[$i]['divisao'])!="PINOS") {
			$soma_qtde_est_real+=$qtde_est_real;
			$soma_total_reais+=$total_reais;
			$divisao=$campos_rel_estoque[$i]['divisao'];
		} else {
			$divisao=$campos_rel_estoque[$i]['divisao']." <font color='darkblue'>(Cálculo incluso na Heinz)</font>";
		}
?>
	<tr class='linhanormal' align="left">
	  <td align="left"><b><font color="blue">
      <?=$divisao;?>
      </font></b></td>
	  <td align="right"><b><font color="blue">
      <?=segurancas::number_format($qtde_est_real,2,".");?>
</font></b></td>
	  <td align="right"><b><font color="#ff9900">
	    <?=segurancas::number_format($total_reais,2,".");?>
	  </font></b></td>
    </tr>
<? } ?>
	<tr class='linhanormal' align="left">
	  <td align="left"><font color='red' size='2'><b>Total:</b></font></td>
	  <td align="right"><span class="style3"><font color='red' size='2'>
	    <?=segurancas::number_format($soma_qtde_est_real,2,".");?>
	  </font></span></td>
	  <td align="right"><span class="style3"><font color='red' size='2'>
	    <?=segurancas::number_format($soma_total_reais,2,".");?>
	  </font>
	  </span></td>
    </tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan="3">
			<input name="cmd_atualizar" type="submit" class='botao' id="cmd_atualizar" title='Atualizar Relatório' value="Atualizar Relatório">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<!--  Relatorio de produção -->
<html>
<body>
<form name='form4' method='post' action=''>
<input type='hidden' name='passo' value='1'>
<table border="0" width="975" align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
	  <td colspan="11">Relat&oacute;rio de Produção do P.A. <font color='#FFFFFF' size='-1'>&nbsp;</font></td>
    </tr>
<?
		$sql = "SELECT id_empresa_divisao, razaosocial 
                        FROM `empresas_divisoes` 
			ORDER BY razaosocial ";
		$campos_empresa_divisao = bancos::sql($sql);
		$linhas_ed              = count($campos_empresa_divisao);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$linhas_ed              = 0;//esta linha é para parar a produção
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	for($i=0;$i<$linhas_ed;$i++) { // ESTA PARTE È DA EMPRESA DIVISÂO
	$sql = "Select f.id_familia, f.nome 
			from empresas_divisoes ed 
			inner join gpas_vs_emps_divs ged on ged.id_empresa_divisao=ed.id_empresa_divisao 
			inner join grupos_pas gpa on gpa.id_grupo_pa=ged.id_grupo_pa 
			inner join familias f on f.id_familia=gpa.id_familia 
			where f.ativo=1 and gpa.ativo=1 and ed.id_empresa_divisao=".$campos_empresa_divisao[$i]['id_empresa_divisao']."
			group by f.id_familia 
			order by f.nome ";
	$campos_familia = bancos::sql($sql);
	$linhas_familia = count($campos_familia);
	for($x=0;$x<$linhas_familia;$x++) {// ESTA PARTE È DA FAMILIA
	$sql = "Select gpa.id_grupo_pa, gpa.nome
			from grupos_pas gpa 
			inner join gpas_vs_emps_divs ged on ged.id_grupo_pa=gpa.id_grupo_pa
			inner join familias f on f.id_familia=gpa.id_familia 
			where f.ativo=1 and gpa.ativo=1 and f.id_familia = ".$campos_familia[$x]['id_familia']." and ged.id_empresa_divisao=".$campos_empresa_divisao[$i]['id_empresa_divisao']." 
			group by gpa.id_grupo_pa 
			order by gpa.nome ";
	$campos_grupo = bancos::sql($sql);
	$linhas_grupo = count($campos_grupo);
	for($y = 0; $y < $linhas_grupo; $y++) {//ESTA PARTE È DO GRUPO
                $sql_rev = "SELECT SUM(nfeh.qtde_entregue) AS qtde_entregue, SUM(nfeh.qtde_entregue * (pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1 - ged.desc_base_b_nac / 100) * (1 + ged.acrescimo_base_nac / 100)) * ged.desc_medio_pa) AS total_revenda_rs 
                            FROM `nfe` 
                            INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pa.`ativo` = '1' 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` = '".$campos_grupo[$y]['id_grupo_pa']."' AND ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                            WHERE nfe.`data_entrega` BETWEEN '$data_inicial' AND '$data_final' GROUP BY ged.id_grupo_pa ";

		$sql_ind = "SELECT SUM(bmp.qtde) AS qtde_produzida,
                            SUM(bmp.qtde * (pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1 - ged.desc_base_b_nac/ 100) * (1+ged.acrescimo_base_nac /100)) * ged.desc_medio_pa) total_industrial_rs 
                            FROM `baixas_manipulacoes_pas` bmp 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = bmp.`id_produto_acabado` 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            WHERE SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' AND eae.`acao` = 'E' 
                            AND ged.`id_grupo_pa` = '".$campos_grupo[$y]['id_grupo_pa']."' 
                            AND ged.`id_empresa_divisao` = ".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                            GROUP BY ged.id_grupo_pa ";
		$campos_rev = bancos::sql($sql_rev);
		$campos_ind = bancos::sql($sql_ind);
		$parcial_rev=$campos_rev[0]['total_revenda_rs'];
		$total_produzida_rs+=$parcial_rev;
		$parcial_ind=$campos_ind[0]['total_industrial_rs'];
		$total_revenda_rs+=$parcial_ind;
		$total_qtde_parcial=$campos_rev[0]['qtde_entregue']+$campos_ind[0]['qtde_produzida'];
		$total_qtde_geral+=$total_qtde_parcial;
		$total_qtde_geral_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']]+=$total_qtde_parcial;
		$total_parcial_rs=$campos_rev[0]['total_revenda_rs']+$campos_ind[0]['total_industrial_rs'];
		$total_geral_rs+=$total_parcial_rs;
		$total_divisao_rev[$campos_empresa_divisao[$i]['id_empresa_divisao']]+=$campos_rev[0]['total_revenda_rs'];
		$total_divisao_ind[$campos_empresa_divisao[$i]['id_empresa_divisao']]+=$campos_ind[0]['total_industrial_rs'];

		if($campos_familia[$x]['id_familia']==2) {
			$pinos_ind+=$campos_ind[0]['total_industrial_rs'];
			$pinos_rev+=$campos_rev[0]['total_revenda_rs'];
			$tot_pinos_qtde_parcial+=$campos_rev[0]['qtde_entregue']+$campos_ind[0]['qtde_produzida'];
			$valor_pinos+=$campos_rev[0]['total_revenda_rs']+$campos_ind[0]['total_industrial_rs'];
		}
	} //fim do for do grupo
	} //fim do for da familia
	} //fim do for da divisão
?>
	<tr class='linhadestaque' align="left">
	  <td align='center'><font size='2'><b>Empresa Divis&atilde;o</b></font></td>
	  <td colspan="2" align='center'><font color="blue"><b>Revenda </b>R$:</font></td>
	  <td colspan="2" align='center'><font color="blue"><b>Industrializado</b> R$:</font></td>
	  <td align='center'><span class="style3"><font color='red' size='2'>QTDE
	  </font></span></td>
	  <td align='center'><span class="style3"><font color='red' size='2'>VALOR
            <font color='red' size='2'>R$:</font> </font>
	  </span></td>
    </tr>
<?	for($i=0;$i<$linhas_ed;$i++) { ?>
	<tr class='linhanormal'>
	  <td align="left"><b><font color="blue">
	    <?=$campos_empresa_divisao[$i]['razaosocial'];?>
	  </font></b></td>
	  <td colspan="2" align="right" ><?=segurancas::number_format($total_divisao_rev[$campos_empresa_divisao[$i]['id_empresa_divisao']],2,".");?></td>
	  <td colspan="2" align="right"><?=segurancas::number_format($total_divisao_ind[$campos_empresa_divisao[$i]['id_empresa_divisao']],2,".");?></td>
	  <td align="right"><font color="red"><?=segurancas::number_format($total_qtde_geral_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']],2,".");?></font></td>
	  <td align="right"><font color="red"><?=segurancas::number_format($total_divisao_rev[$campos_empresa_divisao[$i]['id_empresa_divisao']]+$total_divisao_ind[$campos_empresa_divisao[$i]['id_empresa_divisao']],2,".");?></font></td>
    </tr>
<? } ?>


	<tr class='linhanormal'>
          <td align="left"><b><font color="green">
		PINOS (Já incluso na divisão HEINZ)
	  </font></b></td>
	  <td colspan="2" align="right"><font color="green"><?=segurancas::number_format($pinos_rev,2,".");?></font></td>
	  <td colspan="2" align="right"><font color="green"><?=segurancas::number_format($pinos_ind,2,".");?></font></td>
	  <td align="right"><font color="green"><?=segurancas::number_format($tot_pinos_qtde_parcial,2,".");?></font></td>
	  <td align="right"><font color="green"><?=segurancas::number_format($valor_pinos,2,".");?></font></td>
    </tr>


	<tr class='linhanormal'>
	  <td align="left"><b><font color='red' size='2'><b>Totais:</b></font></b></td>
      <td colspan="2" align="right" ><font color="red">
        <?	echo segurancas::number_format($total_produzida_rs,2,".");?>
      </font></td>
      <td colspan="2" align="right"><font color="red"><?echo segurancas::number_format($total_revenda_rs,2,".");?></font></td>
      <td align="right"><span class="style3"><font color='red' size='2'>
        <?=segurancas::number_format($total_qtde_geral,2,".");?>
      </font></span></td>
      <td align="right"><span class="style3"><font color='red' size='2'>
        <?=segurancas::number_format($total_geral_rs,2,".");?>
      </font></span></td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan="7">
			&nbsp;
		</td>
	</tr>
</table>
</form>
</body>
 <br>
 - As Quantidades <b>Revenda</b> são das NF´s de entrada em compras dos PRAC´s (PI Vs PA).
 <br>
 - A Quantidade <b>Industrializados</b> são das <i>Entradas de Materiais e Estoque</i> do Manipular estoque.
 <br>
 - Não Levamos em conta a <b> Operação de Custo</b>!!!
</html>
<!-- Contas  a Pagar e a receber -->
<?
    $txt_data_inicial_temp  = $txt_data_inicial;
    $txt_data_final_temp    = $txt_data_final;
    $txt_data_inicial       = date('d/m/Y');
    $txt_data_final         = data::adicionar_data_hora($txt_data_final, 30);
    $nao_redeclarar         = 1;
    require('../../../financeiro/relatorio/a_pagar/relatorio.php');
?>
<table border='0' width='975' align='center' cellspacing ='1' cellpadding='1'>
    <tr>
        <td>&nbsp;</td>
    </tr>
</table><!--Só para pular linhar pois o BR dá errado-->
<?
	require('../../../financeiro/relatorio/a_receber/relatorio.php');
	//Volto as data aos valores normais ...
	$txt_data_inicial   = $txt_data_inicial_temp;
	$txt_data_final     = $txt_data_final_temp;
/********************************************Relatório de caixa Início********************************************/
$mensagem[1]        = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia    = genericas::moeda_dia('dolar');
$valor_euro_dia     = genericas::moeda_dia('euro');
?>
<html>
<style type="text/css">
<!--
.style1 {font-size: 9px}
.style2 {font-weight: bold}
-->
</style>
<body>
<form name='form5' method="post" action='' onsubmit='return validar_faturamento()'>
<input type='hidden' name='passo' value='1'>
<table width='975' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="8">
			Relat&oacute;rio de Caixa
		</td>
	</tr>
<?
if($_SERVER['REQUEST_METHOD']!="POST") { //só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer
exit("<tr class='linhacabecalho' align='center'>
			<td colspan='8'>&nbsp;</td>
		</tr>
	</table>");
}
	$sql = "SELECT * 
                FROM `rel_caixas` ";
	$campos = bancos::sql($sql);
	$valor_dolar_dia = genericas::moeda_dia('dolar');
	$valor_euro_dia = genericas::moeda_dia('euro');
	//$txt_data_faturado_mes=date('Y-m-01');// preciso desta data para achar no sql abaixo o faturamento do mes
	//$txt_data_faturavel_mes=date('Y-m-t');
	$txt_data_faturado_mes	= $data_inicial;
	$txt_data_faturavel_mes	= $data_final;
?>
	<tr class='linhadestaque'>
		<td colspan="2" align='center'>Dados Financeiros </td>
		<td colspan="2" align='center'>Outros Dados </td>
	</tr>
	<tr class='linhanormal' align="left">
		<td width="272">Caixa Alba</td>
		<td width="184"><?="R$ ".number_format($campos[0]['caixa_alba'], 2, ',', '.');?></td>
		<td width="319">Estoque A&ccedil;o</td>
		<td width="187"><?="R$ ".number_format($campos[0]['estoque_aco'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Caixa Tool </td>
		<td><?="R$ ".number_format($campos[0]['caixa_tool'], 2, ',', '.');?></td>
		<td>Estoque Rolamento</td>
		<td><?="R$ ".number_format($campos[0]['estoque_rolamento'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Caixa C2 </td>
		<td><?="R$ ".number_format($campos[0]['caixa_c2'], 2, ',', '.');?></td>
		<td>Empréstimo R/S <span class="style1">(n&atilde;o somado na Previs&atilde;o daqui &agrave; 30 dias)</span></td>
		<td><?="R$ ".number_format($campos[0]['emprestimo_rs'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Câmbios Liberados </td>
		<td>
		<?
			$cambios_liberados_rs = $campos[0]['cambio_liberado'] * $valor_dolar_dia;
			echo "R$ ".number_format($cambios_liberados_rs, 2, ',', '.');
			echo " / U$ ".number_format($campos[0]['cambio_liberado'], 2, ',', '.');
		?>
		</td>
		<td>Total de Empréstimos à Pagar</td>
		<td><?="R$ ".number_format($campos[0]['total_emprestimos_pagar'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>
			U$ em Especie
		</td>
		<td>
		<?
			$calculo_dolar_especie_paralelo = $campos[0]['dolar_especie'] * $campos[0]['dolar_paralelo'];
			echo "R$ ".number_format($calculo_dolar_especie_paralelo, 2, ',', '.');
			echo " / U$ ".number_format($campos[0]['dolar_especie'], 2, ',', '.');
		?>
		</td>
		<td>Valor Mensal de Empréstimo</td>
		<td><?="R$ ".number_format($campos[0]['valor_mensal_emprestimo'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><b>Saldo de Caixa</b></td>
		<td><b>
		<?
			$saldo_caixa = ($campos[0]['caixa_alba']+$campos[0]['caixa_tool']+$campos[0]['caixa_c2']+$cambios_liberados_rs+$calculo_dolar_especie_paralelo);
			echo "R$ ".number_format($saldo_caixa, 2, ',', '.');
		?>
		</b></td>
		<td>Valor Mensal do Consórcio</td>
		<td><?="R$ ".number_format($campos[0]['valor_mensal_consorcio'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Dupl. &agrave; Rec. SGD </td>
		<td><?="R$ ".number_format($campos[0]['dupl_sgd_s_prot'], 2, ',', '.');?></td>
		<td colspan="2" align='center' class='linhadestaque'>Resumo Geral</td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Dupl. &agrave; Rec. ALBA</td>
		<td><?="R$ ".number_format($campos[0]['dupl_alba_s_prot'], 2, ',', '.');?></td>
		<td>Saldo K2 (At&eacute; hoje) </td>
		<td><?="R$ ".number_format($campos[0]['saldo_k2'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Dupl. &agrave; Rec. TOOL. </td>
		<td><?="R$ ".number_format($campos[0]['dupl_tool_s_prot'], 2, ',', '.');?></td>
		<td>Saldo Carine (At&eacute; hoje)</td>
		<td><?="R$ ".number_format($campos[0]['saldo_carine'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><b>Total Dupl. Receb. at&eacute; 30 dias</b></td>
		<td><b>
	    <?
			$total_dupli_receb_30_dias=$campos[0]['dupl_sgd_s_prot']+$campos[0]['dupl_alba_s_prot']+$campos[0]['dupl_tool_s_prot'];
			echo "R$ ".number_format($total_dupli_receb_30_dias, 2, ',', '.');?>
		</b></td>
		<td>Cap. Giro C/ Caixa daqui &agrave; 30 dias</td>
		<?
			$capital_giro_sem_caixa = $campos[0]['dupl_sgd_s_prot'] + $campos[0]['dupl_alba_s_prot'] + $campos[0]['dupl_tool_s_prot'] + $campos[0]['emprestimo_func'];
			$capital_giro_com_caixa = $saldo_caixa + $capital_giro_sem_caixa;
		?>
		<td><?="R$ ".number_format($capital_giro_com_caixa, 2, ',', '.');?></td>
    </tr>
	<tr class='linhanormal' align="left">
		<td>Empréstimo Func. </td>
		<td><?="R$ ".number_format($campos[0]['emprestimo_func'], 2, ',', '.');?></td>
		<td>Dup. à Receber &gt; 30 dias </td>
		<td>
		<?
			$dupli_areceber_maior_30=$campos[0]['dup_areceber_maior_30'];
			echo "R$ ".number_format($dupli_areceber_maior_30, 2, ',', '.');?>
		</td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><b>Cap. Giro S/ Caixa daqui &agrave; 30 dias</b></td>
		<td><b><?="R$ ".number_format($capital_giro_sem_caixa, 2, ',', '.');?></b></td>
		<td><b>A&ccedil;o + Rolamento</b></td>
		<?
			$aco_rolamento = $campos[0]['estoque_aco'] + $campos[0]['estoque_rolamento'];
		?>
		<td><b><?="R$ ".number_format($aco_rolamento, 2, ',', '.');?></b></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><b>Cap. Giro C/ Caixa daqui &agrave; 30 dias</b></td>
		<td><b><?="R$ ".number_format($capital_giro_com_caixa, 2, ',', '.');?></b></td>
		<td>Produtos Acabados</td>
		<td><b><?="R$ ".number_format($campos[0]['produtos_acabados'], 2, ',', '.');?></b></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>Semi Acab. Em Prod. (Estimado) </td>
		<td><?="R$ ".number_format($campos[0]['semi_acabado_prod'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><p>Atrasados &lt; 60 dias (<span class="style1">Inclusos nas Dupl. &agrave; Rec.</span>)</p>	    </td>
		<td><?="R$ ".number_format($campos[0]['atrasados_menor_60_dias'], 2, ',', '.');?> </td>
		<td>Importa&ccedil;&otilde;es</td>
		<td><?="R$ ".number_format($campos[0]['importacoes'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Atrasados > 60 e < 180 dias (<span class="style1">N&atilde;o Inclusos no Cap. Giro</span>)</td>
		<td><?="R$ ".number_format($campos[0]['atrasados_maior_60_dias'], 2, ',', '.');?></td>
		<td>Total Pago Consórcio-Taxa Adm</td>
		<td><?="R$ ".number_format($campos[0]['total_pago_consorcio_taxa_adm'], 2, ',', '.');?></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><strong>Total de Atrasos </strong></td>
		<td><b><?="R$ ".number_format($campos[0]['atrasados_menor_60_dias']+$campos[0]['atrasados_maior_60_dias'], 2, ',', '.');?></b></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
    </tr>
	<tr class='linhanormal' align="left">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><strong>Total Patrimônio Líq.</strong></td>
		<?
			$patrimonio_liquido = $campos[0]['saldo_k2'] + $campos[0]['saldo_carine'] + $capital_giro_com_caixa + $dupli_areceber_maior_30 +$aco_rolamento + $campos[0]['produtos_acabados'] + $campos[0]['semi_acabado_prod'] + $campos[0]['importacoes'] + $campos[0]['total_pago_consorcio_taxa_adm'];
		?>
		<td><b><?="R$ ".number_format($patrimonio_liquido, 2, ',', '.');?></b></td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Dup. à Receber &gt; 30 dias </td>
		<td><?="R$ ".number_format($campos[0]['dup_areceber_maior_30'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr class='linhanormal' align="left">
		<td><b>Cap. Giro c/ Caixa c/ Dupl. Receb. <span class="style1">&gt; 30 dias </span></b></td>
		<td><b><?="R$ ".number_format($capital_giro_com_caixa+$campos[0]['dup_areceber_maior_30'], 2, ',', '.');?></b></td>
		<td>Semi Acab. Comp. (Sem Dados) </td>
		<td><?="R$ ".number_format($campos[0]['semi_acabado_comp'], 2, ',', '.');?></td>
    </tr>
	<tr class='linhanormal' align="left">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr class='linhanormal' align="left">
		<td>Contas à Pagar Alba: </td>
		<td><?="R$ ".number_format($campos[0]['contas_apagar_alba'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td>Contas à Pagar Tool: </td>
		<td><?="R$ ".number_format($campos[0]['contas_apagar_tool'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td>Contas à Pagar Grupo: </td>
		<td><?="R$ ".number_format($campos[0]['contas_apagar_grupo'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td>Contas à Pagar Sandra: </td>
		<td><?="R$ ".number_format($campos[0]['contas_apagar_sandra'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td>Diferença de Contas à Pagar Carine:</td>
		<td><?="R$ ".number_format($campos[0]['dif_contas_apagar_carine'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td>Diferença de Contas à Pagar K2 </td>
		<td><?="R$ ".number_format($campos[0]['dif_contas_apagar_k2'], 2, ',', '.');?></td>
		<td>&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Total Contas à Pagar:</b></td>
		<?$total_contas_apagar = $campos[0]['contas_apagar_alba'] + $campos[0]['contas_apagar_tool'] + $campos[0]['contas_apagar_grupo'] + $campos[0]['contas_apagar_sandra'] + $campos[0]['dif_contas_apagar_carine'] + $campos[0]['dif_contas_apagar_k2'];?>
		<td><b><?="R$ ".number_format($total_contas_apagar, 2, ',', '.');?></b></td>
		<td><font color="blue">Valor Dólar do Dia R$: </font></td>
		<td align="left"><?="R$ ".number_format($valor_dolar_dia, 4, ',', '.');?></td>
	</tr>
	<tr class='linhanormal'>
	  <td>&nbsp;</td>
	  <td>&nbsp;</td>
	  <td><font color="blue">Valor Euro do Dia R$: </font></td>
	  <td align="left"><?="R$ ".number_format($valor_euro_dia, 4, ',', '.');?></td>
    </tr>
	<tr class='linhanormal'>
		<td><b>Previs&atilde;o daqui &agrave; 30 dias</b> <span class="style1">(Cap. Giro c/ caixa - Conta &agrave; Pagar) </span></td>
		<?$previsao_fim_mes = $capital_giro_com_caixa - $total_contas_apagar;?>
		<td><b><?="R$ ".number_format($previsao_fim_mes, 2, ',', '.');?></b></td>
		<td>
			<font color="blue">Valor do Dólar Paralelo R$: 
			</font>
		</td>
		<td align="left"><?="R$ ".number_format($campos[0]['dolar_paralelo'], 2, ',', '.');?>
		</td>
	</tr>
	<tr class='linhadestaque' align='center'>
		<td colspan="4">&nbsp;</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan="6">
			<input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title="Atualizar Relatório" id="cmd_atualizar" class='botao'>
		</td>
	</tr>
</table>
<pre>&nbsp;</pre>
</form>
</body>
</html>