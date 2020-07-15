<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/financeiros.php');
require('../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CLIENTE ALTERADO COM SUCESSO.</font>";

$id_cliente     = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];
$pop_up 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
$nao_exibir_menu= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['nao_exibir_menu'] : $_GET['nao_exibir_menu'];

$sql = "SELECT c.*, f.`nome` 
        FROM `clientes` c 
        LEFT JOIN `funcionarios` f on f.`id_funcionario` = c.`id_funcionario` 
        WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
$campos_clientes = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='50%'>
            Raz&atilde;o Social:
        </td>
        <td width='50%'>
            Nome Fantasia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue' size='-1'>
                <b><?=$campos_clientes[0]['razaosocial'];?></b>
            </font>
        </td>
        <td>
            <font color='darkblue' size='-1'>
                <b><?=$campos_clientes[0]['nomefantasia'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            DADOS DE CRÉDITO
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Crédito:</b> 
                <font color='black' size='-1'>
                    <b><?=$campos_clientes[0]['credito'];?></b>
                </font>
                <?
                    if($campos_clientes[0]['limite_credito'] > 0) {
                ?>
                <b> - Limite de Crédito:</b> 
                <font color='black' size='-1'>
                    <b>R$ <?=number_format($campos_clientes[0]['limite_credito'], 2, ',', '.');?></b>
                </font>
                <?
                    }
                ?>
            </font>
        </td>
        <td>
            <font color='darkblue'>
                <b>Último Crédito Alterado por: </b>
            </font>
        <?
            if(!empty($campos_clientes[0]['nome'])) {
                echo '<br>'.$campos_clientes[0]['nome'].' em '.data::datetodata(substr($campos_clientes[0]['credito_data'], 0, 10), '/').' às '.substr($campos_clientes[0]['credito_data'], 11, 8);
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='darkblue'>
                <b>Observação do Crédito:</b>
            </font>
            <font color='green'>
                <?=$campos_clientes[0]['credito_observacao'];?>
            </font>
        </td>
    </tr>
</table>
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' onclick="showHide('contatos'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                Contato(s)
            </font>
        </td>
    </tr>
    <tr>
        <td>
            <iframe src = '../../classes/cliente/contatos.php?id_cliente=<?=$id_cliente;?>' name='contatos' id='contatos' marginwidth='0' marginheight='0' style='display: visible' frameborder='0' height='100%' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
<?
//Listo todas as Empresas Divisões ...
	$sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ORDER BY `razaosocial` ";
	$campos_divisoes = bancos::sql($sql);
	$linhas_divisoes = count($campos_divisoes);
	if($linhas_divisoes > 0) {
?>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Representante(s) Atrelado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC' width='200'>
            <b>Divisão(ões)</b>
        </td>
        <td bgcolor='#CCCCCC' width='200'>
            <b>Representante(s)</b>
        </td>
        <td bgcolor='#CCCCCC' width='200'>
            <b>Desconto do Cliente</b>
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_divisoes; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_divisoes[$i]['razaosocial'];?>
        </td>
        <td>
        <?
//Verifica se existe algum Representante na Empresa Divisão atual do loop para o Cliente ...
            $sql = "SELECT r.`nome_fantasia`, cr.`desconto_cliente` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '$id_cliente' 
                    AND cr.`id_empresa_divisao` = '".$campos_divisoes[$i]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) == 1) echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td align='center'>
        <?
            if(count($campos_representante) == 1) echo number_format($campos_representante[0]['desconto_cliente'], 2, ',', '.');
        ?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal'>
        <td bgcolor="#CCCCCC" colspan='3'>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
</table>
<table border="0" width='95%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class="iframe" onClick="showHide('qtde_quitacao'); return false" style="cursor:pointer;">
		<td height="22" align="left">
			<font color="yellow" size="2">&nbsp;Qtde de Quitação(ões) nos últimos 6 meses: </font>
<?
//Aqui seleciono todas as Contas à Receber do Cliente ...
	$sql = "SELECT count(id_conta_receber) AS qtde_quitacao 
                FROM `contas_receberes` 
                WHERE id_cliente = '$id_cliente' 
                AND `ativo` = '1' 
                AND `data_emissao` > DATE_ADD('$data_hoje', "."INTERVAL -180 DAY".") 
                AND `status` = '2' ";
	$campos = bancos::sql($sql);
	$qtde_quitacao_total+= $campos[0]['qtde_quitacao'];
?>
			<font color="#FFFFFF" size="2"><?=$qtde_quitacao_total;?></font>
			<span id="statusqtde_quitacao">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
			<iframe src="../../classes/cliente/qtde_quitacao.php?id_cliente=<?=$id_cliente;?>" name="qtde_quitacao" id="qtde_quitacao" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="100%" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<?
/************************Visualização das Contas à Receber************************/
    //Visualizando as Contas à Receber
    $retorno    = financeiros::contas_em_aberto($id_cliente, 1, '', 2);
    $linhas     = count($retorno['id_contas']);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;Débito(s) à Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por parâmetro porque utilizo dentro da Função de Receber-->
            <iframe src = '../../classes/cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
/*********************************************************************************/
?>
<center>
<?
    //Quando essa tela, for aberta como Pop-Up ou for pedido para não exibir menu então, não exibo esse botão de Voltar ...
    if($pop_up != 1 && $nao_exibir_menu != 1) {
?>
<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = 'alterar.php<?=$parametro;?>'" class='botao'>
<?
    }
    //Quando essa tela, for aberta como Detalhes, exibo esse botão ...
    if($pop_up == 1) {
?>
<input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.close()' style='color:red' class='botao'>
<?		
    }
?>
</center>
</body>
</html>