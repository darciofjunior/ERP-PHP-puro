<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $representante 		= $_POST['representante'];
    $cmb_representante 	= $_POST['cmb_representante'];
}else {
    $representante 		= $_GET['representante'];
    $cmb_representante 	= $_GET['cmb_representante'];
}

//Variáveis que serão utilizadas mais abaixo ...
$primeiros_meses_trimestre = array(1, 4, 7, 10);
$segundos_meses_trimestre = array(2, 5, 8, 11);
$terceiros_meses_trimestre = array(3, 6, 9, 12);

//Busca do Total de Pedidos Programados do Cliente dentro do Trimestre do Ano Atual ...
if(date('m') <= 3) {//Significa que o Mês é pertinente ao 1º Trimestre ...
    $mes_rotulo1 = 'Janeiro';
    $mes_rotulo2 = 'Fevereiro';
    $mes_rotulo3 = 'Março';
    $condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".date('Y')."-01-01' AND '".date('Y')."-03-31' ";
    $periodo = '01/01/'.date('Y').' à 31/03/'.date('Y');
}else if(date('m') <= 6) {//Significa que o Mês é pertinente ao 2º Trimestre ...
    $mes_rotulo1 = 'Abril';
    $mes_rotulo2 = 'Maio';
    $mes_rotulo3 = 'Junho';
    $condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".date('Y')."-04-01' AND '".date('Y')."-06-30' ";
    $periodo = '01/04/'.date('Y').' à 30/06/'.date('Y');
}else if(date('m') <= 9) {//Significa que o Mês é pertinente ao 3º Trimestre ...
    $mes_rotulo1 = 'Julho';
    $mes_rotulo2 = 'Agosto';
    $mes_rotulo3 = 'Setembro';
    $condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".date('Y')."-07-01' AND '".date('Y')."-09-30' ";
    $periodo = '01/07/'.date('Y').' à 30/09/'.date('Y');
}else {//Significa que o Mês é pertinente ao 4º Trimestre ...
    $mes_rotulo1 = 'Outubro';
    $mes_rotulo2 = 'Novembro';
    $mes_rotulo3 = 'Dezembro';
    $condicao_periodo_trimestre = " AND pv.`faturar_em` BETWEEN '".date('Y')."-10-01' AND '".date('Y')."-12-31' ";
    $periodo = '01/10/'.date('Y').' à 31/12/'.date('Y');
}
?>
<html>
<head>
<title>.:: Relatório de Projeto Trimestral ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' action='' method='post'>
<table width='90%' border='1' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relatório de Projeto Trimestral - Período de 
            <font color='yellow' size='-1'>
                <?=$periodo;?>
            </font>
            <br/>
            <font color='yellow' size='-1'>
                Representante: 
            </font>
            <?
//Verifico se o Vendedor foi passado por Parâmetro ...
                if(!empty($representante)) {
                        $sql = "SELECT nome_fantasia 
                                FROM `representantes` 
                                WHERE `id_representante` = '$representante' LIMIT 1 ";
                        $campos_representante = bancos::sql($sql);
                        echo $campos_representante[0]['nome_fantasia'];
            ?>
                <input type='hidden' name='representante' value='<?=$representante;?>'>
            <?
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
                }else {
            ?>
                    <select name='cmb_representante' title='Selecione o Representante' onchange='document.form.submit()' class='combo'>
            <?
                    $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                    echo combos::combo($sql, $cmb_representante);
            ?>
                    </select>
            <?
                }
            ?>
        </td>
    </tr>
<?
    if(!empty($representante) || !empty($cmb_representante)) {
        $representante = (!empty($representante)) ? $representante : $cmb_representante;
    }else {
        $representante = '%';
    }

    $sql = "SELECT c.`id_cliente`, 
            IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_mensal, 
            MONTH(pv.`faturar_em`) AS mes, r.`id_representante`, r.`nome_fantasia` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pvi.`id_representante` LIKE '$representante' 
            INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pv.`liberado` = '1' 
            AND pv.`projecao_vendas` = 'S' 
            $condicao_periodo_trimestre 
            GROUP BY cliente, mes ORDER BY r.`nome_fantasia`, pv.`faturar_em` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
</table>
<table width='90%' border='0' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <?=$mensagem[1];?>
        <td>
    </tr>
</table>
<?
        exit;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Representante(s)
        </td>
        <td>
            Cliente
        </td>
        <td>
            <?=$mes_rotulo1;?>
        </td>
        <td>
            <?=$mes_rotulo2;?>
        </td>
        <td>
            <?=$mes_rotulo3;?>
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
        if($id_cliente_antigo != $campos[$i]['id_cliente']) {
            if(($campos[$i]['id_cliente'] == $campos[$i + 1]['id_cliente']) && ($campos[$i]['id_cliente'] == $campos[$i + 2]['id_cliente'])) {
                $qtde_meses_exibir = 3; 
            }else if(($campos[$i]['id_cliente'] == $campos[$i + 1]['id_cliente']) && ($campos[$i]['id_cliente'] != $campos[$i + 2]['id_cliente'])) {
                $qtde_meses_exibir = 2;
            }else {
                $qtde_meses_exibir = 1;
            }
            if($qtde_meses_exibir == 3) {//Significa que estão preenchidos todos meses do Semestre ...
                //Se for o Primeiro mês do Trimestre => Janeiro, Abril, Julho, Outubro ...
                if(in_array($campos[$i]['mes'], $primeiros_meses_trimestre)) {
                    $valor_mes_atual = $campos[$i]['valor_mensal'];
                    $valor_proximo_1mes = $campos[$i + 1]['valor_mensal'];
                    $valor_proximo_2meses = $campos[$i + 2]['valor_mensal'];
                //Se for o Segundo mês do Trimestre => Fev, Maio, Agosto, Novembro ...
                }else if(in_array($campos[$i]['mes'], $segundos_meses_trimestre)) {
                    $valor_mes_atual = 0;
                    $valor_proximo_1mes = $campos[$i]['valor_mensal'];
                    $valor_proximo_2meses = $campos[$i + 1]['valor_mensal'];
                }else {
                    $valor_mes_atual = 0;
                    $valor_proximo_1mes = 0;
                    $valor_proximo_2meses = $campos[$i]['valor_mensal'];
                }
            }else if($qtde_meses_exibir == 2) {
                //1º Mês e 2º Mês
                if(in_array($campos[$i]['mes'], $primeiros_meses_trimestre) && in_array($campos[$i + 1]['mes'], $segundos_meses_trimestre)) {
                    $valor_mes_atual = $campos[$i]['valor_mensal'];
                    $valor_proximo_1mes = $campos[$i + 1]['valor_mensal'];
                    $valor_proximo_2meses = 0;
                //1º Mês e 3º Mês
                }else if(in_array($campos[$i]['mes'], $primeiros_meses_trimestre) && in_array($campos[$i + 2]['mes'], $terceiros_meses_trimestre)) {
                    $valor_mes_atual = $campos[$i]['valor_mensal'];
                    $valor_proximo_1mes = 0;
                    $valor_proximo_2meses = $campos[$i + 1]['valor_mensal'];
                //2º Mês e 3º Mês
                }else if(in_array($campos[$i]['mes'], $segundos_meses_trimestre) && in_array($campos[$i + 1]['mes'], $terceiros_meses_trimestre)) {
                    $valor_mes_atual = 0;
                    $valor_proximo_1mes = $campos[$i]['valor_mensal'];
                    $valor_proximo_2meses = $campos[$i + 1]['valor_mensal'];
                }
            }else {
                if(in_array($campos[$i]['mes'], $primeiros_meses_trimestre)) {
                    $valor_mes_atual = $campos[$i]['valor_mensal'];
                    $valor_proximo_1mes = 0;
                    $valor_proximo_2meses = 0;
                }else if(in_array($campos[$i]['mes'], $segundos_meses_trimestre)) {
                    $valor_mes_atual = 0;
                    $valor_proximo_1mes = $campos[$i]['valor_mensal'];
                    $valor_proximo_2meses = 0;
                }else if(in_array($campos[$i]['mes'], $terceiros_meses_trimestre)) {
                    $valor_mes_atual = 0;
                    $valor_proximo_1mes = 0;
                    $valor_proximo_2meses = $campos[$i]['valor_mensal'];
                }
            }
?>
    <tr class='linhanormal'>
        <td align='center'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='right'>
        <?
            if($valor_mes_atual > 0) {
        ?>
        <a href = 'detalhes_pedidos.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&id_representante=<?=$campos[$i]['id_representante'];?>&buscar_mes=mes_atual' class='html5lightbox'>
        <?
            }
            echo 'R$ '.number_format($valor_mes_atual, 2, ',', '.');?>
        </td>
        <td align="right">
        <?
            if($valor_proximo_1mes > 0) {
        ?>
        <a href = 'detalhes_pedidos.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&id_representante=<?=$campos[$i]['id_representante'];?>&buscar_mes=proximo_1mes' class='html5lightbox'>
        <?
            }
            echo 'R$ '.number_format($valor_proximo_1mes, 2, ',', '.');?>
        </td>
        <td align="right">
        <?
            if($valor_proximo_2meses > 0) {
        ?>
        <a href = 'detalhes_pedidos.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&id_representante=<?=$campos[$i]['id_representante'];?>&buscar_mes=proximo_2meses' class='html5lightbox'>
        <?
            }
            echo 'R$ '.number_format($valor_proximo_2meses, 2, ',', '.');?>
        </td>
    </tr>
<?
            $id_cliente_antigo = $campos[$i]['id_cliente'];
        }
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só exibe a(s) Projeção(ões) de Cliente(s) que já virou(aram) Pedido(s).
</pre>