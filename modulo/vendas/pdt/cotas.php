<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$valor_dolar_dia = genericas::moeda_dia('dolar');

//Tratamento com os objetos após ter submetido a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_inicial 		= data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final 		= data::datatodate($_POST['txt_data_final'], '-');
    $id_representante 	= $_POST['id_representante'];
    $cmb_subordinados 	= $_POST['cmb_subordinados'];
}else {
    if($_GET['periodo_mes'] == 1) {//Parâmetro que vem da Tela de baixo ...
        $datas          = genericas::retornar_data_relatorio();
        $data_inicial 	= data::datatodate($datas['data_inicial'], '-');
        $data_final 	= data::datatodate($datas['data_final'], '-');
    }else {
        $data_inicial 	= date('Y').'-'.date('m').'-01';
        $data_final 	= date('Y').'-'.date('m').'-'.date('t');
    }
    $id_representante 	= $_GET['id_representante'];
    $cmb_subordinados 	= $_GET['cmb_subordinados'];
}

//Busca o nome do Representante passado por parâmetro ...
$sql = "SELECT nome_fantasia 
        FROM `representantes` 
        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
$campos_rep = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Consultar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 5 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
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
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_representante' value='<?=$id_representante;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relat&oacute;rio de Supervisor Vs Cota do Representante<br/>
            <?='('.$campos_rep[0]['nome_fantasia'].')';?>
            <font color='yellow'>
                - Período: 
            </font>
            <input type='text' name='txt_data_inicial' value="<?=data::datetodata($data_inicial, '/');?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')" title='Calendário'>
            Data Final:
            <input type='text' name="txt_data_final" value="<?=data::datetodata($data_final, '/');?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')" title='Calendário'>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
//Pego o Total de Pedidos Emitidos Independente de Representante ...
	$sql = "SELECT c.id_pais, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                WHERE pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                AND pv.liberado = '1' 
                GROUP BY c.id_pais ";
	$campos_perc = bancos::sql($sql);
	$linhas_perc = count($campos_perc);
	for($i = 0; $i < $linhas_perc; $i++) {
            if($campos_perc[$i]['id_pais'] == 31) {//Brasil ...
                $tot_nac+= $campos_perc[$i]['total'];
            }else {//Internacional ...
                $tot_exp+= ($campos_perc[$i]['total'] * $valor_dolar_dia);
            }
	}
	$total_pedidos_emitidos = $tot_nac + $tot_exp;
?>
    <font face='arial black' color='darkblue'>
        <center>
            <b>Valor Total do(s) Pedido(s) Emitido(s): <?='R$ '.number_format($total_pedidos_emitidos, 2, ',', '.');?></b>
        </center>
    </font>
    <tr class='linhadestaque' align='center'>
        <td>
            Representante(s)
        </td>
        <td>
            Cotas R$
        </td>
        <td>
            Vendas R$
        </td>
        <td>
            % Sobre a Cota(s)
        </td>
        <td>
            % Sobre o Total(is)
        </td>
    </tr>
<!--************************Total do Representante************************-->	
    <tr class='linhanormaldestaque' align='center'>
    <?
        $geral_comissoes        = pdt::funcao_cotas_metas($id_representante, $cmb_subordinados, $data_inicial, $data_final, 'pv.data_emissao');
        $total_cotas            = $geral_comissoes['total_cotas'];
        $total_vendas           = $geral_comissoes['total_vendas'];
        if($total_cotas != 0)   $perc_cota = (($total_vendas / $total_cotas) * 100);
    ?>
        <td align='left'>
            <?=$campos_rep[0]['nome_fantasia'];?>
        </td>
        <td align='right'>
            <?=number_format($total_cotas, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($total_vendas, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($perc_cota, 2, ',', '.').'%';?>
        </td>
        <td align='right'>
        <?
            if($total_pedidos_emitidos == 0) {//Aqui eu tenho esse tratamento p/ q não dê erro de Divisão por 0
                $total_perc = 0;
            }else {//Posso dividir por Zero normalmente ...
                $total_perc = ($total_vendas / $total_pedidos_emitidos) * 100;
            }
            $total_geral_perc+= $total_perc;
            echo number_format($total_perc, 2, ',', '.').'%';
        ?>
        </td>
    </tr>
<?
	$difereca_mes   = data::diferenca_data($data_inicial, $data_final);
	$qtde_dias      = (integer)$difereca_mes[0];

	if($id_representante == 1) {//Quando é Direto eu verifico todos os Funcionários Internos que venderam para esse caso ...
            $sql = "SELECT DISTINCT(pvi.id_funcionario), SUM(pvi.qtde * pvi.preco_liq_final) AS total_vendas, f.nome 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.id_representante = '1' 
                    INNER JOIN `funcionarios` f ON f.id_funcionario = pvi.id_funcionario 
                    WHERE pv.data_emissao BETWEEN '$data_inicial' and '$data_final' 
                    AND pv.liberado = '1' GROUP BY pvi.id_funcionario ORDER BY f.nome ";
            $campos_funcionarios 	= bancos::sql($sql);
            $linhas_funcionarios	= count($campos_funcionarios);
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            FUNCIONÁRIOS QUE VENDERAM P/ O REPRESENTANTE DIRETO
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Funcionário
        </td>
        <td>
            Total Vendas R$
        </td>
        <td>
            % Sobre o Total
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_funcionarios; $i++) {
?>
    <tr class='linhanormal'>
        <td colspan='3' align='left'>
            <?=$campos_funcionarios[$i]['nome'];?>
        </td>
        <td align='right'>
            <?=number_format($campos_funcionarios[$i]['total_vendas'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $perc_cota = (($campos_funcionarios[$i]['total_vendas'] / $total_vendas) * 100);
            echo number_format($perc_cota, 2, ',', '.').'%';
        ?>
        </td>
    </tr>
<?
            }
	/************************Total dos Subordinados************************/
	}else {//Se for Outro então verifico os subordinados do Representante Principal ...
            //Busco a última Cota em Vigência ...
            $sql = "SELECT r.id_representante, r.nome_fantasia, SUM(rc.cota_mensal) AS total_cota_mensal 
                    FROM `representantes_vs_supervisores` rs 
                    INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                    INNER JOIN `representantes_vs_cotas` rc ON rc.`id_representante` = rs.`id_representante` AND rc.`data_final_vigencia` = '0000-00-00' 
                    WHERE rs.`id_representante_supervisor` = '$id_representante' 
                    GROUP BY rc.`id_representante` ";
            $campos_rep = bancos::sql($sql);
            $linhas 	= count($campos_rep);
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='left'>
                <?=$campos_rep[$i]['nome_fantasia'];?>
        </td>
        <td align='right'>
        <?
                if($qtde_dias <= 31) {//Ou seja se for a diferenca de um mes ele faz a conta pela cota mensal ...
                        $total_cota_diaria = $campos_rep[$i]['total_cota_mensal'];
                }else {//Se nao eu calculo a cota diaria e multiplico pela diferença de dias entre a data solicitada ...
                        $total_cota_diaria = ($campos_rep[$i]['total_cota_mensal'] / 30) * ($qtde_dias + 1);
                }
                echo number_format($total_cota_diaria, 2, ',', '.');
                $total_geral_cotas+= $total_cota_diaria;
                if($total_cota_diaria == 0 || $total_cota_diaria == 0.00) $total_cota_diaria = 1;
        ?>
        </td>
        <td align='right'>
        <?		
            $sql = "SELECT rep.id_representante, rep.nome_fantasia, rep.ativo, c.id_pais, SUM(pvi.qtde * pvi.preco_liq_final) AS total 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.`id_representante` = '".$campos_rep[$i]['id_representante']."' 
                    INNER JOIN `representantes` rep ON rep.id_representante = pvi.id_representante 
                    INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante = rep.id_representante 
                    WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
                    AND pv.`liberado` = '1' GROUP BY rep.id_representante, c.id_pais ";
            $campos_vendas = bancos::sql($sql);
            $linhas_vendas = count($campos_vendas);
            $total_parcial = 0;
            for($j = 0; $j < $linhas_vendas; $j++) {
                if($campos_vendas[$j]['id_pais'] == 31) {//Brasil ...
                    $total_parcial+= $campos_vendas[$j]['total'];
                }else {//Internacional ...
                    $total_parcial+= ($campos_vendas[$j]['total'] * $valor_dolar_dia);
                }
            }
            echo number_format($total_parcial, 2, ',', '.');
            $total_geral+=$total_parcial;
        ?>
        </td>
        <td align='right'>
        <?
            if($total_cota_diaria == 1) {
                echo "<font color='blue'>Sem Cota</font>";
            }else {
                $perc_cota = (($total_parcial / $total_cota_diaria) * 100);
                echo number_format($perc_cota, 2, ',', '.').'%';
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($total_pedidos_emitidos == 0) {//Aqui eu tenho esse tratamento p/ q não dê erro de Divisão por 0
                $total_perc = 0;
            }else {//Posso dividir por Zero normalmente ...
                $total_perc = ($total_parcial / $total_pedidos_emitidos) * 100;
            }
            $total_geral_perc+= $total_perc;
            echo number_format($total_perc, 2, ',', '.').'%';
        ?>
        </td>
    </tr>
<?
            }
//Controle p/ evitar o Erro de Divisão por Zero ...
            if($total_geral_cotas == 0 || $total_geral_cotas == '') {
                $total_geral_cotas_perc = $total_geral * 100;
            }else {
                $total_geral_cotas_perc = ($total_geral / $total_geral_cotas) * 100;
            }
?>
    <tr class='linhanormal' align='right'>
        <td>
            <font color='green'>
                <b>Total do(s) Subordinado(s):</b>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_cotas, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_cotas_perc, 2, ',', '.').'%';?>
            </font>
        </td>
        <td>
            <font color='green'>
                <?=number_format($total_geral_perc, 2, ',', '.').'%';?>
            </font>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormaldestaque'>
        <td colspan='5'>
            <font size='2' color='darkred'>
                <b>Valor Dólar do dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>