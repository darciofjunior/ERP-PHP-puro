<?
/*Se essa variável = '1' representa q esse arquivo está sendo requirido de dentro do 
arquivo baixas_manipulacoes.php...*/
if($verificar_apenas_epi != 1) {
    require('../../../lib/segurancas.php');
    require('../../../lib/genericas.php');
    require('../../../lib/data.php');
}
session_start('funcionarios');
//Essa segurança é p/ que ignore essa verificação devido esse arquivo ser chamado como Iframe em outros arquivos ...
if(empty($_GET['ignorar_seguranca_url'])) segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/inventario.php', '../../../');
$mensagem[1] = "<font class='atencao'>NÃO HÁ BAIXA(S) / MANIPULAÇÃO(ÕES) A SER(EM) CONSULTADA(S) NESSE PERÍODO.</font>";

//Função ...
/**********Atualizando o campo de Troca da Baixa Manipulação**********/
//Aqui eu atualizo o campo de Troca da Baixa Manipulação ...
    if(!empty($_GET['id_baixa_manipulacao'])) {
        $sql = "UPDATE `baixas_manipulacoes` SET `troca` = '".$_GET['nova_troca']."' WHERE `id_baixa_manipulacao` = '".$_GET['id_baixa_manipulacao']."' LIMIT 1 ";
        bancos::sql($sql);
    }
/********************************************************/
//Tratamento com a variável $id_produto_insumo que vem por parâmetro ...
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$id_produto_insumo 		= $_POST['id_produto_insumo'];
		$nao_exibir_voltar 		= $_POST['nao_exibir_voltar'];
		$txt_consultar			= $_POST['txt_consultar'];
		$opt_opcao				= $_POST['opt_opcao'];
		$passo					= $_POST['passo'];
		if(!empty($_POST['txt_data_inicial'])) {
			$txt_data_inicial_usa 	= data::datatodate($_POST['txt_data_inicial'], '-');
			$txt_data_final_usa 	= data::datatodate($_POST['txt_data_final'], '-');
		}else {//Últimos 6 Meses ...
			$txt_data_inicial_usa 	= data::datatodate(data::adicionar_data_hora(date('d/m/Y'), - 180), '-');
			$txt_data_final_usa 	= date('Y-m-d');
		}
	}else {
		$id_produto_insumo 		= $_GET['id_produto_insumo'];
		$nao_exibir_voltar 		= $_GET['nao_exibir_voltar'];
		$txt_consultar			= $_GET['txt_consultar'];
		$opt_opcao                      = $_GET['opt_opcao'];
		$passo                          = $_GET['passo'];
		if(!empty($_GET['txt_data_inicial'])) {
			$txt_data_inicial_usa 	= data::datatodate($_GET['txt_data_inicial'], '-');
			$txt_data_final_usa 	= data::datatodate($_GET['txt_data_final'], '-');	
		}else {//Últimos 6 Meses ...
			$txt_data_inicial_usa 	= data::datatodate(data::adicionar_data_hora(date('d/m/Y'), - 180), '-');
			$txt_data_final_usa 	= date('Y-m-d');
		}
	}
	if(isset($txt_data_inicial_usa)) {
		$condicao_datas                 = " AND SUBSTRING(bm.data_sys, 1, 10) BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' ";
		$txt_data_inicial 		= data::datetodata($txt_data_inicial_usa, '/');
		$txt_data_final 		= data::datetodata($txt_data_final_usa, '/');
	}
	
	/*Se essa variável = '1' representa q esse arquivo está sendo requirido de dentro do 
	arquivo baixas_manipulacoes.php...*/
	if($verificar_apenas_epi != 1) {
		$url = 'detalhes.php?id_produto_insumo='.$id_produto_insumo;
		//Busca a Discriminação do PI corrente que eu estou trabalhando ...
		$sql = "SELECT discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
		$campos = bancos::sql($sql);
		$discriminacao = $campos[0]['discriminacao'];
		//Busca de todas as Baixas / Manipulações do PI no período de Datas especificado ...
		$sql = "SELECT bm.* 
                        FROM `baixas_manipulacoes` bm 
                        WHERE bm.id_produto_insumo = '$id_produto_insumo' $condicao_datas 
                        ORDER BY bm.`data_sys` DESC, bm.`id_baixa_manipulacao` DESC ";
	}else {//Aqui significa que esse arquivo aqui, está sendo requirido de dentro do arquivo baixas_manip.
		$url = 'baixas_manipulacoes.php';
		//Busca de todas as Baixas / Manipulações do PI somente do Grupo EPI no período de Datas espec ...
		$sql = "SELECT bm.*, pi.discriminacao 
                        FROM `baixas_manipulacoes` bm 
                        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = bm.id_produto_insumo 
                        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.id_grupo = '1' 
                        INNER JOIN `funcionarios` f ON f.id_funcionario = bm.id_funcionario_retirado AND f.nome LIKE '%$txt_consultar%' 
                        WHERE 1 $condicao_datas ORDER BY bm.`data_sys` DESC, bm.`id_baixa_manipulacao` DESC ";
	}
        //Se essa Tela foi acessada de Vendas, não posso exibir os dados com paginação porque senão temos erros no cálculo na ML Est ...
        if(!empty($_GET['id_orcamento_venda_item'])) {
            $campos = bancos::sql($sql);
        }else {//Se foi acessada de Compras, então posso exibir os dados com paginação normalmente ...
            $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        }
	$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Baixa(s) / Manipulação(ões) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_data_inicial.value == '' && document.form.txt_data_final.value != '') {
        alert('DIGITE A DATA INICIAL !')
        document.form.txt_data_inicial.focus()
        return false
    }
    if(document.form.txt_data_inicial.value != '' && document.form.txt_data_final.value == '') {
        alert('DIGITE A DATA FINAL !')
        document.form.txt_data_final.focus()
        return false
    }
    data_inicial = document.form.txt_data_inicial.value
    data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.replace('/', '')
    data_inicial_sem_formatacao = data_inicial.replace('/', '')

    data_final = data_final.replace('/', '')
    data_final_sem_formatacao = data_final.replace('/', '')

    data_inicial_invertida = data_inicial_sem_formatacao.substr(4, 4)+data_inicial_sem_formatacao.substr(2, 2)+data_inicial_sem_formatacao.substr(0, 2)
    data_final_invertida = data_final_sem_formatacao.substr(4, 4)+data_final_sem_formatacao.substr(2, 2)+data_final_sem_formatacao.substr(0, 2)

    data_inicial_invertida = eval(data_inicial_invertida)
    data_final_invertida = eval(data_final_invertida)

    if(data_inicial_invertida > data_final_invertida) {
        alert('DATAS INVÁLIDAS !')
        document.form.txt_data_inicial.focus()
        return false
    }
}

function alterar_troca(id_baixa_manipulacao, nova_troca) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ALTERAR ESTA TROCA ?')
    if(resposta == true) window.location = 'detalhes_baixas_manipulacoes.php<?=$parametro;?>&id_baixa_manipulacao='+id_baixa_manipulacao+'&nova_troca='+nova_troca
}
</Script>
</head>
<body onload="if(typeof(document.form.txt_data_inicial) == 'object') {document.form.txt_data_inicial.focus()}">
<form name="form" method='post' action='' onsubmit="return validar()">
<!--*************************************Controles de Tela*************************************-->
<input type='hidden' name='exibir_pendencia_pedido_compras' value='<?=$exibir_pendencia_pedido_compras;?>'>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
<input type='hidden' name='opt_opcao' value='<?=$opt_opcao;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='passo' value='<?=$passo;?>'>
<!--*******************************************************************************************-->
<!--Esse parâmetro $nao_exibir_voltar, vem de um outro arquivo que abre esta Tela como sendo Pop-Up ...-->
<input type='hidden' name='nao_exibir_voltar' value='<?=$nao_exibir_voltar;?>'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Consultar Compra(s) / Baixa(s) / Manipulação(ões) 
        </td>
    </tr>
<?
        //Aqui eu busco o PA do PI que é "Matéria Prima" ...
        $sql = "SELECT `id_produto_acabado`, `referencia`, `discriminacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_pa = bancos::sql($sql);
        if(count($campos_pa) == 0) {//Só irá mostrar esse Filtro quando for PI mesmo ...
?>    
    <tr class='linhadestaque' align='left'>
        <td colspan='11'>
            <b>Data Inicial:</b>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this,'data','','',event)" size='12' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            at&eacute;<b> </b>
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this,'data','','',event)" size='12' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <b>Data Final</b>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
        }else {//Quando for PA, não se deve exibir o Botão de Voltar de Dentro das Pendências ...
            $nao_exibir_voltar = 1;
        }
	/*Se essa variável = '1' representa q esse arquivo está sendo requirido de dentro do 
	arquivo baixas_manipulacoes.php...*/
	if($verificar_apenas_epi != 1) {
?>
<!--*************************************************************************************-->
<!--Consultar Compras, esse arquivo de Baixas, Manipulações, faz um apontamento p/ 
o arquivo de Compras, o Roberto pediu p/ que ficasse tudo unificado em um único arquivo-->
    <tr align='center'>
        <td colspan='11'>
            <iframe style='backgroud:#ccff00' name='consultar_compras' id='consultar_compras' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='auto' title='Consultar Compra(s)' width='980' height='200' src='detalhes_compras.php?indice=<?=$_GET['indice'];?>&id_produto_insumo=<?=$id_produto_insumo;?>&txt_preco_l_fat=<?=$_GET['txt_preco_l_fat'];?>&txt_margem_lucro=<?=$_GET['txt_margem_lucro'];?>&txt_margem_lucro_desejada=<?=$_GET['txt_margem_lucro_desejada'];?>&txt_preco_compra_lista_rs=<?=$_GET['txt_preco_compra_lista_rs'];?>&acrescimo_acessorio=<?=$_GET['acrescimo_acessorio'];?>&id_orcamento_venda_item=<?=$_GET['id_orcamento_venda_item'];?>&id_fornecedor_prod_insumo=<?=$_GET['id_fornecedor_prod_insumo'];?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>&ignorar_seguranca_url=<?=$_GET['ignorar_seguranca_url'];?>&veio_vendas=<?=$_GET['veio_vendas'];?>'></iframe>
        </td>
    </tr>
<!--*************************************************************************************-->
<?
	}
	if($exibir_pendencia_pedido_compras == 1) {
?>
<!--*************************************************************************************-->
<!--Se essa variável $exibir_pendencia_pedido_compras é igual a '1', significa que esse arquivo 
 foi acessado de vendas e sendo assim não precisa ser exibida as baixa(s) / manipulação(ões) do PI, 
somente as Pendências que é o que interessa pra vendas ...-->
	<tr align='center'>
            <td colspan='11'>
                <iframe style='backgroud:#ccff00' name='pendencias_item' id='pendencias_item' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='auto' title='Consultar Pendências de Pedido(s) de Compra(s)' width='980' height='580' src='nivel_estoque/pendencias_item.php?indice=<?=$_GET['indice'];?>&id_produto_insumo=<?=$id_produto_insumo;?>&txt_preco_l_fat=<?=$_GET['txt_preco_l_fat'];?>&txt_margem_lucro=<?=$_GET['txt_margem_lucro'];?>&txt_margem_lucro_desejada=<?=$_GET['txt_margem_lucro_desejada'];?>&txt_preco_compra_lista_rs=<?=$_GET['txt_preco_compra_lista_rs'];?>&acrescimo_acessorio=<?=$_GET['acrescimo_acessorio'];?>&id_orcamento_venda_item=<?=$_GET['id_orcamento_venda_item'];?>&id_fornecedor_prod_insumo=<?=$_GET['id_fornecedor_prod_insumo'];?>&nao_exibir_voltar=<?=$nao_exibir_voltar;?>&ignorar_seguranca_url=<?=$_GET['ignorar_seguranca_url'];?>'></iframe>
            </td>
	</tr>
<!--*************************************************************************************-->
<?
            exit;
	}
?>
<!--Consultar Baixa(s) / Manipulação(ões)-->
	<tr class='linhacabecalho' align='center'>
		<td colspan='11'>
			<font color="yellow">
				Consultar Baixa(s) / Manipulação(ões) do PI: 
			</font>
			<br>
			<?=$discriminacao;?>
		</td>
	</tr>
<?
//Não existem Baixas / Manipulações do PI no período de Datas especificado ...
		if($linhas == 0) {
?>	
	
	<tr align='center'>
            <td colspan='11'>
                <?=$mensagem[1];?>
            </td>
	</tr>
	<tr align='center'>
		<td colspan='11'>
		<?
//Esse parâmetro $nao_exibir_voltar, vem de um outro arquivo que abre esta Tela como sendo Pop-Up ...
			if($nao_exibir_voltar == 0) {
		?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = '<?=$url;?>'">
		<?
			}
		?>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick='window.close()' style="color:red" class='botao'>
		</td>
	</tr>
<?
//Existe pelo menos uma Baixa / Manipulação do PI no período de Datas especificado ...
		}else {
?>
	<tr class="linhanormal" align='center'>
		<td bgcolor='#CECECE'>
			<b>Qtde</b>
		</td>
<?		
		//Aqui significa que esse arquivo aqui, está sendo requirido de dentro do arquivo baixas_manip.
		if($verificar_apenas_epi == 1) {
?>
		<td bgcolor='#CECECE'>
			<b>Discriminação</b>
		</td>
<?
		}
?>
		<td bgcolor='#CECECE'>
			<b>Retirada em</b>
		</td>
		<td bgcolor='#CECECE'>
			<font title="Responsável pelo Estoque" style="cursor:help">
				<b>Resp. pelo Estoque</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
			<b>Solicitado por</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Retirado Por</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Qtde => N.º da OP</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Estoque Final</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Observação</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Ação</b>
		</td>
		<td bgcolor='#CECECE'>
			<b>Troca / Compra</b>
		</td>
	</tr>
<?
			for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
		</td>
<?		
		//Aqui significa que esse arquivo aqui, está sendo requirido de dentro do arquivo baixas_manip.
                if($verificar_apenas_epi == 1) {
?>
		<td align='left'>
			<?=$campos[$i]['discriminacao'];?>
		</td>
<?
		}
?>
		<td>
		<?
//Esse campo será utilizado mais abaixo ...
			$data_baixa_manipulacao = substr($campos[$i]['data_sys'], 0, 10);
			echo data::datetodata($data_baixa_manipulacao, '/');
		?>
		</td>
		<td align='left'>
		<?
                    $sql = "SELECT `nome` 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                    $campos_responsavel = bancos::sql($sql);
		?>
                    <font title='<?=$campos_responsavel[0]['nome'];?>' style='cursor:help'>
                        <?=$campos_responsavel[0]['nome'];?>
                    </font>
		</td>
		<td align='left'>
		<?
                    if($campos[$i]['id_funcionario_retirado'] > 0) {
                        $sql = "SELECT `nome` AS solicitador
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_retirado']."' LIMIT 1 ";
                        $campos_solicitador = bancos::sql($sql);
		?>
                    <font title='<?=$campos_solicitador[0]['solicitador'];?>' style='cursor:help'>
                        <?=$campos_solicitador[0]['solicitador'];?>
                    </font>
                <?
                    }
                ?>
		</td>
		<td align='left'>
                    <font title='<?=$campos[$i]['retirado_por'];?>' style='cursor:help'>
                        <?=$campos[$i]['retirado_por'];?>
                    </font>
		</td>
		<td align='left'>
		<?
/*Aqui eu busco os dados na Tabela de Baixa_de_Ops através do campo id_baixa_manipulacao*/
/*************************Modo Novo*************************/
				$sql = "SELECT id_op, qtde_baixa 
						FROM `baixas_ops_vs_pis` 
						WHERE `id_baixa_manipulacao` = ".$campos[$i]['id_baixa_manipulacao']." ORDER BY id_op ";
				$campos_ops = bancos::sql($sql);
				$linhas_ops = count($campos_ops);
				if($linhas_ops == 0) {
/*Aqui eu busco os dados na Tabela de Baixa_de_Ops através do campo id_produto_insumo*/
/*************************Modo Velho*************************/
                                    $sql = "SELECT `id_op`, `qtde_baixa` 
                                            FROM `baixas_ops_vs_pis` 
                                            WHERE `id_produto_insumo` = '$id_produto_insumo' 
                                            AND SUBSTRING(`data_sys`, 1, 10) = '$data_baixa_manipulacao' 
                                            AND `id_baixa_manipulacao` = '0' ORDER BY `id_op` ";
                                    $campos_ops = bancos::sql($sql);
                                    $linhas_ops = count($campos_ops);
                                    $font = '';
				}else {
                                    $font = '<font color="darkblue"><b>';
				}
				$id_ops = '';//Sempre limpa a variável p/ não herdar valores do Loop anterior ...
				if($linhas_ops > 0) {
                                    for($j = 0; $j < $linhas_ops; $j++) {
                                        if(($j + 1) == $linhas_ops) {//Se for o último registro não quebra a linha. 
                                            $quebra_linha = '';
                                        }else {//Do contrário vai quebrando a linha ...
                                            $quebra_linha = '<br/> ';
                                        }	
                                        echo $font.number_format($campos_ops[$j]['qtde_baixa'], 2, ',', '.').' =>';
            ?>
                                    <a href = '../../producao/ops/alterar.php?passo=1&id_op=<?=$campos_ops[$j]['id_op'];?>&pop_up=1' title='Detalhes de OP' class='html5lightbox'>
                                        <?=$campos_ops[$j]['id_op'];?>
                                    </a>
            <?
                                        echo $quebra_linha;
                                    }
				}
		?>
		</td>
		<td align="right">
			<?=number_format($campos[$i]['estoque_final'], 2, ',', '.');?>
		</td>
		<td align='left'>
			<?=$campos[$i]['observacao'];?>
		</td>
		<td>
		<?
				if($campos[$i]['acao'] == 'B') {
					echo '<font color="darkblue"><b>Baixa</b></font>';
				}else if($campos[$i]['acao'] == 'E') {
					echo '<font color="red"><b>Estorno de Baixa</b></font>';
				}else if($campos[$i]['acao'] == 'M') {
					echo '<font color="darkgreen"><b>Manipulação</b></font>';
				}else if($campos[$i]['acao'] == 'L') {
					echo '<font color="purple"><b>Liberação de NF</b></font>';
				}
		?>
		</td>
		<td>
			<? 
/*Esse Iframe serve justamente p/ a alterar a Troca, mas só aparecerá p/ os usuários 
da Gladys, Roberto e Dárcio ...*/
				if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
					//Se a Troca for sim, irá virar Não ...
					$nova_troca = ($campos[$i]['troca'] == 'S') ? 'N' : 'S';
			?>		
			<a href="javascript:alterar_troca('<?=$campos[$i]['id_baixa_manipulacao'];?>', '<?=$nova_troca;?>')" style="cursor:help" title="Alterar Troca">
			<?
				}
/*************************************************************************************/
				if($campos[$i]['troca'] == 'S') {
					echo '<font color="darkblue"><b>SIM</b></font>';
				}else {
					echo '<font color="red"><b>NÃO</b></font>';
				}
			?>
			</a>
		</td>
	</tr>
<?
			}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='11'>
		<?
//Esse parâmetro $nao_exibir_voltar, vem de um outro arquivo que abre esta Tela como sendo Pop-Up ...
			if($nao_exibir_voltar == 0) {
		?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = '<?=$url;?>'">
		<?
			}
		?>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
		</td>
	</tr>
</table>
<center>
<?
    //Se foi acessada de Compras, então posso exibir os dados com paginação normalmente ...
    if(empty($_GET['id_orcamento_venda_item'])) echo paginacao::print_paginacao('sim');
?>
</center>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

- Todas as Qtde(s) que estão em azul escuro, na coluna de <b>Qtde-OP(s)</b>, estão seguindo a Nova Lógica 
que entrou em vigor a partir do dia 10/07/2008.

- Quando Troca = <b>NÃO</b>, computa CMM.
</pre>
<?	
	}
?>