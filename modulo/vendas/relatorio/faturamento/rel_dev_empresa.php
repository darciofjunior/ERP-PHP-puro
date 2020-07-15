<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/faturamentos.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_pendentes/pedidos_pendentes.php', '../../../../');

$valor_dolar_dia = genericas::moeda_dia('dolar');

/************************************Estorno de Comissões************************************/
//Se não for passada nenhuma Empresa por parâmetro, então eu listo todas as Empresas ...
if(empty($_GET['id_empresa_parametro'])) {
    $sql = "SELECT ce.*, nfs.id_empresa, nfs.id_cliente 
            FROM `comissoes_estornos` ce 
            INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf AND nfs.id_empresa IN (1, 2, 4) 
            WHERE ce.data_lancamento BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' ";
}else {//Só exibo os Estornos de Comissões de acordo com a Empresa Corrente passada por parâmetro ...
    $sql = "SELECT ce.*, nfs.id_empresa, nfs.id_cliente 
            FROM `comissoes_estornos` ce 
            INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf AND nfs.id_empresa = '$_GET[id_empresa_parametro]' 
            WHERE ce.data_lancamento BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' ";
}
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Abatimento / Dif. Preço(s) por Empresa ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border="0" cellspacing ='1' cellpadding='1' align='center'>
<?
if($linhas > 0) {//Só exibo essas linhas, caso encontre pelo menos 1 Nota nessa Condição ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan="8">
            Relat&oacute;rio de Abatimento / Reembolso por Empresa
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            <font color="Yellow">Empresa</font> 
            <?
                if($_GET['id_empresa_parametro'] != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($_GET['id_empresa_parametro']);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            <font color='yellow'>
                Data de
            </font>
            <?=data::datetodata($_GET['data_inicial'], '/');?>
            <font color="Yellow"> à </font>
            <?=data::datetodata($_GET['data_final'], '/');?>
        </td>
    </tr>
    <tr class="linhadestaque" align="center"> 
        <td>
            N.º NF <br>de Saída
        </td>
        <td>
            Empresa
        </td>
        <td>
            Tipo de Lançamento
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            N.º NF <br>de Devolução
        </td>
        <td>
            Valor S/ IPI
        </td>
        <td>
            Data de <br>Lançamento
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" align="center">
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nfs_consultar/cabecalho_nfs_saida_dev.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes da NF" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
        <?
            $empresa_conta = genericas::nome_empresa($campos[$i]['id_empresa']);
            if($empresa_conta == 'ALBAFER') {
                echo '<font title="ALBAFER" style="cursor:help">A</font>';
            }else if($empresa_conta == 'TOOL MASTER') {
                echo '<font title="TOOL MASTER" style="cursor:help">T</font>';
            }else if($empresa_conta == 'GRUPO') {
                echo '<font title="GRUPO" style="cursor:help">G</font>';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_lancamento'] == 0) {
                echo 'DEVOLUÇÃO';
            }else if($campos[$i]['tipo_lancamento'] == 1) {
                echo 'ATRASO DE PAGAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 2) {
                echo 'ABATIMENTO / DIF. PREÇOS';
            }else if($campos[$i]['tipo_lancamento'] == 3) {
                echo 'REEMBOLSO';
            }else if($campos[$i]['tipo_lancamento'] == 4) {
                echo 'NF DE ENTRADA';
            }
        ?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/follow_ups/detalhes.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&origem=11', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Registrar Follow-UP do Cliente' class='link'>
            <?
                //Busca do Nome do Cliente da Nota que está sendo Devolvida ...
                $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente 
                        FROM `nfs` 
                        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                        WHERE nfs.`id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                echo $campos_cliente[0]['cliente'];
            ?>
            </a>
        </td>
        <td>
        <?
            //Busca do Nome do Representante ...
            $sql = "SELECT nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['num_nf_devolvida'] == 0) {
                echo ' - ';
            }else {
                echo $campos[$i]['num_nf_devolvida'];
            }
        ?>
        </td>
        <td align="right">
        <?

            if($campos[$i]['tipo_lancamento'] == 3) {//Se for Reembolso ...
                $total_abatimento+=$campos[$i]['valor_duplicata'];
            }else {//Outro Tipo de Lançamento ...
                $total_abatimento-=$campos[$i]['valor_duplicata'];
            }
            if($campos[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
                echo '<font color="blue"> R$ '.number_format($campos[$i]['valor_duplicata'], 2, ',', '.').'</font>';
            }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                echo '<font color="red"> R$ '.number_format($campos[$i]['valor_duplicata'] * (-1), 2, ',', '.').'</font>';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_lancamento'], 0, 10), '/').' '.substr($campos[$i]['data_lancamento'], 11, 8);?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='8'>
            <font color='red' size='2'>
                <b>Total Abatimento(s) / Dif. Preço(s): </b> 
                <?=number_format($total_abatimento, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
}
/************************************Devoluções Automáticas - Modo Novo************************************/
//Se não for passada nenhuma Empresa por parâmetro, então eu listo todas as Empresas ...
if(empty($_GET['id_empresa_parametro'])) {
    $sql = "SELECT * 
            FROM `nfs` 
            WHERE `data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
            AND `status` = '6' 
            AND `id_empresa` IN (1, 2, 4) ";
}else {//Só exibo as Notas de Devoluções de acordo com a Empresa Corrente passada por parâmetro ...
    $sql = "SELECT * 
            FROM `nfs` 
            WHERE `data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
            AND `status` = '6' 
            AND `id_empresa` = '$_GET[id_empresa_parametro]' ";
}
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//Só exibo essas linhas, caso encontre pelo menos 1 Nota nessa Condição ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Relat&oacute;rio de Devolução(ões) por Empresa
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan="8">
            <font color='yellow'>
                Empresa
            </font>
            <?
                if($_GET['id_empresa_parametro'] != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($_GET['id_empresa_parametro']);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            <font color='yellow'>
                Data de
            </font>
            <?=data::datetodata($_GET['data_inicial'], '/');?>
            <font color="Yellow"> à </font>
            <?=data::datetodata($_GET['data_final'], '/');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º NF <br>de Saída
        </td>
        <td>
            Empresa
        </td>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            N.º NF <br>de Devolução
        </td>
        <td>
            Valor S/ IPI
        </td>
        <td>
            Data de <br>Lançamento
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nfs_consultar/cabecalho_nfs_saida_dev.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes da NF" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'D');?>
            </a>
        </td>
        <td>
        <?
            $empresa_conta = genericas::nome_empresa($campos[$i]['id_empresa']);
            if($empresa_conta == 'ALBAFER') {
                echo '<font title="ALBAFER" style="cursor:help">A</font>';
            }else if($empresa_conta == 'TOOL MASTER') {
                echo '<font title="TOOL MASTER" style="cursor:help">T</font>';
            }else if($empresa_conta == 'GRUPO') {
                echo '<font title="GRUPO" style="cursor:help">G</font>';
            }
        ?>
        </td>
        <td colspan='2' align='left'>
            <a href="javascript:nova_janela('../../../classes/cliente/follow_up.php?identificacao=<?=$campos[$i]['id_cliente'];?>&origem=11', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Registrar Follow-UP do Cliente" class='link'>
            <?
                //Busca do Nome do Cliente da Nota que está sendo Devolvida ...
                $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente 
                        FROM `nfs` 
                        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                        WHERE nfs.`id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                echo $campos_cliente[0]['cliente'];
            ?>
            </a>
        </td>
        <td>
        <?
            //Busca do Nome do Representante
            $sql = "SELECT r.nome_fantasia 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN representantes r ON r.id_representante = nfsi.id_representante 
                    WHERE nfsi.id_nf = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nfs_consultar/cabecalho_nfs_saida_dev.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')">
            <?
                //Busca aqui o Número da Nota Principal que gerou essa Nota Fiscal de Devolução Corrente ...
                if(!empty($campos[$i]['snf_devolvida'])) {
                    echo '<font color="red" title="NF de Devolução" style="cursor:help"><b>'.$campos[$i]['snf_devolvida'].'</font>';
                }else {
                    echo '<font color="red" title="NF de Devolução" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf_num_nota']).'</font>';
                }
            ?>
            </a>
        </td>
        <td align="right">
        <?
//Busca do Valor da Nota Fiscal ...
            $sql = "SELECT SUM(qtde_devolvida * valor_unitario) AS total 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '".$campos[$i]['id_nf']."' ";
            $campos_devolucao = bancos::sql($sql);
            $total_devolucao-= $campos_devolucao[0]['total'];
            //Por ser uma devolução, apresento com sinal negativo, porque isso representa Prejuízo ...
            echo '<font color="red"> R$ '.number_format($campos_devolucao[0]['total'] * (-1), 2, ',', '.').'</font>';
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan="8" align="right">
            <font color='red' size='2'>
                <b>Total Devolução(ões): </b> 
                <?=number_format($total_devolucao, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
}
?>
    <tr class='linhadestaque'>
        <td colspan='8' align="right">
            <font color='yellow' size='2'>
                <b>Total Geral: </b>
            </font>
            <?=number_format($total_abatimento + $total_devolucao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='8'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="print()" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="window.close()" style='color:red' class="botao">
        </td>
    </tr>
</table>
</body>
</html>