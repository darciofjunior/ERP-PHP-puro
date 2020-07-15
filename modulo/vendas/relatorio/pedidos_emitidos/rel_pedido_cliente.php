<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');
$mensagem[1]        = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia    = genericas::moeda_dia('dolar');

$id_cliente         = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];

if(!empty($data_inicial)) {
    $txt_data_inicial   = data::datetodata($data_inicial, '/');
    $txt_data_final     = data::datetodata($data_final, '/');
}
?>
<html>
<head>
<title>.:: Relatório de Pedidos Emitidos do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Relat&oacute;rio de Pedido(s) Emitido(s) do Cliente
            <font color='yellow'>
            <?
                //Busca do nome do Cliente ...
                $sql = "SELECT CONCAT(nomefantasia, ' (', razaosocial, ')') AS cliente 
                        FROM `clientes` 
                        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                echo '<br/>'.$campos_cliente[0]['cliente'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='6'>
            <p>Data Inicial: 
            <?
                $data_inicial   = data::datatodate($txt_data_inicial, '-');
                $data_final     = data::datatodate($txt_data_final, '-');
                if(empty($txt_data_inicial))    $txt_data_inicial = data::adicionar_data_hora(date('d-m-Y'), -365);
                if(empty($txt_data_final))      $txt_data_final = date('t/m/Y');
                $diff_dias  = data::diferenca_data(data::datatodate($txt_data_inicial,"-"), data::datatodate($txt_data_final,"-")); // 90 dias + - 
                $diff_dias  = (intval($diff_dias[0]) / 30);
                if($diff_dias < 0.01) $diff_dias = 0.01;
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border="0" alt="Calend&aacute;rio Normal" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"  style='cursor:hand'> &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border="0" alt="Calend&aacute;rio Normal" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')" style='cursor:hand'>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
	$sql = "SELECT ed.`razaosocial` AS razaosocial_divisao, ed.`id_empresa_divisao`, pv.`id_pedido_venda`, 
                pv.`data_emissao`, pv.`finalidade`, c.`id_pais`, pv.`vencimento1`, pv.`vencimento2`, 
                pv.`vencimento3`, pv.`vencimento4`, SUM((pvi.`qtde` - pvi.`qtde_devolvida`) * pvi.preco_liq_final) AS total 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.`id_cliente` = '$id_cliente' 
                AND `liberado` = '1' 
                GROUP BY pv.`id_pedido_venda`, ed.`id_empresa_divisao` ORDER BY ed.`razaosocial`, pv.`id_pedido_venda` DESC ";
	$campos = bancos::sql($sql);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Divis&atilde;o
        </td>
        <td>
            N&ordm; do Pedido
        </td>
        <td>
            Data de Emiss&atilde;o
        </td>
        <td>
            Forma de Pagamento
        </td>
        <td>
            Total  R$
        </td>
        <td>
            MMV R$
        </td>
    </tr>
<?
    //esta linha precisa existir para saber quando vai mudar de divisão no rel ...
    $id_divisao = $campos[0]['id_empresa_divisao'];
    $linhas     = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        if($campos[$i]['id_pais'] != 31) {//Fora do País ...
            $total_parcial = $campos[$i]['total'] * $valor_dolar_dia;
        }else {//Brasil ...
            $total_parcial = $campos[$i]['total'];
        }
        if($id_divisao == $campos[$i]['id_empresa_divisao']) {//Mesma Divisão ...
            $total_divisao+= $total_parcial;//Na mesma Divisão ...
        }else {//Nova Divisão ...
            $id_divisao = $campos[$i]['id_empresa_divisao'];//O sistema atribui a Nova Divisão do Loop ...
?>
    <tr class='linhanormal'>
        <td align='right' colspan='6'>
            <font color='blue'>
                Total da <?=$campos[$i - 1]['razaosocial_divisao'].': '.number_format($total_divisao, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
            $total_divisao = $total_parcial;
        }
        switch ($campos[$i]['id_empresa_divisao']) { /// este switch serve somente para fazer a somatoria e achar a porcetagem de cada empresa e cada divisao
            case 1://Cabri
                $cabri+= $total_parcial;
            break;
            case 2://Heinz
                $heinz+= $total_parcial;
            break;
            case 3://Warrior
                $warrior+= $total_parcial;
            break;
            case 4:// Tool Master
                $tool+= $total_parcial;
            break;
            case 5: //NVO
                $nvo+= $total_parcial;
            break;
        }
        $total_geral+= $total_parcial;
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['razaosocial_divisao'];?>
        </td>
        <td>
            <?=$campos[$i]['id_pedido_venda'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
<?
	$prazo_faturamento = '';//Limpo essa variável p/ não herdar valores do Loop Anterior ...
  	if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
	if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
	if($campos[$i]['vencimento2'] > 0) {
            $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
	}else {
            $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
	}
//Verifico o Tipo de Nota pela Empresa do Pedido ...
	if($campos[$i]['id_empresa'] == 4) {
            $rotulo_sgd = ' - SGD';
	}else {
            $rotulo_sgd = ' - NF';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
            if($campos[$i]['finalidade'] == 'C') {
                $finalidade = 'CONSUMO';
            }else if($campos[$i]['finalidade'] == 'I') {
                $finalidade = 'INDUSTRIALIZAÇÃO';
            }else {
                $finalidade = 'REVENDA';
            }
            $rotulo_sgd.= '/'.$finalidade;
	}
        $prazo_faturamento.=$rotulo_sgd;
        echo $prazo_faturamento;
?>
        </td>
        <td align='right'>
            <?=number_format($total_parcial, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_parcial / $diff_dias, 2, ',', '.');?>
        </td>

    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td align='right' colspan='6'>
            <font color='blue'>
                Total da <?=$campos[$i - 1]['razaosocial_divisao'].': '.number_format($total_divisao, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
    if(empty($total_geral) || $total_geral == 0) $total_geral = 0.001;
?>
    <tr class='linhadestaque' align='center'>
      <td colspan='2'>
          Divisões
      </td>
      <td>
          MMV R$
      </td>
      <td>
          Porcentagens
      </td>
      <td colspan='2'>
          Totais R$
      </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='green'>
                CABRI
            </font>
        </td>
        <td align='right'>
            <?=number_format(($cabri / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format(($cabri / $total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($cabri, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='green'>
                HEINZ
            </font>
        </td>
        <td align='right'>
            <?=number_format(($heinz / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format(($heinz / $total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($heinz, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='green'>
                WARRIOR
            </font>
        </td>
        <td align='right'>
            <?=number_format(($warrior / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format(($warrior / $total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($warrior, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='red'>NVO</font>
        </td>
        <td align='right'>
            <?=number_format(($nvo/$diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format(($nvo/$total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($nvo, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='red'>
                TOOL
            </font>
        </td>
        <td align='right'>
            <?=number_format(($tool / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format(($tool/$total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($tool, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Empresas
        </td>
        <td>
            MMV R$
        </td>
        <td>
            Porcentagens
        </td>
        <td colspan='2'>
            Totais R$
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='green'>
                ALBAFER
            </font>
        </td>
        <td align='right'>
            <?=number_format((($cabri + $heinz + $warrior) / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format((($cabri + $heinz + $warrior) / $total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($cabri + $heinz + $warrior, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='red'>
                TOOL MASTER
            </font>
        </td>
        <td align='right'>
            <?=number_format((($tool + $nvo) / $diff_dias), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format((($tool + $nvo) / $total_geral * 100), 2, ',', '.').'%';?>
        </td>
        <td colspan='2' align='right'>
            <?=number_format($tool + $nvo, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='blue'>
                <b>Total Geral R$: </b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <?=number_format($total_geral / $diff_dias, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='3' align='right'>
            <font color='blue'>
                <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Valor Dólar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
	</td>
        <td colspan='4'>
            Relatório de
            <font color='blue'>
                <?=$txt_data_inicial;?></font> à <font color='blue'><?=$txt_data_final;?></font>
                com diferença de <font color='blue'><?=number_format($diff_dias, 2, ',', '.');?></font> Meses.
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</body>
</html>
</table>
</form>
</body>
</html>