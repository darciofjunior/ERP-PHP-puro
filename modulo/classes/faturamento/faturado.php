<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../');

//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.`razaosocial`, c.`credito`, nfs.* 
        FROM `nfs_itens` nfsi 
        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfsi.`id_pedido_venda_item` = '$_GET[id_pedido_venda_item]' LIMIT 1 ";
$campos = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota    = $campos[0]['id_empresa'];
$id_cliente         = $campos[0]['id_cliente'];
$razao_social       = $campos[0]['razaosocial'];
$credito            = $campos[0]['credito'];
$numero_nf          = faturamentos::buscar_numero_nf($campos[0]['id_nf'], 'S');
if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista': $campos[0]['vencimento1'];
}
//Aqui verifica o Tipo de Nota
if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
    $nota_sgd = 'N';//var surti efeito lá embaixo
    $tipo_nota = ' (NF)';
}else {
    $nota_sgd = 'S'; //var surti efeito lá embaixo
    $tipo_nota = ' (SGD)';
}
//Aqui é a verifica se esta Nota é de Saída ou Entrada
$tipo_nfe_nfs       = ($campos[0]['tipo_nfe_nfs'] == 'S') ? ' - Saída' : ' - Entrada';
$prazo_faturamento.= $tipo_nota.$tipo_nfe_nfs;
?>
<html>
<head>
<title>.:: Visualizar Faturamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form'>
<table width='95%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho'>
        <td height='17' align='left'>
            <font color='#49D2FF'>
                Cliente:
            </font>
            <?=$id_cliente.' - '.$razao_social;?>
            <font color='#49D2FF'>
                / Cr&eacute;dito:
            </font>
            <?=$credito;?>
            <font color='#49D2FF'>
                &nbsp;/ Forma de Venda:
            </font>
            <?=$prazo_faturamento;?>
        </td>
    </tr>
</table>
<?
//Aqui começa a segunda parte, a parte em q calcula e exibe os itens
$sql = "SELECT t.`nome` AS transportadora, nfs.`id_nf`, nfs.`id_nf_num_nota`, nfs.`id_funcionario`, 
        nfs.`id_empresa`, nfs.`tipo_nfe_nfs`, nfs.`snf_devolvida`, nfs.`data_emissao`, nfs.`status`, 
        nfs.`tipo_despacho`, nfs.`numero_remessa`, nfs.`data_sys`, nfsi.`id_nfs_item`, nfsi.`qtde` AS qtde_nota, 
        nfsi.`valor_unitario` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
        WHERE nfsi.`id_pedido_venda_item` = '$_GET[id_pedido_venda_item]' ORDER BY nfs.`id_nf` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
//Verifica se tem pelo menos um item na Nota Fiscal
if($linhas > 0) {
?>
<table width='95%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            Visualizar Faturamento
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            N.º NF
        </td>
        <td>
            Data de <br/>Emissão
        </td>
        <td>
            Status NF / Tipo de Despacho <br/>/ N.º de Remessa
        </td>
        <td>
            Transportadora
        </td>
        <td>
            N.º de Remessa
        </td>
        <td>
            Empresa
        </td>
        <td>
            Login
        </td>
        <td>
            Data e Hora
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
	<td>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 1010, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes' class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
	</td>
	<td>
	<?
            if($campos[$i]['data_emissao'] != '0000-00-00') {
                echo data::datetodata($campos[$i]['data_emissao'], '/');
            }else {
                echo '&nbsp;';
            }
	?>
	</td>
	<td align='left'>
	<?
            if($campos[$i]['status'] == 0) {
                echo 'EM ABERTO';
            }else if($campos[$i]['status'] == 1) {
                echo 'LIBERADA P/ FATURAR';
            }else if($campos[$i]['status'] == 2) {
                echo 'FATURADA';
            }else if($campos[$i]['status'] == 3) {
                echo 'EMPACOTADA';
            }else if($campos[$i]['status'] == 4) {
                echo 'DESPACHADA';
                if($campos[$i]['tipo_despacho'] == 1) {
                    echo ' / PORTARIA'.' / '.$campos[$i]['numero_remessa'];
                }else if($campos[$i]['tipo_despacho'] == 2) {
                    echo ' / TRANSPORTADORA'.' / '.$campos[$i]['numero_remessa'];
                }else if($campos[$i]['tipo_despacho'] == 3) {
                    echo ' / NOSSO CARRO';
                }else if($campos[$i]['tipo_despacho'] == 4) {
                    echo ' / RETIRA';
                }else if($campos[$i]['tipo_despacho'] == 5) {
                    echo ' / CORREIO/SEDEX'.' / '.$campos[$i]['numero_remessa'];
                }
            }else if($campos[$i]['status'] == 5) {
                echo 'CANCELADA';
            }else if($campos[$i]['status'] == 6) {
                echo '<font color="red"><b>DEVOLUÇÃO</b></font>';
            }
	?>
	</td>
	<td>
            <?=$campos[$i]['transportadora'];?>
	</td>
        <td>
            <?=faturamentos::numero_remessa($campos[$i]['id_nf']);?>
        </td>
	<td>
        <?
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];
            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $apresentar = $nomefantasia.' (NF)';
            }else {
                $apresentar = $nomefantasia.' (SGD)';
            }
//Significa q é da Albafer
            if($campos[$i]['id_empresa'] == 1) {
                if($campos[$i]['id_empresa_divisao'] == 1) {
                    $apresentar.= ' - CABRI - HEINZ';
                }else {
                    $apresentar.= ' - WARRIOR';
                }
//Significa q é da Tool Master
            }else if($campos[$i]['id_empresa'] == 2) {
                if($campos[$i]['id_empresa_divisao'] == 5) {
                    $apresentar.= ' - NVO';
                }else {
                    $apresentar.= ' - TOOL';
                }
//Significa q estava selecionada o Grupo
            }else if($campos[$i]['id_empresa'] == 4) {
                $apresentar.= ' - TODAS DIVISÕES';
            }
            echo $apresentar;
        ?>
	</td>
	<td>
	<?
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
	?>
	</td>
	<td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' - '.substr($campos[$i]['data_sys'], 11, 8);?>
	</td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
    	<td colspan='8'>
            &nbsp;
	</td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
?>
<html>
<body>
<table width='95%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='atencao'>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                <b>Nota Fiscal
                <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="blue">
                    <?=$numero_nf;?>
                </font>
                n&atilde;o cont&eacute;m itens cadastrado.</b>
            </font>
        </td>
    </tr>
</table>
</body>
</html>
<?}?>