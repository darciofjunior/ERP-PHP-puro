<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PROCESSO DE OP EXCLUÍDO COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $chkt_ops_aberto    = $_POST['chkt_ops_aberto'];
        $id_op              = $_POST['id_op'];
        $opt_opcao          = $_POST['opt_opcao'];
        $txt_consultar      = $_POST['txt_consultar'];
    }else {
        $chkt_ops_aberto    = $_GET['chkt_ops_aberto'];
        $id_op              = $_GET['id_op'];
        $opt_opcao          = $_GET['opt_opcao'];
        $txt_consultar      = $_GET['txt_consultar'];
    }
/********************************************************/
    if(!empty($chkt_ops_aberto)) $condicao = " AND o.`status_finalizar` = '0' ";
/********************************************************/
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT o.*, pa.referencia 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = o.id_produto_acabado AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') AND pa.`referencia` LIKE '%$txt_consultar%' 
                    WHERE o.`ativo` = '1' 
                    $condicao 
                    ORDER BY o.id_op DESC ";
        break;
        case 2:
            $sql = "SELECT o.*, pa.referencia 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = o.id_produto_acabado AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') AND pa.`discriminacao` LIKE '%$txt_consultar%' 
                    WHERE o.`ativo` = '1' 
                    $condicao 
                    ORDER BY o.id_op DESC ";
        break;
        case 3:
            $sql = "SELECT o.*, pa.referencia 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = o.id_produto_acabado AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') 
                    WHERE o.`id_op` LIKE '%$txt_consultar%' 
                    AND o.`ativo` = '1' 
                    $condicao 
                    ORDER BY o.id_op DESC ";
        break;
        default:
            $sql = "SELECT o.*, pa.referencia 
                    FROM `ops` o 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = o.id_produto_acabado AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') 
                    WHERE o.`ativo` = '1' 
                    $condicao 
                    ORDER BY o.id_op DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'controle_processo.php?valor=1'
        </Script>
<?
    }else {
        $sql_todos_itens = $sql;//Essa variável será utilizada mais abaixo ...
?>
<html>
<head>
<title>.:: Controle de Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function imprimir_op(id_op, referencia) {
    if(referencia == 'ESP') alert('ANTES DE IMPRIMIR NÃO SE ESQUEÇA DE MUDAR O PAPEL PARA \n\n\nA   M   A   R   E   L   O !')
    nova_janela('../relatorio/relatorio.php?id_op='+id_op, 'POP', '', '', '', '', 750, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Controle de Produção
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OP
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde Nominal / Restante
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Prazo de Entrega
        </td>
        <td>
            <font title="Funcionário e Data da Ocorrência" style="cursor:help">
                Func e Data Ocorr
            </font>
        </td>
    </tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
			$url = "controle_processo.php?passo=2&id_op=".$campos[$i]['id_op'];
			estoque_acabado::atualizar_producao($campos[$i]['id_produto_acabado']);
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td width="10" onclick="window.location = '<?=$url;?>'">
                    <a href="#" class="link">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="window.location = '<?=$url;?>'" align='center'>
			<a href="#" class="link">
				<?=$campos[$i]['id_op'];?>
			</a>
		</td>
		<td align="left">
			<?=$campos[$i]['referencia'];?>
		</td>
		<td align="left">
			<?
                            echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
                            if($campos[$i]['desenho_para_op'] == '') {//Não existe desenho no Produto Acabado ...
                        ?>
                                &nbsp;<img src='../../../imagem/folha_em_branco.png' width='12' height='12' border='0' title='Não Existe Desenho no Produto Acabado'>
                        <?
                            }else {//Já consta desenho anexado
                        ?>
                                &nbsp;<img src='../../../imagem/folha_preenchida.png' width='12' height='12' border='0' title='Existe Desenho no Produto Acabado'>
                        <?
                            }
                        ?>
			&nbsp;<img src="../../../../imagem/impressora.gif" border="0" onclick="imprimir_op('<?=$campos[$i]['id_op'];?>', '<?=$campos[$i]['referencia'];?>')" title="Imprimir OP" alt="Imprimir OP" style="cursor:pointer">
			<?
				if($campos[$i]['lote_diferente_custo'] == 'S') {//Não existe desenho anexado ...
			?>
			&nbsp;<img src="../../../../imagem/ponto_interrogacao_vermelho.png" width="16" height="16" border="0" title="Lote Dif. > 15% do Custo" alt="Lote Dif. > 15% do Custo" style="cursor:pointer">	
			<?		
				}
			?>
		</td>
		<td>
			<?
				echo number_format($campos[$i]['qtde_produzir'], 2, ',', '.').' / ';
				//Busca tudo o que foi produzido da OP ...
				$sql = "SELECT SUM(bop.qtde_baixa) AS qtde_produzido 
						FROM `ops` 
						INNER JOIN `baixas_ops_vs_pas` bop ON bop.id_op = ops.id_op and bop.id_produto_acabado = ops.id_produto_acabado 
						WHERE ops.`status_finalizar` = '0' 
						AND ops.id_op = '".$campos[$i]['id_op']."' ";
				$campos_produzido 	= bancos::sql($sql);
				$qtde_restante 		= $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];
				echo number_format($qtde_restante, 2, ',', '.');
			?>
		</td>
		<td>
			<?=data::datetodata($campos[$i]['data_emissao'], '/');?>
		</td>
		<td>
			<?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
		</td>
		<td>
		<?
//Busca do Nome do Funcionário e da Data de Ocorrência de alteração da última OP ...
                    $sql = "SELECT nome 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_ocorrencia']."' LIMIT 1 ";
                    $campos_nome = bancos::sql($sql);
                    if(count($campos_nome) == 1) {
//Aqui eu só listo o primeiro nome ...
                        echo strtok($campos_nome[0]['nome'], ' ').' - '.data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/').' - '.substr($campos[$i]['data_ocorrencia'], 11, 8);
                    }
                ?>
		</td>
	</tr>
<?
		}
		//Aqui eu faço o mesmo SQL só que dessa vez sem paginar ...
		$campos = bancos::sql($sql_todos_itens);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
                    //Busca tudo o que foi produzido da OP ...
                    $sql = "SELECT SUM(bop.qtde_baixa) AS qtde_produzido 
                            FROM `ops` 
                            INNER JOIN `baixas_ops_vs_pas` bop ON bop.id_op = ops.id_op and bop.id_produto_acabado = ops.id_produto_acabado 
                            WHERE ops.`status_finalizar` = '0' 
                            AND ops.id_op = '".$campos[$i]['id_op']."' ";
                    $campos_produzido 	= bancos::sql($sql);
                    $qtde_restante 		= $campos[$i]['qtde_produzir'] - $campos_produzido[0]['qtde_produzido'];

                    $sql = "SELECT ged.desc_medio_pa, (pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1 - ged.desc_base_b_nac / 100) * (1 + ged.acrescimo_base_nac / 100)) AS preco_list_desc 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                            WHERE pa.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_preco_unit 	= bancos::sql($sql);
                    $preco_lista 	= ($campos_preco_unit[0]['desc_medio_pa'] > 0) ? $campos_preco_unit[0]['preco_list_desc'] * $campos_preco_unit[0]['desc_medio_pa'] : $campos_preco_unit[0]['preco_list_desc'];

                    if($qtde_restante > 0 && $preco_lista > 0) $valor_total_rs+= $qtde_restante * $preco_lista;
		}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'controle_processo.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
    if(!empty($_GET['id_op_processo'])) {//Exclusão dos Processos
        $sql = "DELETE FROM `ops_vs_processos` WHERE `id_op_processo` = '$_GET[id_op_processo]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }

    $sql = "SELECT op.*, f.`nome` AS funcionario, m.`nome`, mcm.`codigo_maquina`, mo.`operacao` 
            FROM `ops_vs_processos` op 
            INNER JOIN `maquinas` m ON m.`id_maquina` = op.`id_maquina` 
            INNER JOIN `maquinas_vs_codigos_maquinas` mcm ON mcm.`id_maquina_codigo_maquina` = op.`id_maquina_codigo_maquina` 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = op.`id_funcionario` 
            INNER JOIN `maquinas_vs_operacoes` mo ON mo.`id_maquina_operacao` = op.`id_maquina_operacao` 
            WHERE op.id_op = '$_GET[id_op]' ";
    $campos_ops	= bancos::sql($sql);
    $linhas_ops = count($campos_ops);
    if($linhas_ops > 0) {//Existe pelo menos 1 processo vinculado a OP ...
?>
<html>
<head>
<title>.:: Controle de Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_op_processo) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE PROCESSO ?')
    if(mensagem == false) {
        return false
    }else {
        document.location = 'controle_processo.php?passo=2&id_op=<?=$_GET['id_op']?>&id_op_processo='+id_op_processo
    }
}
</Script>
<body>
<p>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='12'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Relatório de Processo(s) da OP N.º
            <font color="yellow">
                <?=$id_op;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Máquina
        </td>
        <td>
            Cod. Máquina
        </td>
        <td>
            Func.
        </td>
        <td>
            Operação
        </td>
        <td>
            Data Inicial
        </td>
        <td>
            Hora Inicial
        </td>
        <td>
            Data Final
        </td>
        <td>
            Hora Final
        </td>
        <td>
            Qtde Produzida
        </td>
        <td>
            &nbsp;
        </td>				
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_ops; $i++) {
?>	
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_ops[$i]['nome'];?>
        </td>
        <td>
            <?=$campos_ops[$i]['codigo_maquina'];?>
        </td>
        <td align='left'>
            <?=$campos_ops[$i]['funcionario'];?>
        </td>
        <td>
            <?=$campos_ops[$i]['operacao'];?>
        </td>
        <td>
            <?=data::datetodata($campos_ops[$i]['data_inicial'], '/');?>
        </td>
        <td>
            <?=$campos_ops[$i]['hora_inicial'];?>
        </td>
        <td>
            <?=$data_final = ($campos_ops[$i]['data_final'] == '0000-00-00') ? '&nbsp;' : data::datetodata($campos_ops[$i]['data_final'], '/');?>
        </td>
        <td>
            <?=$hora_final = ($campos_ops[$i]['hora_final'] == '00:00:00') ? '&nbsp;' : $campos_ops[$i]['hora_final'];?>
        </td>
        <td>
            <?=$qtde_produzida = ($campos_ops[$i]['qtde_produzida'] == '0') ? '&nbsp;' : $campos_ops[$i]['qtde_produzida'];?>
        </td>
        <td>
            <img src = '../../../../imagem/menu/adicao.jpeg' border='0' onclick="html5Lightbox.showLightbox(7, 'incluir_processo.php?id_op_processo=<?=$campos_ops[$i]['id_op_processo'];?>')" alt='Vincular Processo' title='Vincular Processo' width='16' height='16'>
        </td>	
        <td>
            <img src = '../../../../imagem/menu/alterar.png' border='0' onClick="html5Lightbox.showLightbox(7, 'alterar_processo.php?id_op_processo=<?=$campos_ops[$i]['id_op_processo'];?>')" alt='Alterar Processo' title='Alterar Processo'>
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' onClick="excluir_item('<?=$campos_ops[$i]['id_op_processo'];?>')" alt='Excluir' title='Excluir'>
        </td>																							
    </tr>
<?		
        }
?>	
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'controle_processo.php<?=$parametro;?>'" style='color:red' class='botao'>
            <input type='button' name='cmd_incluir_processo' value='Incluir Processo' title='Incluir Processo' onclick="html5Lightbox.showLightbox(7, 'incluir_processo.php?id_op=<?=$id_op;?>')" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }else {//Não existe nenhum processo vinculado a OP ...
?>
<html>
<head>
<title>.:: Controle de Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<body>
<p>
<table width='90%' border="0" cellspacing="1" cellpadding="1" align='center'>
    <tr class="atencao" align='center'>
        <td>
            NÃO EXISTE(M) PROCESSO(S) ATRELADO(S) PARA ESTA OP.
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'controle_processo.php<?=$parametro;?>'" style='color:red' class='botao'>
            <input type="button" name="cmd_incluir_processo" value="Incluir Processo" title="Incluir Processo" onclick="html5Lightbox.showLightbox(7, 'incluir_processo.php?id_op=<?=$id_op;?>')" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
    //Aqui chama os Detalhes da OP para o Usuário visualizar ...
    $nao_chamar_biblioteca  = 1;
    $niveis                 = '../../../../';
    require('../detalhes.php');
}else {
?>
<html>
<head>
<title>.:: Controle de Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Controle de Produção
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Referência" id='label' checked>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Acabados por: Discriminação" id='label2'>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="radio" name="opt_opcao" value="3" onclick="document.form.txt_consultar.focus()" title="Consultar OPs por: Número da OP" id='label3'>
            <label for='label3'>
                Número da OP
            </label>
        </td>
        <td width="20%">
            <input type='checkbox' name='chkt_ops_aberto' value='1' title="Somente OP(s) em Aberto" id='label4' class="checkbox" checked>
            <label for='label4'>
                Somente OP(s) em Aberto
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todas as OPs" onClick='limpar()' id='label5' class='checkbox'>
            <label for='label5'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>