<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
session_start('funcionarios');

$vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 2 => 'AVALIADO PELO SUPERVISOR', 3 => 'ENVIADO PARA PROCESSO INTERNO', 
4 => 'ENVIADO P/ T…CNICO - PARA ESCLARECIMENTO DE PROBLEMA', 5 => 'ENVIADO P/ T…CNICO - PARA OR«AMENTO', 
6 => 'OR«AMENTO ENVIADO P/ CLIENTE - AGUARDANDO APROVA«√O', 7 => 'ENVIADO P/ ESTOQUE', 8 => 'MANIPULA«√O P/ ESTOQUE', 
9 => 'ENVIADO P/ CLIENTE / REPRESENTANTE', 10 => 'DESDOBRAR QUANTIDADE', 11 => 'ACOMPANHAMENTO INTERNO');

$caracteres_invalidos = '‡·ÈÌÛ˙„ı‚ÍÓÙ˚Á¿¡…Õ”⁄√’¬ Œ‘€«™∞∫"ß';
$caracteres_validos = 'aaeiouaoaeioucAAEIOUAOAEIOUC     ';

//Parte aonde se exclui os Itens da OC ...
if(!empty($_POST['id_oc_item'])) {
    //Excluindo o Item da OC ...
    $sql = "DELETE FROM `ocs_itens` WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
    bancos::sql($sql);
    echo '<font class="confirmacao"><center>ITEM EXCLUIDO COM SUCESSO.</center></font>';
}

//Verifico o Tipo de PaÌs do Cliente ...
$sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, ocs.* 
        FROM `ocs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = ocs.`id_cliente` 
        WHERE ocs.`id_oc` = '$_GET[id_oc]' LIMIT 1 ";
$campos = bancos::sql($sql);

//Busca dos Itens da OC ...
$sql = "SELECT oci.*, pa.`referencia`, pa.`discriminacao`, pa.`operacao`, pa.`operacao_custo`, 
        pa.`operacao_custo_sub`, pa.`peso_unitario`, pa.`mmv`, pa.`observacao` AS observacao_produto 
        FROM `ocs_itens` oci 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oci.`id_produto_acabado` 
        WHERE oci.`id_oc` = '$_GET[id_oc]' ORDER BY oci.`id_oc_item` ";
$campos_itens = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas_itens = count($campos_itens);
?>
<html>
<table width='100%' border='0' cellspacing='1' cellpadding='1' class='table_pontilhada' onmouseover='total_linhas(this)'>
<?
	if($linhas_itens == 0) {//Se n„o existir nenhum Item de OC ...
?>
	<tr align='center'>
            <td colspan='5'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="red">
                    <b>N&atilde;o existem Itens.</b>
                </font>
            </td>
	</tr>
<?
	}else {//Se existir pelo menos 1 Item ...
?>
	<tr class="linhadestaque" align="center">
		<td colspan='5'>
			<b><font face="Verdana" color='#FFFFFF' size='-1'>
				&Uacute;ltima altera&ccedil;&atilde;o realizada por Funcion&aacute;rio: 
				<font face="Verdana" color="yellow">
				<?
					$sql = "SELECT l.login 
							FROM `funcionarios` f 
							INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
							WHERE f.id_funcionario = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
					$campos_login = bancos::sql($sql);
					echo $campos_login[0]['login'];
				?>
				</font>
			</font></b>
			&nbsp;-&nbsp;
			<b><font face="Verdana" color='#FFFFFF' size='-1'>
				Data e Hora de Atualiza&ccedil;&atilde;o:
				<font face="Verdana" color="yellow">
					<?=data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' - '.substr($campos[0]['data_sys'], 11, 8);?>
				</font>
			</font></b>
		</td>
	</tr>
	<tr><td></td></tr>
	<tr class='linhanormal' align='center'>
		<td bgcolor='#CECECE'><b>Op&ccedil;&otilde;es</b></td>
		<td bgcolor='#CECECE'><b>Qtde</b></td>
		<td bgcolor='#CECECE'><b>Produto</b></td>
		<td bgcolor='#CECECE'><b>Defeito Alegado</b></td>
	</tr>
<?
		for ($i = 0; $i < $linhas_itens; $i++) {
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
            <td class='td_pontilhada'>
                <a href="javascript:alterar_item('<?=$i + 1;?>')">
                    <img src = '../../../../imagem/menu/alterar.png' border='0' alt='Alterar Item' title='Alterar Item'>
                </a>
                <?
                    //SÛ posso excluir um Item em Aberto em que a OC tambÈm esteja em aberto ...
                    if($campos_itens[$i]['status'] == 0 && $campos[$i]['status'] == 0) {
                ?>
                <a href="javascript:excluir_item('<?=$campos_itens[$i]['id_oc_item'];?>')">
                    <img src = '../../../../imagem/menu/excluir.png' border='0' alt='Excluir Item' title='Excluir Item'>
                </a>
                <?
                    }
                ?>
            </td>
            <td class='td_pontilhada'>
                <?=$campos_itens[$i]['qtde'];?>
            </td>
            <td class='td_pontilhada' align='left'>
                <?=strtr(intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 1, 1, $campos_itens[$i]['id_produto_acabado_discriminacao']), $caracteres_invalidos, $caracteres_validos).'&nbsp;';?>
            </td>
            <td class='td_pontilhada' align='left'>
            <?
                echo utf8_encode($campos_itens[$i]['defeito_alegado']);
                if($campos_itens[$i]['cliente_vai_devolver_peca'] == 'S') echo '<font color="red" title="Cliente vai Devolver Pe&ccedil;a" style="cursor:help"><b> (Cliente Dev)</b></font>';
            ?>
            </td>
	</tr>
<?			
			//Busco Follows Ups dos Itens da OC ...
			$sql = "SELECT oci.*, l.login
					FROM `ocs_itens_follow_ups` oci 
					INNER JOIN `funcionarios` f ON f.id_funcionario = oci.id_funcionario 
					INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
					WHERE oci.`id_oc_item` = '".$campos_itens[$i]['id_oc_item']."' 
					ORDER BY oci.`id_oc_item_follow_up` DESC LIMIT 2";
			$campos_itens_follow_up = bancos::sql($sql);
			$linhas_itens_follow_up = count($campos_itens_follow_up);
			for($j = 0; $j < $linhas_itens_follow_up ;$j++) {
?>
	<tr class="linhanormalescura">
		<td width='90'>
			<b>Login:</b> <?=$campos_itens_follow_up[$j]['login'];?> 
		</td>
		<td>
			<b>Status:</b> <?=utf8_encode($vetor_status[$campos_itens_follow_up[$j]['status']]);?>
		</td>
		<td>
			<b>Observa&ccedil;&atilde;o:</b> <?=utf8_encode($campos_itens_follow_up[$j]['observacao']);?>
		</td>
		<td>
			<b>Data e Hora:</b> <?=data::datetodata(substr($campos_itens_follow_up[$j]['data_sys'], 0, 10), '/').' '.substr($campos_itens_follow_up[$j]['data_sys'], 11, 5);?>
		</td>
	</tr>		
<?
			}
		}
?>
	<tr class='linhacabecalho' align="center">
		<td colspan='4'>
			&nbsp;
		</td>
	</tr>
	<tr align="center">
		<td colspan='4'>
			<font size='-2' color='#0066ff' face='verdana, arial, helvetica, sans-serif'><b>
				<?=utf8_encode(paginacao::print_paginacao('sim'));?>
			</b></font>
		</td>
	</tr>
</table>
<table border="1" width="100%" cellspacing='0' cellpadding='1' class='linhanormal'>
	<tr align='center'>
		<td bgcolor='#ffffff' height='40' width='30%' align='left'><b>Avaliado pelo CQ</b></td>
		<td bgcolor='#ffffff' width='5%'><b>Data</b></td>
		<td bgcolor='#ffffff' width='25%'>&nbsp;</td>
		<td bgcolor='#ffffff' width='5%'><b>Visto</b></td>
		<td bgcolor='#ffffff' width='35%'>&nbsp;</td>
	</tr>
	<tr align='center'>
		<td bgcolor='#ffffff' height='40' align='left'><b>Avaliado pelo Supervisor</b></td>
		<td bgcolor='#ffffff' ><b>Data</b></td>
		<td bgcolor='#ffffff' >&nbsp;</td>
		<td bgcolor='#ffffff' ><b>Visto</b></td>
		<td bgcolor='#ffffff' >&nbsp;</td>
	</tr>
</table>
<br/><br/>
<table width='100%' border='1' cellspacing='0' cellpadding='1' class='linhanormal'S>
    <tr align='center'>
        <td bgcolor='#ffffff' colspan='5'>
            <font size='2'>
                <b>Controle de Estoque - OC N.∫ <?=$_GET[id_oc];?></b>
            </font>
        </td>
    </tr>
    <tr align='center'>
        <td bgcolor='#ffffff' ><b>Item</b></td>
        <td bgcolor='#ffffff' ><b>Data Retirada</b></td>
        <td bgcolor='#ffffff' ><b>Retirado por (Nome)</b></td>
        <td bgcolor='#ffffff' ><b>Visto / Assinatura</b></td>
    </tr>
<?
            for($linha = 1; $linha <= 3; $linha++) {
?>
    <tr>
        <td bgcolor='#ffffff' height='40' width='30%'>&nbsp;</td>
        <td bgcolor='#ffffff' >&nbsp;</td>
        <td bgcolor='#ffffff' >&nbsp;</td>
        <td bgcolor='#ffffff' >&nbsp;</td>
    </tr>
<?
            }
?>
</table>
<br>
<table border="1" width="100%" cellspacing='0' cellpadding='1' class='linhanormal'>
	<tr align='center'>
		<td bgcolor='#ffffff' height='40' width='30%' align='left'><b>Finalizado Pelo Supervisor</b></td>
		<td bgcolor='#ffffff' width='5%'><b>Data</b></td>
		<td bgcolor='#ffffff' width='25%'>&nbsp;</td>
		<td bgcolor='#ffffff' width='5%'><b>Visto</b></td>
		<td bgcolor='#ffffff' width='35%'>&nbsp;</td>
	</tr>
<?
	}
?>
</table>
</html>