<?
//Executa o sql passado por parâmetro
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);

if($linhas == 0) {
    if($tela == 1) {//Veio de P.A. Industrial - Todos
        $endereco = 'pa_componente_todos.php';
    }else if($tela == 2) {//Veio de P.A. Industrial - Especial
        $endereco = 'pa_componente_esp.php';
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '<?=$endereco;?>?valor=1'
    </Script>
<?
}else {
/*************Atualização dos Cortes das Matérias Primas da Segunda Etapa do Custo*************/
    if(!empty($_POST['hdd_produto_acabado_custo'])) {
        foreach($_POST['hdd_produto_acabado_custo'] as $i => $id_produto_acabado_custo) {
            if($_POST['txt_corte'][$i] > 0) {//Só irá atualizar se tiver Valor de Corte preenchido
                $sql = "UPDATE `produtos_acabados_custos` SET `comprimento_2` = '".$_POST['txt_corte'][$i]."' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }
/**********************************************************************************************/
    if($tela == 1) {//Veio de P.A. Industrial - Todos
        $endereco = 'pa_componente_todos.php';
    }else if($tela == 2) {//Veio de P.A. Industrial - Especial
        $endereco = 'pa_componente_esp.php';
    }
?>
<html>
<head>
<title>.:: Relatório ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function modo_normal() {
//Quando passo o modo_relatorio = 0, significa que é para exibir do modo normal
	window.location = '<?=$endereco.$parametro;?>&modo_relatorio=0'
}

function copiar_corte_geral() {
    if(document.form.txt_corte_geral.value == '') {
        alert('DIGITE O CORTE PARA COPIAR PARA O(S) OUTRO(S) PRODUTO(S) INSUMO(S) !')
        document.form.txt_corte_geral.focus()
        return false
    }
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_produto_acabado_custo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado_custo[]'].length)
    }
    for(var i = 0; i < linhas; i++) document.getElementById('txt_corte'+i).value = document.form.txt_corte_geral.value
}

function validar() {
    var preenchido  = 0
    var elementos   = document.form.elements
    if(typeof(elementos['hdd_produto_acabado_custo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado_custo[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        //Aqui eu verifico se foi preenchido algum Corte de algum Item ...
        if(document.getElementById('txt_corte'+i).value != '') {
            preenchido++
            break;//Para sair fora do Loop ...
        }
    }
    if(preenchido == 0) {
        alert('PREENCHA O(S) CAMPO(S) COM ALGUM CORTE PARA SALVAR !')
        document.getElementById('txt_corte0').focus()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_corte_geral.focus()">
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='150%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='29'>
            Consultar Produtos Acabados
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Grupo P.A. (Empresa Divisão)'>
                Grupo P.A. (E.D.)
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Data de Inclusão'>
                Data Inc
            </font>
        </td>
        <td>
            Última Alteração
        </td>
        <td>
            <font title='Quantidade em Estoque' style='cursor:help'>
                Qtde Est
            </font>
        </td>
        <td>
            <font title='Quantidade em Produção' style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O. C.
            </font>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <font title='Operação (Fat)'>
                O. F.
            </font>
        </td>
        <td>
            <font title='Peso Unitário' style='cursor:help'>
                P. U.
            </font>
        </td>
        <td>
            <font title='Quantidade do Lote' style='cursor:help'>
                Qtde Lote
            </font>
        </td>
        <td colspan='2'>
            1&ordf; Etapa
        </td>
        <td colspan='4'>
            2&ordf; Etapa
        </td>
        <td colspan='2'>
            3&ordf; Etapa
        </td>
        <td colspan='2'>
            4&ordf; Etapa
        </td>
        <td colspan='3'>
            5&ordf; Etapa
        </td>
        <td colspan='2'>
            6&ordf; Etapa
        </td>
        <td colspan='2'>
            7&ordf; Etapa
        </td>
        <td>
            Observação
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            &nbsp;
        </td>
        <td>
            Qtde
        </td>
        <td>
            Embalagem
        </td>
        <td>
            <label title='Peças Cortes'>
                P.C.
            </label>
        </td>
        <td>
            <label title='Comprimento'>
                Comp.
            </label>
        </td>
        <td>
            Corte
            &nbsp;
            <input type='text' name='txt_corte_geral' maxlength='6' size='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
            <img src = '../../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_corte_geral()'>
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Tempo
        </td>
        <td>
            Máquina
        </td>
        <td>
            Fator T.T.
        </td>
        <td>
            Peso
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
                    $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
                    
                    $url = "javascript:window.location = 'custo_industrial.php?tela=".$tela."&id_produto_acabado=".$campos[$i]['id_produto_acabado']."&parametro=".$parametro."'";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td onclick="<?=$url;?>">
			<?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($campos[$i]['status_custo'] == 1) {//Já está liberado
		?>
			<font title="Custo Liberado">
				<?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
			</font>
		<?
			}else {//Não está liberado
		?>
			<font title="Custo não Liberado" color="red">
				<?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
			</font>
		<?
			}
		?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
			<?
			//Se for Diferente de 00/00/0000, então a Data Normal
				if($campos[$i]['data_inclusao'] != '00/00/0000') {
					if($campos[$i]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod
					$sql = "SELECT l.login 
                                                FROM `funcionarios` f
                                                INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                                                WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario']." LIMIT 1 ";
					$campos_login = bancos::sql($sql);
?>
					<font title="Responsável pela alteração: <?=$campos_login[0]['login'];?>"><?=$campos[$i]['data_inclusao']?></font>
<?
					}else {
                                            echo $campos[$i]['data_inclusao'];
					}
				}
			?>
		</td>
                <td onclick="<?=$url;?>" align='center'>
                <?
                    //Esses campos 'id_produto_acabado_custo' e 'qtde_lote' serão utilizados mais abaixo ...
                    $sql = "SELECT id_produto_acabado_custo, qtde_lote, CONCAT(DATE_FORMAT(SUBSTRING(data_sys, 1, 10), '%d/%m/%Y'), SUBSTRING(data_sys, 11, 9)) AS data_atualizacao 
                            FROM `produtos_acabados_custos` 
                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                            AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
                    $campos_custo               = bancos::sql($sql);
                    $id_produto_acabado_custo   = (count($campos_custo) == 1) ? $campos_custo[0]['id_produto_acabado_custo'] : 0;
                    echo $campos_custo[0]['data_atualizacao'];
                ?>
		</td>
		<?
//Aqui eu trago a qtde em Estoque e a qtde em Produção
                    $sql = "SELECT qtde, qtde_producao 
                            FROM `estoques_acabados` 
                            WHERE `id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." LIMIT 1 ";
                    $campos_estoque_pa = bancos::sql($sql);
                    if(count($campos_estoque_pa) == 1) {
                        $estoque    = $campos_estoque_pa[0]['qtde'];
                        $producao   = $campos_estoque_pa[0]['qtde_producao'];
                    }else {
                        $estoque    = 0;
                        $producao   = 0;
                    }
		?>
		<td onclick="<?=$url;?>" align='center'>
			<?=number_format($estoque, 2, ',', '.');?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
			<?=number_format($producao, 2, ',', '.');?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($campos[$i]['operacao_custo'] == 0) {
				echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
				if($campos[$i]['operacao_custo_sub'] == 0) {
					echo '-<font title="Industrial" style="cursor:help">I</font>';
				}else if($campos[$i]['operacao_custo_sub'] == 1) {
					echo '-<font title="Revenda" style="cursor:help">R</font>';
				}else {
					echo '-';
				}
			}else if($campos[$i]['operacao_custo'] == 1) {
				echo '<font title="Revenda" style="cursor:help">R</font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
                    <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($campos[$i]['operacao'] == 0) {
		?>
			<p title="Industrialização (c/ IPI)">I - C</p>
		<?
			}else {
		?>
			<p title="Revenda (s/ IPI)">R - S</p>
		<?
			}
		?>
		</td>
		<td onclick="<?=$url;?>" align='right'>
			<?=number_format($campos[$i]['peso_unitario'], 3, ',', '.');?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
                    <?=$campos_custo[0]['qtde_lote'];?>
		</td>
<?/*********************************Etapa 1***********************************/?>
		<td onclick="<?=$url;?>">
		<?
			$sql = "Select ppe.id_pa_pi_emb, ppe.pecas_por_emb, ppe.embalagem_default, pi.id_produto_insumo, pi.discriminacao, pi.unidade_conversao, u.sigla from produtos_insumos pi, pas_vs_pis_embs ppe, unidades u where ppe.id_produto_acabado = ".$campos[$i]['id_produto_acabado']." and ppe.id_produto_insumo = pi.id_produto_insumo and pi.id_unidade = u.id_unidade order by ppe.id_pa_pi_emb ";
			$campos2 = bancos::sql($sql);
			$linhas2 = count($campos2);
			if($linhas2 > 0) {//Encontrou Embalagens Atrelada(s)
				for($j = 0; $j < $linhas2; $j++) {
					$embalagem_default = $campos2[$j]['embalagem_default'];
					$pecas_por_emb = $campos2[$j]['pecas_por_emb'];
					$unidade_conversao = $campos2[$j]['unidade_conversao'];
					if($embalagem_default == 1) {//Principal
		?>
					<img src="../../../../imagem/certo.gif">
					<font title="Embalagem Principal">
					<?
						if($unidade_conversao > 0.00) {
							echo $pecas_por_emb.' / '.number_format($unidade_conversao, 2, ',', '.').'<br>';
						}else {
							echo $pecas_por_emb.' / <font color="red" title="Sem Conversão">S. C.</font>'.'<br>';
						}
					?>
					</font>
		<?
					}else {
						echo $pecas_por_emb.'<br>';
		?>
					<!--<font color="red">
						<b>* </b><?=$pecas_por_emb;?><br>
					</font>-->
		<?
					}
				}
			}else {//Não encontrou Embalagens Atrelada(s)
				echo '&nbsp;';
			}
		?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($linhas2 > 0) {//Encontrou Embalagens Atrelada(s)
				for($j = 0; $j < $linhas2; $j++) {
					$embalagem_default = $campos2[$j]['embalagem_default'];
					$discriminacao_loop = $campos2[$j]['discriminacao'];
					
					if($embalagem_default == 1) {//Principal
		?>
						<img src="../../../../imagem/certo.gif">
						<font title="Embalagem Principal">
		<?
						echo '* '.$discriminacao_loop.'<br>';
		?>
						</font>
		<?
					}else {
						echo '* '.$discriminacao_loop.'<br>';
					}
				}
			}else {//Não encontrou Embalagens Atrelada(s)
				echo '&nbsp;';
			}
		?>
		</td>
<?/*********************************Etapa 2***********************************/
			$sql = "Select id_produto_insumo, qtde_lote, peso_kg, peca_corte, comprimento_1, comprimento_2 
                                from produtos_acabados_custos 
                                where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 1) {
				$id_produto_insumo = $campos2[0]['id_produto_insumo'];
//Peça Corte
				$pecas_corte = $campos2[0]['peca_corte'];
//Comprimento A
				$comprimento_a = $campos2[0]['comprimento_1'];
//Comprimento B
				$comprimento_b = $campos2[0]['comprimento_2'];
//Discriminação
				$sql = "Select discriminacao 
                                        from produtos_insumos 
                                        where id_produto_insumo = '$id_produto_insumo' limit 1";
				$campos2 = bancos::sql($sql);
				$discriminacao = $campos2[0]['discriminacao'];
			}else {
				$pecas_corte = '';
				$comprimento_a = '';
				$comprimento_b = '';
				$discriminacao = '';
			}
?>
		<td onclick="<?=$url;?>" align='center'>
			<?=$pecas_corte;?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
			<?=$comprimento_a;?>
		</td>
		<td align='center'>
                    <?=$comprimento_b;?>
                    <!--*************************Controle de Tela*************************-->
                    <input type="hidden" name="hdd_produto_acabado_custo[]" value="<?=$id_produto_acabado_custo;?>">
                    <!--******************************************************************-->
                    &nbsp;<input type="text" name="txt_corte[]" id="txt_corte<?=$i;?>" maxlength="6" size="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class="caixadetexto">
		</td>
		<td onclick="<?=$url;?>">
			<?=$discriminacao;?>
		</td>
<?/*********************************Etapa 3***********************************/
	$sql = "Select pp.id_pac_pi, g.referencia, pi.id_produto_insumo, pi.discriminacao, pp.qtde, u.sigla from produtos_insumos pi, pacs_vs_pis pp, grupos g, unidades u where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' and pp.id_produto_insumo = pi.id_produto_insumo and pi.id_grupo = g.id_grupo and pi.id_unidade = u.id_unidade order by pp.id_pac_pi asc ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
?>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo number_format($campos2[$j]['qtde'], 2, ',', '.').'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo $campos2[$j]['discriminacao'].'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
<?/*********************************Etapa 4***********************************/
	$sql = "Select pm.id_pac_maquina, m.id_maquina, m.nome, m.custo_h_maquina, pm.tempo_hs from maquinas m, pacs_vs_maquinas pm where pm.id_produto_acabado_custo = '$id_produto_acabado_custo' and pm.id_maquina = m.id_maquina order by pm.id_pac_maquina asc ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
?>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo number_format($campos2[$j]['tempo_hs'], 1, ',', '.').'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo $campos2[$j]['nome'].'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
<?/*********************************Etapa 5***********************************/
	$sql = "Select ppt.id_pac_pi_trat, u.sigla, pi.id_produto_insumo, pi.discriminacao, ppt.fator, ppt.peso_aco, ppt.peso_aco_manual from produtos_insumos pi, pacs_vs_pis_trat ppt, unidades u where ppt.id_produto_acabado_custo = '$id_produto_acabado_custo' and ppt.id_produto_insumo = pi.id_produto_insumo and pi.id_unidade = u.id_unidade order by ppt.id_pac_pi_trat asc ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
?>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo number_format($campos2[$j]['fator'], 2, ',', '.').'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					if($campos2[$j]['peso_aco_manual'] == 1) {
						echo number_format($campos2[$j]['peso_aco'], 3, ',', '.');
					}else {
						echo number_format($campos2[$j]['peso_aco'] * $campos2[$j]['fator'], 3, ',', '.');
					}
//Peso Aço Manual está checado
					if($campos2[$j]['peso_aco_manual'] == 1) {
						echo ' <font color="green"><b>REAL</b></font>';
					}
					echo '<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo $campos2[$j]['discriminacao'].'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
<?/*********************************Etapa 6***********************************/
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
	$sql = "Select ppu.id_pac_pi_usi, ppu.qtde, u.sigla, pi.id_produto_insumo, pi.discriminacao from produtos_insumos pi, pacs_vs_pis_usis ppu, unidades u where ppu.id_produto_acabado_custo = '$id_produto_acabado_custo' and ppu.id_produto_insumo = pi.id_produto_insumo and pi.id_unidade = u.id_unidade order by ppu.id_pac_pi_usi asc ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
?>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo number_format($campos2[$j]['qtde'], 2, ',', '.').'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>">
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo $campos2[$j]['discriminacao'].'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
<?/*********************************Etapa 7***********************************/
	$sql = "Select pa.referencia, pa.id_produto_acabado, pa.discriminacao, pa.operacao_custo, pa.preco_unitario, pa.status_custo, pp.id_pac_pa, pp.qtde, pp.usar_este_lote_para_orc, u.sigla 
                from pacs_vs_pas pp, produtos_acabados pa, unidades u 
                where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' and pp.id_produto_acabado = pa.id_produto_acabado and pa.id_unidade = u.id_unidade order by pp.id_pac_pa asc ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
?>
		<td onclick="<?=$url;?>" align='center'>
		<?
			if($linhas2 > 0) {
				for($j = 0; $j < $linhas2; $j++) {
					echo number_format($campos2[$j]['qtde'], 2, ',', '.').'<br>';
				}
			}else {
				echo '';
			}
		?>
		</td>
		<td onclick="<?=$url;?>" align='left'>
		<?
			if($linhas2 > 0) {
                            for($j = 0; $j < $linhas2; $j++) {
                                echo $campos2[$j]['discriminacao'];
                                if($campos2[$j]['usar_este_lote_para_orc'] == 'S') echo '<img src="../../../../imagem/certo.gif" title="Usa este Lote de Custo p/ Orc" style="cursor:help">';
                                echo '<br/>';
                            }
			}else {
                            echo '';
			}
		?>
		</td>
<?/***************************************************************************/?>
		<td onclick="<?=$url;?>" align='left'>
			<?=$campos[$i]['observacao'];?>
		</td>
	</tr>
<?
		}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            &nbsp;
        </td>
        <td>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
        <td colspan='13' align='left'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '<?=$endereco;?>'" class='botao'>
            <input type='button' name='cmd_modo_normal' value='Modo Normal' title='Modo Normal' onclick='modo_normal()' class='botao'>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='29'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?}?>