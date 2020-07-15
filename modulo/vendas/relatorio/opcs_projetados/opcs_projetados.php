<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $representante          = $_POST['representante'];
    $cmb_representante      = $_POST['cmb_representante'];
    $cmb_subordinado        = $_POST['cmb_subordinado'];
}else {
    $representante          = $_GET['representante'];
    $cmb_representante      = $_GET['cmb_representante'];
    $cmb_subordinado        = $_GET['cmb_subordinado'];
}
?>
<html>
<head>
<title>.:: OPC(s) Projetado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data 
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial 	= document.form.txt_data_inicial.value
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
<?
//Se anteriormente foi selecionado um Subordinado, faço rodar a função de Ajax automaticamente quando carregar a Tela ...
if(!empty($cmb_subordinado)) $onload = "ajax('carregar_subordinados.php', 'cmb_subordinado', '$cmb_subordinado')";
?>
<body onload="<?=$onload;?>">
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='70%' border='1' cellspacing ='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            OPC(s) - 
            <font color='yellow' size='-1'>
                Representante(s): 
            </font>
            <?
                if(!empty($representante)) {//Verifico se o Vendedor foi passado por Parâmetro ...
                    $sql = "Select nome_fantasia 
                            from representantes 
                            where id_representante = '$representante' limit 1 ";
                    $campos_representante = bancos::sql($sql);
                    echo $campos_representante[0]['nome_fantasia'];
            ?>
                <input type="hidden" name="representante" value="<?=$representante;?>">
            <?
                }else {//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
            ?>
                <select name="cmb_representante" title="Selecione o Representante" onchange="ajax('carregar_subordinados.php', 'cmb_subordinado')" class="combo">
            <?
                    //Aqui eu listo todos os Representantes que não são Subordinados a ninguém ...
                    $sql = "SELECT id_representante, nome_fantasia AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' 
                            AND `id_representante` NOT IN 
                            (SELECT r.id_representante 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.id_representante = rs.id_representante AND r.ativo = '1') 
                            ORDER BY nome_fantasia ";
                    echo combos::combo($sql, $cmb_representante);
            ?>
                </select>
                &nbsp;
                <font color='yellow' size='-1'>
                    Subordinado(s): 
                </font>
                <select name="cmb_subordinado" title="Selecione o Subordinado" class="combo">
                    <option value='' style='color:red'>SELECIONE</option>
                </select>
            <?
                }
            ?>
            &nbsp;
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {//Aqui é p/ quando acabar de carregar a tela, não submeteu nenhuma vez ...
                    $txt_data_inicial   = '01/'.date('m/Y');
                    $txt_data_final     = date('t/m/Y');
                }
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <?$checked = ($chkt_opcs_projetados == 'S') ? 'checked' : '';?>
            &nbsp;
            <input type="checkbox" name="chkt_opcs_projetados" id='id_opcs_projetados' value="S" <?=$checked;?>>
            <font color='yellow' size='-1'>
                <label for='id_opcs_projetados'>OPC(s) projetado(s)</label>
            </font>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
        $data_inicial           = data::datatodate($txt_data_inicial, '-');
        $data_final             = data::datatodate($txt_data_final, '-');
        $primeiro_ano_cheio     = date('Y') - 3;
        $ultimo_ano_cheio       = date('Y') - 1;
/****************************************Total acumulado dos Últimos 3 anos******************************************/
        $sql = "SELECT pv.id_cliente, SUM(pvi.qtde * pvi.preco_liq_final) AS total_acumulado_ultimos_3_anos 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                WHERE YEAR(pv.data_emissao) BETWEEN '$primeiro_ano_cheio' AND '$ultimo_ano_cheio' 
                GROUP BY pv.id_cliente ORDER BY SUM(pvi.qtde * pvi.preco_liq_final) DESC ";
        $campos_total_acumulado_ultimos3_anos   = bancos::sql($sql);
        $linhas_total_acumulado_ultimos3_anos   = count($campos_total_acumulado_ultimos3_anos);
        for($i = 0; $i < $linhas_total_acumulado_ultimos3_anos; $i++) {
            $vetor_total_acumulado_ultimos_3_anos[$campos_total_acumulado_ultimos3_anos[$i]['id_cliente']]  = $campos_total_acumulado_ultimos3_anos[$i]['total_acumulado_ultimos_3_anos'];
        }
/*********************************************Valor da Última Projeção***********************************************/
        $sql = "SELECT opcs.id_cliente, DATE_FORMAT(SUBSTRING(opcs.data_sys, 1, 10), '%d/%m/%Y') AS data_emissao, COUNT(DISTINCT(opcs.id_opc)) AS qtde_opc_projetado, SUM(oi.qtde_proposta * oi.preco_proposto) AS valor_ultimo_opc_projetado 
                FROM `opcs_itens` oi 
                INNER JOIN `opcs` ON opcs.id_opc = oi.id_opc AND SUBSTRING(opcs.data_sys, 1, 10) BETWEEN '$data_inicial' AND '$data_final'
                GROUP BY opcs.id_cliente, opcs.id_opc ORDER BY data_emissao ";
        $campos_opcs_projetado   = bancos::sql($sql);
        $linhas_opcs_projetado  = count($campos_opcs_projetado);
        for($i = 0; $i < $linhas_opcs_projetado; $i++) {
            $id_clientes.= $campos_opcs_projetado[$i]['id_cliente'].', ';//Será utilizada na ordenação mais abaixo ...
            $vetor_qtde_opc_projetado[$campos_opcs_projetado[$i]['id_cliente']]++;
            $vetor_valor_ultimo_opc_projetado[$campos_opcs_projetado[$i]['id_cliente']]  = $campos_opcs_projetado[$i]['valor_ultimo_opc_projetado'];
            $vetor_data_ultimo_opc_projetado[$campos_opcs_projetado[$i]['id_cliente']]  = $campos_opcs_projetado[$i]['data_emissao'];
        }
/******************************************************Valor de Pedidos******************************************************/
        //Busca o(s) Pedido(s) do determinado Cliente que foram projetados como sendo de OPC no determinado Ano - será utilizado mais abaixo ...
        $sql = "SELECT pv.id_cliente, SUM(pvi.qtde * pvi.preco_liq_final) AS total_pedidos_opc 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                WHERE pv.`projecao_apv` = 'S' 
                AND pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' 
                GROUP BY pv.id_cliente ";
        $campos_pedido = bancos::sql($sql);
        $linhas_pedido = count($campos_pedido);
        for($i = 0; $i < $linhas_pedido; $i++) $vetor_total_pedidos_opc[$campos_pedido[$i]['id_cliente']] = $campos_pedido[$i]['total_pedidos_opc'];
/****************************************************************************************************************************/
        if(!empty($cmb_subordinado)) {
            $representante = $cmb_subordinado;
        }else {
            if(!empty($representante) || !empty($cmb_representante)) {
                $representante = (!empty($representante)) ? $representante : $cmb_representante;
                //Se o Representante for PME, então só irá exibir os Clientes que são do Tipo Revenda Ativa ...
                if($representante == 71) $condicao_tipo = " AND ct.id_cliente_tipo in (1, 4) ";
            }else {//O usuário é obrigado a selecionar um Representante ...
?>
    <tr class="erro" align="center">
        <td>
            SELECIONE UM REPRESENTANTE.
        </td>
    </tr>
<?
                exit;
            }
	}
        $mostrar_projetados = ($chkt_opcs_projetados == 'S') ? 'INNER JOIN `opcs` ON opcs.id_cliente = c.id_cliente AND SUBSTRING(opcs.data_sys, 1, 10) BETWEEN "'.$data_inicial.'" AND "'.$data_final.'" ' : '';
        $id_clientes        = substr($id_clientes, 0, strlen($id_clientes) - 2);
//Aqui eu listo todos os Clientes do Representante selecionado ...
	$sql = "SELECT DISTINCT(c.id_cliente), if(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente 
                FROM clientes c 
                INNER JOIN clientes_tipos ct ON ct.id_cliente_tipo = c.id_cliente_tipo $condicao_tipo 
                INNER JOIN clientes_vs_representantes cr ON c.id_cliente = cr.id_cliente AND cr.id_representante LIKE '$representante'
                $mostrar_projetados
                WHERE c.ativo = '1' 
                GROUP BY c.id_cliente ORDER BY FIELD(c.`id_cliente`, $id_clientes) ";
	$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
	$linhas = count($campos);
?>
    <tr class="linhadestaque" align="center">
        <td>
            Cliente
        </td>
        <td>
            Total do(s) Último(s)<br>
            3 Ano(s) cheio(s)
        </td>
        <td>
            Qtde de OPC(s) <br>Projetado(s)
        </td>
        <td>
            Valor do Último <br>OPC Projetado
        </td>
        <td>
            Data do Último <br>OPC Projetado
        </td>        
        <td>
            Valor de Pedido(s) <br>Emitido(s)
        </td>
    </tr>
<?    
	for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="right">
        <td align="left">
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
        <?
            if($vetor_total_acumulado_ultimos_3_anos[$campos[$i]['id_cliente']] > 0) {
                echo number_format($vetor_total_acumulado_ultimos_3_anos[$campos[$i]['id_cliente']], 2, ',', '.');
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
             <a href = 'detalhes_opcs_projetados.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
        <?
            if($vetor_qtde_opc_projetado[$campos[$i]['id_cliente']] > 0) {
                echo $vetor_qtde_opc_projetado[$campos[$i]['id_cliente']];
            }else {
                echo '&nbsp;';
            }
        ?>
             </a>
        </td>
        <td>
        <?
            if($vetor_valor_ultimo_opc_projetado[$campos[$i]['id_cliente']] > 0) {
                echo number_format($vetor_valor_ultimo_opc_projetado[$campos[$i]['id_cliente']], 2, ',', '.');
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <?=$vetor_data_ultimo_opc_projetado[$campos[$i]['id_cliente']].'&nbsp;';?>
        </td>        
        <td>
            <a href = 'detalhes_pedidos.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
            <?
                if($vetor_total_pedidos_opc[$campos[$i]['id_cliente']] > 0) {
                    echo number_format($vetor_total_pedidos_opc[$campos[$i]['id_cliente']], 2, ',', '.');
                }else {
                    echo '&nbsp;';
                }
            ?>
            </a>
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>