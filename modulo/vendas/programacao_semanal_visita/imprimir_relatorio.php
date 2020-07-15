<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/programacao_semanal_visita/relatorio.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<?
//Faço esse Tratamento com os campos abaixo p/ não furar o SQL abaixo ...
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final     = data::datatodate($_POST['txt_data_final'], '-');

    if(!empty($_POST['txt_cliente'])) {
        $join_cliente = " INNER JOIN `clientes` c ON c.`id_cliente` = psv.`id_cliente` AND (c.`nomefantasia` LIKE '%$_POST[txt_cliente]%' OR c.`razaosocial` LIKE '%$_POST[txt_cliente]%') ";
    }else {
        $join_cliente = " LEFT JOIN `clientes` c ON c.`id_cliente` = psv.`id_cliente` AND (c.`nomefantasia` LIKE '%$_POST[txt_cliente]%' OR c.`razaosocial` LIKE '%$_POST[txt_cliente]%') ";
    }
    
    //Trago dados de "Programação Semanal de Visita" de acordo com o Filtro feito pelo Usuário ...
    $sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, cc.`nome`, 
            DATE_FORMAT(data_registro, '%d/%m/%Y') AS data_registro, psv.`perspectiva_periodo`, 
            IF(psv.`periodo` = 'M', 'MANHÃ', 'TARDE') AS periodo, psv.`comentario` 
            FROM `programacoes_semanais_visitas` psv 
            $join_cliente 
            LEFT JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = psv.`id_cliente_contato` 
            WHERE psv.`id_representante` LIKE '$_POST[cmb_representante]' 
            AND psv.`data_registro` BETWEEN '$data_inicial' AND '$data_final' ORDER BY data_registro, periodo ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
<table width='100%' border='0' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[1];?>
        <td>
    </tr>
</table>
<?
    }else {
?>
<table width='100%' border='1' cellspacing='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            PROGRAMAÇÃO SEMANAL DE VISITAS
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            Representante: 
            <?
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '$_POST[cmb_representante]' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
            ?>
            <font color='black'>
                <?=$campos_representante[0]['nome_fantasia'];?>
            </font>
        </td>
    </tr>
<?
        $data_anterior              = '';
        $total_perspectiva_periodo  = 0;
        
        //Esse vetor irá me auxiliar mais abaixo ...
        $vetor_semana = array('DOMINGO', 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO');

	for($i = 0; $i < $linhas; $i++) {
/*********************************************************************************************/
/*Aqui eu verifico se a Data Anterior é Diferente da Data Atual que está sendo listada no loop, 
se for então eu atribuo a Data Atual p/ a Data Anterior ...*/
            if($data_anterior != $campos[$i]['data_registro']) {
                $data_anterior = $campos[$i]['data_registro'];
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <?=$vetor_semana[data::dia_semana($campos[$i]['data_registro'])];?>
        </td>
        <td colspan='3'>
            Data: 
            <font color='black'>
                <?=$campos[$i]['data_registro'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Período
        </td>
        <td>
            Cliente
        </td>
        <td>
            Contato
        </td>
        <td>
            Perspectiva de Período R$
        </td>
        <td>
            Comentário
        </td>
    </tr>
<?   
            }
/*********************************************************************************************/
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['periodo'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['cliente'])) {
                echo $campos[$i]['cliente'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['nome'])) {
                echo $campos[$i]['nome'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['perspectiva_periodo'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['comentario'];?>
        </td>
    </tr>
<?
            $total_perspectiva_periodo+= $campos[$i]['perspectiva_periodo'];
	}
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            Total Perspectiva de Período R$ => 
        </td>
        <td align='right'>
            <font color='black'>
                <?=number_format($total_perspectiva_periodo, 2, ',', '.');?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' style='purple' onclick='window.print()' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
</body>
</html>