<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/variaveis/intermodular.php');
require('class_pdt.php');
segurancas::geral($PHP_SELF, '../../../');

//Essas Datas são utilizadas mais abaixo ...
$data_inicial = date('Y').'-'.date('m').'-01';
$data_final = date('Y').'-'.date('m').'-'.date('t');
//$data_final = date('Y').'-'.date('m').'-'.cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))

/*Usuários que terão acesso a combo em que se enxerga todos os representantes no PDT ...
Funcionários: Rivaldo 27, Agueda 32, Rodrigo 54, Roberto 62, Wilson Chefe 68, 
Dárcio 98, Cleber 120, Wilson Japonês 136 ...*/
$vetor_usuarios_com_acesso = array('27', '32', '54', '62', '68', '98', '120', '136');
$usuario_com_acesso = 0;

for($i = 0; $i < count($vetor_usuarios_com_acesso); $i++) {
//Se o usuário logado for um dos designados acima, então este terá acesso ao combo ...
	if($vetor_usuarios_com_acesso[$i] == $_SESSION['id_funcionario']) $usuario_com_acesso = 1;
}

//Tratamento com os objetos após ter submetido a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmb_representante 	= $_POST['cmb_representante'];
    $cmb_subordinados 	= $_POST['cmb_subordinados'];
    $cmb_opcoes_dias 	= $_POST['cmb_opcoes_dias'];
}else {
    $cmb_representante 	= $_GET['cmb_representante'];
    $cmb_subordinados 	= $_GET['cmb_subordinados'];
    $cmb_opcoes_dias 	= $_GET['cmb_opcoes_dias'];
}
?>
<html>
<head>
<title>.:: PDT ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function visualizar_comissoes() {
    alert('EM MANUTENÇÃO !')
        
    /*if(document.pdt.cmb_subordinados.value == '') {//Se não está selecionado, força o usuário preencher ...
        alert('SELECIONE UM SUBORDINADO !')
        document.pdt.cmb_subordinados.focus()
    }else {//Se já está selecionado ...
        var id_representante = document.pdt.cmb_subordinados.value
        html5Lightbox.showLightbox(7, '../../faturamento/relatorio/comissoes_novo/comissoes.php?cmb_representante='+id_representante+'&pop_up=1')
    }*/
}
</Script>
</head>
<body>
<form name='pdt' method='post'>
<table border="0" width='80%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td>
			<font color="yellow" size="2">
				<b>PDT (Planejamento Diário de Trabalho)</b>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<table border="0" width='100%' align='center' cellspacing ='1' cellpadding='1'>
				<tr class='linhanormal'>
					<td width="436">
						<font color="darkblue">
							<b>Vendedor: </b>
						</font>
<?/***************************************************************************************/
//Essa variável foi tratada acima ...
							if($usuario_com_acesso == 1) {//Exibe todos os Representantes permissão Master ...
?>
			&nbsp;
<!--Sempre que mudar o representante, eu zero a combo de subordinados abaixo, afinal irá ser 
trago todos os Subordinados que fazem referência ao Representante-->
			<select name="cmb_representante" title="Selecione o Representante" onchange="document.pdt.cmb_subordinados.value = '';document.pdt.submit()" class='objeto_transparente'>
			<?
				$sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
						FROM `representantes` 
						WHERE `ativo` = '1' ORDER BY nome_fantasia ";
				echo combos::combo($sql, $cmb_representante);
			?>
			</select>
			&nbsp;
<?
							}else {//Aqui exibe apenas o Representante Logado ...
								//Busca do id_representante através do id_funcionario ...
								$sql = "SELECT rf.id_representante, r.nome_fantasia 
										FROM `representantes_vs_funcionarios` rf 
										INNER JOIN `representantes` r ON r.id_representante = rf.id_representante 
										WHERE rf.`id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
								$campos_representante = bancos::sql($sql);
								$cmb_representante = $campos_representante[0]['id_representante'];
								echo $campos_representante[0]['nome_fantasia'];
								/*P/ não furar os códigos de JavaScript criei esse hidden com o mesmo nome da 
								Combo, pois não existe combo quando entra o representante ...*/
			?>
						<input type="hidden" name="cmb_representante" value="<?=$cmb_representante;?>">
			<?
							}
/***************************************************************************************/
			?>
						<!--Eu passo a opção de Veio de Outra Tela, pois essa tela é acessada de outros locais e não posso furar a segurança-->
                                                <!--onclick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, '../../faturamento/relatorio/comissoes_novo/comissoes.php?cmb_representante='+document.pdt.cmb_representante.value+'&pop_up=1')}"-->
						<img src="../../../imagem/cifrao.png" width="20" height="16" border="0" title="Visualizar Comissões" alt="Visualizar Comissões" style='cursor:pointer' onclick="alert('EM MANUTENÇÃO !')">
					</td>
					<td width="457">
						<?
							$geral_comissoes 	= pdt::funcao_cotas_metas($cmb_representante, '', $data_inicial, $data_final, 'pv.data_emissao');
							$total_cotas 		= $geral_comissoes['total_cotas'];
							$total_vendas 		= $geral_comissoes['total_vendas'];
							if($total_cotas != 0) $perc_cota = (($total_vendas / $total_cotas) * 100);
						?>
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="red">
								Cotas R$ <?=number_format($total_cotas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="green" title="Período de <?=data::datetodata($data_inicial, '/').' à '.data::datetodata($data_final, '/');?>">
								Vendas R$ <?=number_format($total_vendas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="darkblue">
								<?=number_format($perc_cota, 2, ',', '.');?> % da Meta Atingida
							</font>
						</a>
						<?
/**********************************************************************************************/
/************************Detalhes de Tudo que foi feito no último mês**************************/
/**********************************************************************************************/						
							$datas = genericas::retornar_data_relatorio();
							$data_inicial_fat_mes = data::datatodate($datas['data_inicial'], '-');
							$data_final_fat_mes = data::datatodate($datas['data_final'], '-');
							
							$geral_comissoes = pdt::funcao_cotas_metas($cmb_representante, '', $data_inicial_fat_mes, $data_final_fat_mes, 'pv.data_emissao');
							$total_cotas = $geral_comissoes['total_cotas'];
							$total_vendas = $geral_comissoes['total_vendas'];
							if($total_cotas != 0) $perc_cota = (($total_vendas / $total_cotas) * 100);
						?>
						<br>
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="red">
								Cotas R$ <?=number_format($total_cotas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>&periodo_mes=1')}" class='link'>
							<font color="green" title="Período de <?=data::datetodata($data_inicial_fat_mes, '/').' à '.data::datetodata($data_final_fat_mes, '/');?>">
								Vendas R$ <?=number_format($total_vendas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_representante.value == '') {alert('SELECIONE UM REPRESENTANTE !');document.pdt.cmb_representante.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="darkblue">
								<?=number_format($perc_cota, 2, ',', '.');?> % da Meta Atingida
							</font>
						</a>
					</td>
				</tr>
				<tr class='linhanormal'>
					<td>
						<font color="darkblue">
							<b>Subordinado(s): </b>
						</font>
						&nbsp;
						<select name="cmb_subordinados" title="Selecione os Subordinados" onchange="document.pdt.submit()" class='objeto_transparente'>
						<?
							/*Trago todos os representantes subordinados do Funcionário "Representante" 
							logado e inclusive o próprio representante ...*/
							$sql = "SELECT r.id_representante, r.nome_fantasia 
                                                                FROM `representantes_vs_supervisores` rs 
                                                                INNER JOIN `representantes` r ON r.id_representante = rs.id_representante AND r.ativo = '1' 
                                                                WHERE rs.`id_representante_supervisor` = '$cmb_representante' 
                                                                UNION 
                                                                SELECT id_representante, nome_fantasia 
                                                                FROM `representantes` 
                                                                WHERE `id_representante` = '$cmb_representante' 
                                                                AND `ativo` = '1' ORDER BY nome_fantasia ";
							echo combos::combo($sql, $cmb_subordinados);
						?>
					    </select>
					    &nbsp;
					    <!--Eu passo a opção de Veio de Outra Tela, pois essa tela é acessada de outros locais e não posso furar a segurança-->
						<img src="../../../imagem/cifrao.png" width="20" height="16" border="0" title="Visualizar Comissões" alt="Visualizar Comissões" style="cursor:pointer" onclick="visualizar_comissoes()">
					</td>
					<td>
						<?
							if(!empty($cmb_subordinados)) {
								/*Trago todos os representantes subordinados do Funcionário "Representante" 
								logado e inclusive o próprio representante ...*/
								$geral_comissoes 	= pdt::funcao_cotas_metas($cmb_subordinados, '', $data_inicial_fat_mes, $data_final_fat_mes, 'pv.data_emissao');
								$total_cotas 		= $geral_comissoes['total_cotas'];
								$total_vendas 		= $geral_comissoes['total_vendas'];
								if($total_cotas != 0) $perc_cota = (($total_vendas / $total_cotas) * 100);
						?>
						<a href="#" onClick="if(document.pdt.cmb_subordinados.value == '') {alert('SELECIONE UM SUBORDINADO !');document.pdt.cmb_subordinados.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="red">
								Cotas R$ <?=number_format($total_cotas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_subordinados.value == '') {alert('SELECIONE UM SUBORDINADO !');document.pdt.cmb_subordinados.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>&periodo_mes=1')}" class='link'>
							<font color="green" title="Período de <?=data::datetodata($data_inicial_fat_mes, '/').' à '.data::datetodata($data_final_fat_mes, '/');?>">
								Vendas R$ <?=number_format($total_vendas, 2, ',', '.');?>
							</font>
						</a>
						- 
						<a href="#" onClick="if(document.pdt.cmb_subordinados.value == '') {alert('SELECIONE UM SUBORDINADO !');document.pdt.cmb_subordinados.focus()}else {html5Lightbox.showLightbox(7, 'cotas.php?id_representante='+document.pdt.cmb_representante.value+'&cmb_subordinados=<?=$cmb_subordinados;?>')}" class='link'>
							<font color="darkblue">
								<?=number_format($perc_cota, 2, ',', '.');?> % da Meta Atingida
							</font>
						</a>
						<?
							}
						?>
					</td>
				</tr>
				<?
					$representante = (!empty($cmb_subordinados)) ? $cmb_subordinados : $cmb_representante;
				?>
				<tr class='linhanormal'>
					<td>
						<font color="red">
							<b>Últimos:</b>
						</font>
						<select name="cmb_opcoes_dias" title="Selecione a Opção" onchange="document.pdt.submit()" class='objeto_transparente'>
							<option value="">SELECIONE</option>
							<?
								if($cmb_opcoes_dias == 7) {
									$option7 = 'selected';
								}else if($cmb_opcoes_dias == 14) {
									$option14 = 'selected';
								}else if($cmb_opcoes_dias == 21) {
									$option21 = 'selected';
								}else if($cmb_opcoes_dias == 28) {
									$option28 = 'selected';
								}
							?>
							<option value="7" <?=$option7;?>>7 dias</option>
							<option value="14" <?=$option14;?>>14 dias</option>
							<option value="21" <?=$option21;?>>21 dias</option>
							<option value="28" <?=$option28;?>>28 dias</option>
					    </select>
					</td>
					<td>
					<?
						if($usuario_com_acesso == 1) {
                                                    $data_atual = date('Y-m-d');
                                                    $geral_comissoes = pdt::funcao_cotas_metas($cmb_representante, '', $data_atual, $data_atual, 'pv.data_emissao');
                                                    $total_cotas = $geral_comissoes['total_cotas'];
                                                    $total_vendas = $geral_comissoes['total_vendas'];
					?>
                                                    <a href = 'cota_diaria.php?id_representante=+document.pdt.cmb_representante.value' class='html5lightbox'>
                                                        <font color='Black' title="Período de <?=data::datetodata($data_atual, '/').' à '.data::datetodata($data_atual, '/');?>">
                                                            <b>Venda do Dia R$: </b><?=number_format($total_vendas, 2, ',', '.');?>
                                                        </font>
                                                    </a>
					<?
						}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table width='100%' border="1" align='center' cellpadding='1' cellspacing ='1' bgcolor="#00FF00">
							<tr class='linhadestaque' align='center' >
                                                            <td colspan="3">
                                                                <font color='#000000'>
                                                                    <b>Or&ccedil;amento(s)</b>
                                                                </font>
                                                            </td>
							</tr>
                                                        <tr class='linhanormaldestaque' align='center'>
                                                            <td width="111">
                                                                <b>Todos</b>
                                                            </td>
                                                            <td width="372">
                                                                <a href = 'detalhes_orcamentos.php?tipo_retorno=1&representante=<?=$representante;?>' class='html5lightbox'>
                                                                    <b>N&atilde;o Congelados</b>
                                                                </a>
                                                            </td>
                                                            <td width="399">
                                                                <a href = 'detalhes_orcamentos.php?tipo_retorno=2&representante=<?=$representante;?>' class='html5lightbox'>
                                                                    <b>Congelados, mas sem Pedidos</b>
                                                                </a>
                                                            </td>
							</tr>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Hoje</b>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_orcamentos(1, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
                                                                    <a href = 'detalhes_orcamentos.php?tipo_retorno=1&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <font color='#ff9900'>
								<?
                                                                                echo count($retorno['campos']);
                                                                    }else {
                                                                        echo '&nbsp;';
                                                                    }
								?>
								</td>
								<td>
				            	<?
									$retorno = pdt::funcao_geral_orcamentos(2, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_orcamentos.php?tipo_retorno=2&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
										<font color='#ff9900'>
								<?
										echo count($retorno['campos']);
									}else {
										echo '&nbsp;';
									}
								?> 
								</td>
							</tr>
							<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
								if(!empty($cmb_opcoes_dias)) {
							?>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
								</td>
								<td>
				                <?
                                                                    $retorno = pdt::funcao_geral_orcamentos(1, $cmb_opcoes_dias, $representante);
									
                                                                    if(count($retorno['campos']) > 0) {
								?>
                                                                    <a href = 'detalhes_orcamentos.php?tipo_retorno=1&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <font color='red'>
								<?
                                                                        echo count($retorno['campos']);
                                                                    }else {
                                                                        echo '&nbsp;';
                                                                    }
								?>
								</td>
								<td>
				                <?
                                                                    $retorno = pdt::funcao_geral_orcamentos(2, $cmb_opcoes_dias, $representante);
                                                                    if(count($retorno['campos']) > 0) {
								?>
                                                                    <a href = 'detalhes_orcamentos.php?tipo_retorno=2&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <font color='red'>
								<?
                                                                        echo count($retorno['campos']);
                                                                    }else {
                                                                        echo '&nbsp;';
                                                                    }
								?> 
								</td>
							</tr>
							<?
								}
/***************************************************************************************/
							?>
							<tr class='linhadestaque' align='center'>
                                                            <td colspan='3'>
                                                                <input type='button' name="cmd_incluir_orcamento" value="Incluir Novo Or&ccedil;amento" title="Incluir Novo Or&ccedil;amento" onclick="html5Lightbox.showLightbox(7, '../orcamentos/incluir.php?pop_up=1')" class='objeto_transparente'>
                                                                <input type='button' name="cmd_incluir_cliente" value="Incluir Novo Cliente" title="Incluir Novo cliente" onclick="html5Lightbox.showLightbox(7, '../../classes/cliente/incluir_dados_basicos.php?detalhes=1')" class='objeto_transparente'>
                                                                <input type='button' name="cmd_filtro_orcamento" value="Filtro de Orçamento" title="Filtro de Orçamento" onclick="html5Lightbox.showLightbox(7, '../orcamentos/itens/consultar.php')" class='objeto_transparente'>
                                                            </td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table width='100%' border="1" align='center' cellpadding='1' cellspacing ='1' bgcolor="#FFFF00">
							<tr class='linhadestaque' align='center'>
                                                            <td colspan="6">
                                                                <font color='#000000'>
                                                                    <b>Pedido(s)</b>
                                                                </font>
                                                            </td>
							</tr>
							<tr class='linhanormaldestaque' align='center'>
								<td width="111">
                                                                    <b>Todos</b>
								</td>
								<td width="100">
                                                                    <a href = 'detalhes_pedidos.php?tipo_retorno=1&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <b>N&atilde;o Liberados</b>
                                                                    </a>
								</td>
								<td width="99">
                                                                    <a href = 'detalhes_pedidos.php?tipo_retorno=2&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <b>Liberados</b>
                                                                    </a>
								</td>
								<td width="207">
                                                                    <a href = 'detalhes_pedidos.php?tipo_retorno=3&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <b>Programados</b>
                                                                    </a>
								</td>
								<td width="161">
                                                                    <a href = 'detalhes_pedidos.php?tipo_retorno=4&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <b>Com Vale</b>
                                                                    </a>
								</td>
								<td width="194">
                                                                    <a href = 'detalhes_pedidos.php?tipo_retorno=5&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <b>Pendentes e não Faturados</b>
                                                                    </a>
								</td>
							</tr>
							<tr class='linhanormal' align='center'>
              					<td>
              						<b>Hoje</b>
              					</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(1, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=1&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='#ff9900'>	
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(2, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=2&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='#ff9900'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(3, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=3&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='#ff9900'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(4, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=4&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='#ff9900'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(5, 'H', $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=5&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='#ff9900'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
							</tr>
							<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
								if(!empty($cmb_opcoes_dias)) {
							?>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(1, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
                                                                        <a href = 'detalhes_pedidos.php?tipo_retorno=1&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?	
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(2, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
                                                                        <a href = 'detalhes_pedidos.php?tipo_retorno=2&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?	
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(3, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=3&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(4, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=4&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?
												
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_pedidos(5, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_pedidos.php?tipo_retorno=5&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
							</tr>
							<?
								}
/***************************************************************************************/
							?>
							<tr class='linhadestaque' align='center'>
                                                            <td colspan='6'>
                                                                <input type='button' name="cmd_filtro_pedido" value="Filtro de Pedido" title="Filtro de Pedido" onclick="html5Lightbox.showLightbox(7, '../pedidos/itens/consultar.php')" class='objeto_transparente'>
                                                            </td>
							</tr>
						</table>
					</tr>
				<tr>
					<td>
						<table width='100%' border="1" cellpadding='1' cellspacing ='1' bgcolor="#0000FF" align='center'>
							<tr class='linhadestaque' align='center'>
                                                            <td colspan="3">
                                                                <font color='#000000'>
                                                                    <b>Faturamento(s)</b>
                                                                </font>
                                                            </td>
							</tr>
							<tr class='linhanormaldestaque' align='center'>
                                                            <td width="114">
                                                                    <b>Todos</b>
                                                            </td>
                                                            <td width="182">
                                                                <a href = 'detalhes_notas_fiscais.php?tipo_retorno=0&representante=<?=$representante;?>' class='html5lightbox'>
                                                                    <b>N&atilde;o Despachados</b>
                                                                </a>
                                                            </td>
                                                            <td width="136">
                                                                <a href = 'detalhes_notas_fiscais.php?tipo_retorno=1&representante=<?=$representante;?>' class='html5lightbox'>
                                                                    <b>Devolvidos</b>
                                                                </a>
                                                            </td>
							</tr>
							<tr class='linhanormal' align='center'>
                                                            <td>
                                                                <b>Hoje</b>
                                                            </td>
                                                            <td>
                                                            <?
                                                                $retorno = pdt::funcao_geral_faturamentos(0, 'H', $representante);
                                                                if(count($retorno['campos']) > 0) {
                                                            ?>
                                                            <a href = 'detalhes_notas_fiscais.php?tipo_retorno=0&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                <font color='#ff9900'>
                                                            <?
                                                                    echo count($retorno['campos']);
                                                                }else {
                                                                    echo '&nbsp;';
                                                                }
                                                            ?>
                                                            </td>
                                                            <td>
                                                            <?
                                                                $retorno = pdt::funcao_geral_faturamentos(1, 'H', $representante);
                                                                if(count($retorno['campos']) > 0) {
                                                            ?>
                                                            <a href = 'detalhes_notas_fiscais.php?tipo_retorno=1&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                <font color='#ff9900'>
                                                            <?
                                                                    echo count($retorno['campos']);
                                                                }else {
                                                                    echo '&nbsp;';
                                                                }
                                                            ?>
                                                            </td>
							</tr>
							<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
								if(!empty($cmb_opcoes_dias)) {
							?>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_faturamentos(0, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_notas_fiscais.php?tipo_retorno=0&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
								</td>
								<td>
								<?
									$retorno = pdt::funcao_geral_faturamentos(1, $cmb_opcoes_dias, $representante);
									if(count($retorno['campos']) > 0) {
								?>
									<a href = 'detalhes_notas_fiscais.php?tipo_retorno=1&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                            <font color='red'>
								<?
                                                                            echo count($retorno['campos']);
									}else {
                                                                            echo '&nbsp;';
									}
								?>
									</a>
								</td>
							</tr>
							<?
								}
/***************************************************************************************/
							?>
							<tr class='linhadestaque' align='center'>
								<td colspan="3">
									<input type='button' name="cmd_filtro_nota_fiscal" value="Filtro de Nota Fiscal" title="Filtro de Nota Fiscal" onclick="html5Lightbox.showLightbox(7, '../../faturamento/nfs_consultar/consultar.php?pop_up=1')" class='objeto_transparente'>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<table width='100%' border="1" align='center' cellpadding='1' cellspacing ='1' bgcolor="#FFFFFF">
							<tr class='linhadestaque' align='center'>
								<td colspan="5">
									<font color='#000000'>&nbsp;</font>
								</td>
							</tr>
							<tr class='linhanormaldestaque' align='center'>
								<td width="117">
									<b>Todos</b>
								</td>
								<td width="112">&nbsp;
									
								</td>
								<td width="63">&nbsp;
									
								</td>
								<td width="63">&nbsp;
									
								</td>
								<td width="67">&nbsp;
									
								</td>
							</tr>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Hoje</b>
								</td>
								<td>&nbsp;</td>
								<td>&nbsp;
								</td>
								<td>&nbsp;
									
								</td>
								<td>&nbsp;
									
								</td>
							</tr>
							<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
								if(!empty($cmb_opcoes_dias)) {
							?>
							<tr class='linhanormal' align='center'>
								<td>
									<b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
								</td>
								<td>&nbsp;
									
								</td>
								<td>&nbsp;
									
								</td>
								<td>&nbsp;
									
								</td>
								<td>&nbsp;
									
								</td>
							</tr>
							<?
								}
/***************************************************************************************/
							?>
							<tr class='linhadestaque' align='center'>
								<td colspan="5">&nbsp;
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width='100%' border="1" align='center' cellpadding='1' cellspacing ='1' bgcolor="#FF0000">
							<tr class='linhadestaque' align='center'>
                                                            <td colspan='3'>
                                                                <font color='#000000'>
                                                                    <b>Follow Up(s)</b>
                                                                </font>
                                                            </td>
							</tr>
							<tr class='linhanormaldestaque' align='center'>
                                                            <td width='115'>
                                                                <b>Todos</b>
                                                            </td>
                                                            <td width='180'>
                                                                <a href = 'detalhes_follow_ups.php?tipo_retorno=F&representante=<?=$representante;?>' class='html5lightbox'>
                                                                    Registrados
                                                                </a>
                                                            </td>
							</tr>
							<tr class='linhanormal' align='center'>
                                                            <td>
                                                                <b>Hoje</b>
                                                            </td>
                                                            <td colspan='2'>
                                                            <?
                                                                    $retorno = pdt::funcao_geral_follow_ups('H', $representante);
                                                                    if(count($retorno['campos']) > 0) {
                                                            ?>
                                                                    <a href = 'detalhes_follow_ups.php?tipo_retorno=F&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <font color='#ff9900'>
                                                            <?		
                                                                        echo count($retorno['campos']);
                                                                    }else {
                                                                        echo '&nbsp;';
                                                                    }
                                                            ?>
                                                            </td>
							</tr>
							<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
                                                            if(!empty($cmb_opcoes_dias)) {
							?>
							<tr class='linhanormal' align='center'>
                                                            <td>
                                                                <b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
                                                            </td>
                                                            <td colspan='2'>
                                                            <?
                                                                    $retorno = pdt::funcao_geral_follow_ups($cmb_opcoes_dias, $representante);
                                                                    if(count($retorno['campos']) > 0) {
                                                            ?>
                                                                    <a href = 'detalhes_follow_ups.php?tipo_retorno=F&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                                        <font color='red'>
                                                            <?		
                                                                        echo count($retorno['campos']);
                                                                    }else {
                                                                        echo '&nbsp;';
                                                                    }
                                                            ?>
                                                            </td>
							</tr>
							<?
                                                            }
/***************************************************************************************/
							?>
							<tr class='linhadestaque' align='center'>
                                                            <td colspan='3'>
                                                                <input type='button' name="cmd_registrar_follow_up" value="Registrar Follow Up" title="Registrar Follow Up" onclick="html5Lightbox.showLightbox(7, 'registrar_follow_up.php')" class='objeto_transparente'>
                                                            </td>
							</tr>
					</table>
				</td>
				<td>
					<table width='100%' border='1' cellpadding='1' cellspacing='1' bgcolor='#FF00FF' align='center'>
						<tr class='linhadestaque' align='center'>
                                                    <td colspan='3'>
                                                        <font color='#000000'>
                                                            Financeiro
                                                        </font>
                                                    </td>
						</tr>
						<tr class='linhanormaldestaque' align='center'>
                                                    <td width="118">
                                                        <b>Todos</b>
                                                    </td>
                                                    <td width="149">
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=0&representante=<?=$representante;?>' class='html5lightbox'>
                                                            Cr&eacute;ditos Bloqueados
                                                        </a>
                                                    </td>
                                                    <td width="165">
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=1&representante=<?=$representante;?>' class='html5lightbox'>
                                                            Contas Atrasadas / Vencendo
                                                        </a>
                                                    </td>
						</tr>
						<tr class='linhanormal' align='center'>
                                                    <td>
                                                        <b>Hoje</b>
                                                    </td>
                                                    <td>
                                                    <?
                                                        $retorno = pdt::funcao_geral_financeiros(0, 'H', $representante);
                                                        if(count($retorno['campos']) > 0) {
                                                    ?>
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=0&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                            <font color='#ff9900'>
                                                    <?		
                                                            echo count($retorno['campos']);
                                                        }else {
                                                            echo '&nbsp;';
                                                        }
                                                    ?>
                                                    </td>
                                                    <td>
                                                    <?
                                                        $retorno = pdt::funcao_geral_financeiros(1, 'H', $representante);
                                                        if(count($retorno['campos']) > 0) {
                                                    ?>
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=1&dias=H&representante=<?=$representante;?>' class='html5lightbox'>
                                                            <font color='#ff9900'>
                                                    <?		
                                                            echo count($retorno['campos']);
                                                        }else {
                                                            echo '&nbsp;';
                                                        }
                                                    ?>  
                                                    </td>
						</tr>
						<?
/***************************************************************************************/
//Se estiver selecionada a combo de Últimos dias ...
							if(!empty($cmb_opcoes_dias)) {
						?>
						<tr class='linhanormal' align='center'>
                                                    <td>
                                                        <b>Últimos <?=$cmb_opcoes_dias;?> dias</b>
                                                    </td>
                                                    <td>
                                                    <?
                                                        $retorno = pdt::funcao_geral_financeiros(0, $cmb_opcoes_dias, $representante);
                                                        if(count($retorno['campos']) > 0) {
                                                    ?>
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=0&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                            <font color='red'>
                                                    <?		
                                                            echo count($retorno['campos']);
                                                        }else {
                                                            echo '&nbsp;';
                                                        }
                                                    ?>
                                                    </td>
                                                    <td>
                                                    <?
                                                        $retorno = pdt::funcao_geral_financeiros(1, $cmb_opcoes_dias, $representante);
                                                        if(count($retorno['campos']) > 0) {
                                                    ?>
                                                        <a href = 'detalhes_financeiros.php?tipo_retorno=1&dias=<?=$cmb_opcoes_dias;?>&representante=<?=$representante;?>' class='html5lightbox'>
                                                            <font color='red'>
                                                    <?		
                                                            echo count($retorno['campos']);
                                                        }else {
                                                            echo '&nbsp;';
                                                        }
                                                    ?>
                                                    </td>
						</tr>
						<?
							}
/***************************************************************************************/
						?>
						<tr class='linhadestaque' align='center'>
							<td colspan="3">
								&nbsp;
							</td>
						</tr>
					</table>
				</tr>
				<tr class='linhacabecalho' align='center'>
                                    <td colspan='2'>
                                        <input type='button' name='cmd_filtro_apv' value='Filtro do APV' title='Filtro do APV' onclick="html5Lightbox.showLightbox(7, '../apv/apv.php')" class='botao'>
                                        <input type='button' name='cmd_relatorios_estrategicos' value='Relatórios Estratégicos' title="Relatórios Estratégicos" onclick="html5Lightbox.showLightbox(7, 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=1')" class='botao'>
                                        <input type='submit' name='cmd_atualizar' value='Atualizar PDT' title='Atualizar PDT' class='botao'>
                                    </td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
</body>
</html>