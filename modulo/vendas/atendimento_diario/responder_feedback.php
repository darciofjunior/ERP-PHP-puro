<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>NO MOMENTO NÃO EXISTE(M) OCORRÊNCIAS PARA RESPONDER FEEDBACK.</font>";
?>
<html>
<head>
<title>.:: Relatório de Atendimento Diário - Responder FeedBack ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
/*Aqui eu trago todos os Registros Diários que o "Representante" do Cliente ainda não deu nenhum Feedback e que 
o Autor do Registro seja diferente do usuário logado que terá que responder ...*/
$sql = "SELECT ad.`id_atendimento_diario`, IF(ad.`id_cliente` = '0', ad.`pessoa_atendida`, c.`razaosocial`) AS cliente, 
        r.`nome_fantasia`, ad.`contato` , ad.`procedimento`, ad.`observacao`, ad.`feedback`, f.`nome`, 
        DATE_FORMAT(ad.`data_sys_registrou`, '%d/%m/%Y') AS data, TIME_FORMAT(ad.`data_sys_registrou`, '%H:%i:%s') AS hora, ad.`numero` 
        FROM `atendimentos_diarios` ad 
        LEFT JOIN `clientes` c ON c.`id_cliente` = ad.`id_cliente` 
        INNER JOIN `representantes` r ON r.`id_representante` = ad.`id_representante`
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = ad.`id_funcionario_registrou` 
        WHERE ad.`id_funcionario_responder` = '$_SESSION[id_funcionario]' 
        AND ad.`id_funcionario_registrou` <> ad.`id_funcionario_responder` 
        AND ad.`feedback` = '' ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        <td>
    </tr>
<?
}else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório de Atendimento Diário - Responder FeedBack
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Contato
        </td>
        <td>
            Procedimento
        </td>
        <td>
            Observação
        </td>
        <td>
            Funcionário que Registrou
        </td>
        <td>
            Data e Hora de Registro
        </td>
    </tr>
<?
    $vetor_procedimentos = array('C' => 'Ocorrência', 'O' => 'Orçamento', 'P' => 'Pedido');	
    for($i = 0; $i < $linhas; $i++) {
        $url = 'feedback.php?id_atendimento_diario='.$campos[$i]['id_atendimento_diario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'" align='left'>
            <a href="<?=$url?>" class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>	
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <?=$campos[$i]['contato'];?>
        </td>
        <td>
        <?
            if($campos[$i]['procedimento'] == 'O' || $campos[$i]['procedimento'] == 'P' || $campos[$i]['procedimento'] == 'OC') {
                if($campos[$i]['procedimento'] == 'O') {
                    $url = "../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos[$i]['numero'];
                }else if($campos[$i]['procedimento'] == 'P') {
                    $url = "../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[$i]['numero'];
                }else if($campos[$i]['procedimento'] == 'OC') {
                    $url = "../../vendas/ocs/itens/itens.php?id_oc=".$campos[$i]['numero'];
                }
                echo $vetor_procedimentos[$campos[$i]['procedimento']].' / ';
        ?>
            <a href="javascript:nova_janela('<?=$url;?>', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">	
                <?=$campos[$i]['numero'];?>
            </a>
        <?		
            }else {
                echo $vetor_procedimentos[$campos[$i]['procedimento']];
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['data'].' '.$campos[$i]['hora'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?}?>
</body>
</html>