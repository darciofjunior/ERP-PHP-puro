<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>NO MOMENTO NÃO EXISTE(M) OCORRÊNCIAS PARA EXCLUIR.</font>";
$mensagem[2] = "<font class='confirmacao'>OCORRÊNCIA EXCLUÍDA COM SUCESSO.</font>";

if(!empty($_GET['id_atendimento_diario'])) {
    $sql = "DELETE FROM `atendimentos_diarios` WHERE `id_atendimento_diario` = '$_GET[id_atendimento_diario]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}

/*************************Controle para saber se o usuário terá permissão Total no Relatório*************************/
$usuario_com_acesso = 0;//Valor Padrão ...
/*Usuários que terão acesso a combo no qual poderão fazer qualquer tipo de Manipulação no Relatório 
Funcionários: Roberto 62, Wilson Chefe 68, Dárcio 98 e Arnaldo Netto 147 ...*/
$vetor_usuarios_com_acesso = array('62', '68', '98', '147');

for($i = 0; $i < count($vetor_usuarios_com_acesso); $i++) {
//Se o usuário logado for um dos designados acima, então este terá acesso ao combo ...
    if($vetor_usuarios_com_acesso[$i] == $_SESSION['id_funcionario']) $usuario_com_acesso = 1;
}

//Se o usuário não tem acesso, vem tudo travado ...
if($usuario_com_acesso == 0) {
    $disabled           = 'disabled';
    $class              = 'textdisabled';
    $cmb_funcionario 	= $_SESSION['id_funcionario'];//A combo virá com a Sugestão do Funcionário logado ...
}else {
    $class              = 'combo';
}
/********************************************************************************************************************/
?>
<html>
<head>
<title>.:: Excluir Relatório de Atendimento Diário ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir(id_atendimento_diario) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA OCORRÊNCIA ?')
    if(resposta == true) window.location = 'excluir.php?id_atendimento_diario='+id_atendimento_diario
}
</Script>
</head>
<body>
<form name='form' action='' method='post'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        <td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Excluir Relatório de Atendimento Diário
            <br>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {//Sugestão p/ a primeira vez que se carrega a Tela ...
                    $txt_data_inicial 	= date('d/m/Y');
                    $txt_data_final 	= date('d/m/Y');
                }
                $data_inicial = data::datatodate($txt_data_inicial, '-');
                $data_final = data::datatodate($txt_data_final, '-');
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <select name='cmb_funcionario' title='Selecione o Funcionário' class='<?=$class;?>' <?=$disabled;?>>
            <?
                //Aqui eu listo todos os Funcs que ainda trabalham na Empresa, do Depto. de Vendas ...
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `id_departamento` = '3' 
                        AND `status` <= '1' ORDER BY `nome` ";
                echo combos::combo($sql, $cmb_funcionario);
            ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>                
    </tr>
<?    
/*****************Se já submeteu então*****************/
if(!empty($cmd_consultar)) {
    //Aqui eu busco todas as Ocorrências de Atendimento Diário na Respectiva Data e do Respectivo Funcionário selecionado ...
    if(!empty($cmb_funcionario)) $condicao_funcionario = " AND (ad.`id_funcionario_registrou` = '$cmb_funcionario' OR ad.id_funcionario_responder = '$cmb_funcionario') ";
//Aqui eu trago todos os Registros Diários que estão sem Feedback, independente do Representante ...
    $sql = "SELECT ad.`id_atendimento_diario`, IF(ad.id_cliente = 0, ad.pessoa_atendida, c.`razaosocial`) AS cliente, r.`nome_fantasia`, ad.`contato` , ad.`procedimento`, ad.`observacao`, ad.`feedback`, f.`nome`, DATE_FORMAT(ad.`data_sys_registrou`, '%d/%m/%Y') AS data, TIME_FORMAT(ad.`data_sys_registrou`, '%H:%i:%s') AS hora, ad.`numero` 
            FROM `atendimentos_diarios` ad 
            LEFT JOIN `clientes` c ON c.`id_cliente` = ad.`id_cliente` 
            INNER JOIN `representantes` r ON r.`id_representante` = ad.`id_representante` 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = ad.`id_funcionario_registrou` 
            WHERE ad.`feedback` = '' AND SUBSTRING(ad.`data_sys_registrou`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
            $condicao_funcionario ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
    <tr class='atencao' align='center'>
        <td>
            <?=$mensagem[1];?>
        <td>
    </tr>
<?
        exit;
    }
?>
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
    $vetor_procedimentos = array('C' => 'Ocorrência', 'O' => 'Orçamento', 'P' => 'Pedido', 'OC' => 'OC');
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:excluir('<?=$campos[$i]['id_atendimento_diario']?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="javascript:excluir('<?=$campos[$i]['id_atendimento_diario']?>')" align='left'>
            <a href='#' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>	
        </td>
        <td>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['contato'];?>
        </td>
        <td>
            <?
                echo $vetor_procedimentos[$campos[$i]['procedimento']];

                if($campos[$i]['procedimento'] == 'O') {
                    $url = "../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos[$i]['numero'];
                }else if($campos[$i]['procedimento'] == 'P') {
                    $url = "../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[$i]['numero'];
                }else if($campos[$i]['procedimento'] == 'OC') {
                    $url = "../../vendas/ocs/itens/itens.php?id_oc=".$campos[$i]['numero'];
                }
            ?>
            &nbsp;-&nbsp;
            Número: 
            <a href="javascript:nova_janela('<?=$url;?>', 'ORC', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                <?=$campos[$i]['numero'];?>
            </a>
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
</form>
</body>
</html>
<?}?>