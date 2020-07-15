<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/custos_new.php');
session_start('funcionarios');

//Esse parâmetro é porque essa tela também é puxada de lá da tela de Orçamentos, e daí tem conflito de sessão
if(empty($ignorar_sessao)) {
    if($tela == 1) {//Veio da tela de Todos os P.A.
        segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
    }else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
        segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
    }
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	$condicao = (!empty($chkt_so_custos_nao_liberados)) ? 'and pa.status_custo = 0' : '';
        
	global $sql, $tela;
	$tela = 1;
	switch($opt_opcao) {
		case 1:
			$sql= "select pa.*, ed.razaosocial, gpa.nome, u.unidade 
                                from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
                                where pa.referencia like '%$txt_consultar%' and pa.operacao_custo = 0 and pa.ativo = 1 and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and ged.id_grupo_pa = gpa.id_grupo_pa and ged.id_empresa_divisao = ed.id_empresa_divisao and pa.id_unidade = u.id_unidade $condicao order by pa.referencia ";
		break;
		case 2:
			$sql= "select pa.*, ed.razaosocial, gpa.nome, u.unidade 
                                from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
                                where pa.discriminacao like '%$txt_consultar%' and pa.operacao_custo = 0 and pa.ativo = 1 and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and ged.id_grupo_pa = gpa.id_grupo_pa and ged.id_empresa_divisao = ed.id_empresa_divisao and pa.id_unidade = u.id_unidade $condicao order by pa.discriminacao ";
		break;
		case 3:
			$sql= "select pa.*, ed.razaosocial, gpa.nome, u.unidade 
                                from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
                                where ed.razaosocial like '%$txt_consultar%' and pa.operacao_custo = 0 and pa.ativo = 1 and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and ged.id_grupo_pa = gpa.id_grupo_pa and ged.id_empresa_divisao = ed.id_empresa_divisao and pa.id_unidade = u.id_unidade $condicao order by ed.razaosocial ";
		break;
		default:
			$sql= "select pa.*, ed.razaosocial, gpa.nome, u.unidade 
                                from empresas_divisoes ed, gpas_vs_emps_divs ged, grupos_pas gpa, produtos_acabados pa, unidades u 
                                where pa.operacao_custo = 0 and pa.ativo=1 and pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div and ged.id_grupo_pa = gpa.id_grupo_pa and ged.id_empresa_divisao = ed.id_empresa_divisao and pa.id_unidade = u.id_unidade $condicao order by pa.discriminacao ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
            header("Location:$PHP_SELF?valor=1&id_produto_acabado_custo=$id_produto_acabado_custo");
	} else { ?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_produto_acabado, id_produto_acabado_custo) {
	var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA CLONAR ESSE PRODUTO ACABADO ?')
	if(resposta == false) {
		return false
	}else {
		window.location = 'clonagem_custo.php?passo=2&id_produto_acabado='+id_produto_acabado+'&id_produto_acabado_custo='+id_produto_acabado_custo
	}
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='720' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
	<tr>
		<td colspan='5'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='5' height="21">
			<font color='#FFFFFF' size='-1'>
				Consultar Produtos Acabados
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				<p title="Grupo P.A. (Empresa Divisão)">Grupo P.A. (E.D.)</p>
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Ref.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Discriminação
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1' title="Lote do Custo por Unidade">
				Lote / U
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			$url = "javascript:prosseguir(".$campos[$i]['id_produto_acabado'].", $id_produto_acabado_custo)";
?>
	<tr onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" class="linhanormal">
		<td width="15" onclick="<?=$url;?>">
                    <a href="#">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td width="251" onclick="<?=$url;?>">
			<a href="#">
				<?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
			</a>
		</td>
		<td>
			<?=$campos[$i]['referencia'];?>
		</td>
		<td>
				<?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>			
		</td>
		<td align='center'>
		<?
//Aki eu pego a qtde do lote do custo do P.A. Corrente
			$sql = "Select qtde_lote from produtos_acabados_custos where id_produto_acabado = ".$campos[$i]['id_produto_acabado']." and operacao_custo = ".$campos[$i]['operacao_custo']." limit 1";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]['qtde_lote'].' / '.substr($campos[$i]['unidade'], 0, 1);
		?>
		<!--<font color="red">
			<b>* </b><?=$campos2[$j]['pecas_por_emb'].' - '.$campos2[$j]['discriminacao'];?><br>
		</font>-->
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='5'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = '<?=$PHP_SELF."?id_produto_acabado_custo=".$id_produto_acabado_custo;?>'" class="botao">
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>
<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?
		}
	}else if($passo == 0) { //aqui  q muda de passo
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		for(i = 0; i < 3; i ++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled=true
		document.form.txt_consultar.value=''
	}else {
		for(i = 0; i < 3;i ++) {
			document.form.opt_opcao[i].disabled = false
		}
		document.form.txt_consultar.disabled=false
		document.form.txt_consultar.value=''
		document.form.txt_consultar.focus()
	}
}
function iniciar() {
	document.form.txt_consultar.focus()
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
</script>
</head>
<body onLoad="return iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_produto_acabado_custo' value='<?=$id_produto_acabado_custo;?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Produtos Acabados
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='2'>
			<div align='center'>Consultar
				<input type="text" name="txt_consultar" size=45 maxlength=45 class="caixadetexto">
			</div>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" onclick="return iniciar()"; title="Consultar Produtos Acabados por: Referência" id='label'>
			<label for='label'>
				Referência
			</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" checked value="2" onClick="return iniciar()"; title="Consultar Produtos Acabados por: Discriminação" id='label2'>
			<label for='label2'>
				Discrimina&ccedil;&atilde;o
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type="radio" name="opt_opcao" value="3" onClick="return iniciar()"; title="Consultar Produtos Acabados por: Empresa Divisão" id='label3'>
			<label for='label3'>
				Empresa Divisão
			</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' tabindex='4' title="Só Custos não Liberados" class="checkbox" id='label4'>
			<label for='label4'>
				Só Custos não Liberados
			</label>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width="20%">
			<input type='checkbox' name='opcao' onClick='limpar()' value='4' tabindex='5' title="Consultar todos os Produtos Acabados" class="checkbox" id='label5'>
			<label for='label5'>
				Todos os registros
			</label>
		</td>
		<td width="20%">
			 &nbsp;
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" tabindex="6" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" tabindex="5" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

* Traz somente P.A(s) do:
<b>* Tipo de O.C. = Industrializado.</b>
</pre>
<?} else if($passo == 2) {
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// id_produto_acabado_custo ====> custo_corrente
// id_produto_acabado_custo_clo ====> custo base para clonar
// Apago todas as tabelas relacional com este custo
// Procuro o id_custo
$id_produto_acabado_corrent_temp=$id_produto_acabado;
$sql="select pac.id_produto_acabado_custo ";
$sql.="from produtos_acabados_custos pac, produtos_acabados pa ";
$sql.="where pac.id_produto_acabado=pa.id_produto_acabado and pac.id_produto_acabado=$id_produto_acabado and pac.operacao_custo=0 LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas=count($campos);
if($linhas>0) {//clono o custo
	$id_produto_acabado_custo_clo=$campos[0]['id_produto_acabado_custo'];
	if($id_produto_acabado_custo==$id_produto_acabado_custo_clo) {
		echo 'NEM O Dr. ALBIERE CONSEGUE CLONAR ELE NELE MESMO.';
		exit;
	}
} else {
	echo 'NÃO FOI POSSÍVEL ENCONTRAR O CUSTO DESTE PRODUTO.';
	exit;
}

$sql="delete from pacs_vs_maquinas where id_produto_acabado_custo=$id_produto_acabado_custo ";
bancos::sql($sql);
$sql="delete from pacs_vs_pis where id_produto_acabado_custo=$id_produto_acabado_custo ";
bancos::sql($sql);
$sql="delete from pacs_vs_pas where id_produto_acabado_custo=$id_produto_acabado_custo ";
bancos::sql($sql);
$sql="delete from pacs_vs_pis_trat where id_produto_acabado_custo=$id_produto_acabado_custo ";
bancos::sql($sql);
$sql="delete from pacs_vs_pis_usis where id_produto_acabado_custo=$id_produto_acabado_custo ";
bancos::sql($sql);

//Seleciono todos os relacionamento de origem para clona-lo
$sql="select id_maquina, tempo_hs from pacs_vs_maquinas where id_produto_acabado_custo=$id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas=count($campos);
if($linhas>0) {//clono o custo
	for($i=0;$i<$linhas;$i++) {
		$id_maquina	= $campos[$i]['id_maquina'];
		$tempo_hs	= $campos[$i]['tempo_hs'];
		$sql="insert into pacs_vs_maquinas (id_produto_acabado_custo, id_maquina, tempo_hs) values($id_produto_acabado_custo, $id_maquina, $tempo_hs) ";
		bancos::sql($sql);
	}
}

$sql="select id_produto_insumo, qtde from pacs_vs_pis where id_produto_acabado_custo=$id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas=count($campos);
if($linhas>0) {//clono o custo
	for($i=0;$i<$linhas;$i++) {
		$id_produto_insumo	= $campos[$i]['id_produto_insumo'];
		$qtde				= $campos[$i]['qtde'];
		$sql="insert into pacs_vs_pis (id_produto_acabado_custo, id_produto_insumo, qtde) values($id_produto_acabado_custo, $id_produto_insumo, $qtde) ";
		bancos::sql($sql);
	}
}

//clonagem da 7º etapa
$sql="select id_produto_acabado from produtos_acabados_custos where id_produto_acabado_custo=$id_produto_acabado_custo ";
$campos_pa = bancos::sql($sql);
$id_produto_acabado_principal=$campos_pa[0]['id_produto_acabado'];
$sql="select id_produto_acabado, qtde from pacs_vs_pas where id_produto_acabado_custo=$id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas=count($campos);
if($linhas>0) {//clono o custo
	for($i=0;$i<$linhas;$i++) {
		$id_produto_acabado	= $campos[$i]['id_produto_acabado'];//PA  Principal
		$qtde				= $campos[$i]['qtde'];
		
		if(custos::vasculhar_pa($id_produto_acabado_principal, $id_produto_acabado)) {
			$alert=1;
		} else {
			$sql="insert into pacs_vs_pas (id_produto_acabado_custo, id_produto_acabado, qtde) values($id_produto_acabado_custo, $id_produto_acabado, $qtde) ";
			bancos::sql($sql);
		}
	}
}


$sql="select id_produto_insumo, fator, peso_aco, peso_aco_manual from pacs_vs_pis_trat where id_produto_acabado_custo=$id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas=count($campos);
if($linhas>0) {//clono o custo
	for($i=0;$i<$linhas;$i++) {
		$id_produto_insumo	= $campos[$i]['id_produto_insumo'];
		$fator			= $campos[$i]['fator'];
		$peso_aco		= $campos[$i]['peso_aco'];
		$peso_aco_manual	= $campos[$i]['peso_aco_manual'];
		$sql = "insert into pacs_vs_pis_trat (id_produto_acabado_custo, id_produto_insumo, fator, peso_aco, peso_aco_manual) values($id_produto_acabado_custo, $id_produto_insumo, $fator, $peso_aco, $peso_aco_manual) ";
		bancos::sql($sql);
	}
}

$sql="select * from produtos_acabados_custos where id_produto_acabado_custo=$id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//clono o custo
	$id_produto_acabado_clo	= $campos[0]['id_produto_acabado'];
	$id_produto_insumo	= $campos[0]['id_produto_insumo'];
	$qtde_lote			= $campos[0]['qtde_lote'];
	$peso_kg			= $campos[0]['peso_kg'];
	$peca_corte			= $campos[0]['peca_corte'];
	$comprimento_1		= $campos[0]['comprimento_1'];
	$comprimento_2		= $campos[0]['comprimento_2'];
	//$sql="insert into pacs_vs_maquinas values('', $id_produto_acabado_custo, $id_maquina, $tempo_hs) ";
	$sql="update produtos_acabados_custos set qtde_lote=$qtde_lote, id_produto_insumo=$id_produto_insumo, peso_kg=$peso_kg, peca_corte=$peca_corte, comprimento_1=$comprimento_1, comprimento_2=$comprimento_2  where id_produto_acabado_custo=$id_produto_acabado_custo ";
	bancos::sql($sql);
}

$sql="select id_produto_insumo, qtde from pacs_vs_pis_usis where id_produto_acabado_custo = $id_produto_acabado_custo_clo ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {//clono o custo
	for($i = 0; $i < $linhas; $i++) {
		$id_produto_insumo	= $campos[$i]['id_produto_insumo'];
		$qtde			= $campos[$i]['qtde'];
		$sql = "insert pacs_vs_pis_usis (id_produto_acabado_custo, id_produto_insumo, qtde) values($id_produto_acabado_custo, $id_produto_insumo, $qtde) ";
		bancos::sql($sql);
	}
}
?>
<Script Language = 'JavaScript'>
    if('<?=$alert;?>' == '1') alert('NEM TODOS OS P.A. DA 7ª ETAPA PODERAM SER CLONADOS !')
    alert('CLONAGEM REALIZADA COM SUCESSO !')
    window.close()
    window.opener.document.form.submit()
</Script>
<?}?>