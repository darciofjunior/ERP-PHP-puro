<?
require('../../../../../lib/pdf/fpdf.php');
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/data.php');
session_start('funcionarios');

$cmb_empresa="t";//passo direto pq tive que desabilitar o combo, por causa que o premio pe pago pelo grupo e � emcima das tr�s empresas.
//Referente ao pr�mio que os Vendedores conquistam com a Meta ...
$mes_ref_sg = (string)data::mes((int)date('m'));
$mes_ref_sg = substr($mes_ref_sg, 0, 3).date('/Y');
$mes_ref = date('m');
$ano_ref = date('Y');
$_representante = (empty($cmb_representante))? " like '%'" : "=".$cmb_representante; //existe este macete por causa do loop de todas as comiss�es 
$sql = "Select id_representante, comissao_meta_atingida, comissao_meta_atingida_sup 
		from comissoes_extras 
		where MONTH(data_periodo_fat) = '$mes_ref' 
		and YEAR(data_periodo_fat) = '$ano_ref' 
		and id_representante $_representante ";
$campos_perc_extra= bancos::sql($sql);
$linhas_perc_extra=count($campos_perc_extra);
if($linhas_perc_extra==0) {
	$comissao_meta_atingida_perc[$cmb_representante]		= 0;
	$comissao_meta_atingida_sup_perc[$cmb_representante]	= 0;
} else {
	for($i=0;$i<$linhas_perc_extra;$i++) {
		$comissao_meta_atingida_perc[$campos_perc_extra[$i]['id_representante']]		= $campos_perc_extra[$i]['comissao_meta_atingida'];
		$comissao_meta_atingida_sup_perc[$campos_perc_extra[$i]['id_representante']]	= $campos_perc_extra[$i]['comissao_meta_atingida_sup'];
	}
}
function comissao_noronha($data_inicial, $data_final) {
/*
	
//Faturamento ...
	$sql = "Select sum(nfsi.qtde*nfsi.valor_unitario) total, nfs.id_nf, nfs.id_empresa 
			from nfs 
			inner join nfs_itens nfsi on nfsi.id_nf = nfs.id_nf 
			where nfs.data_emissao between '$data_inicial' and '$data_final' group by nfs.id_empresa order by nfs.id_empresa ";
	$campos_faturado_nf = bancos::sql($sql);
	$linhas_faturado_nf = count($campos_faturado_nf);
	for($i = 0; $i < $linhas_faturado_nf; $i++) {//Disparo do Loop ...
		if($campos_faturado_nf[$i]['id_empresa'] == 1) {//Alba
			$GLOBALS['fat_alba'] = $campos_faturado_nf[$i]['total'];
		}else if($campos_faturado_nf[$i]['id_empresa'] == 2) {//Tool
			$GLOBALS['fat_tool'] = $campos_faturado_nf[$i]['total'];
		}else if($campos_faturado_nf[$i]['id_empresa'] == 4) {//Grupo
			$GLOBALS['fat_grupo'] = $campos_faturado_nf[$i]['total'];
		}
	}
	//Nova Devolu��o...
	
	$sql = "Select sum(nfsi.qtde_devolvida*nfsi.valor_unitario)*(-1) total, nfs.id_empresa
			from nfs 
			inner join nfs_itens nfsi on nfsi.id_nf = nfs.id_nf 
			where nfs.data_emissao between '$data_inicial' and '$data_final' 
			and nfs.status = 6 group by nfs.id_empresa order by nfs.id_empresa ";
	$campos_devolucao_nf=bancos::sql($sql);
	$linhas = count($campos_devolucao_nf);
	for($dev = 0; $dev < $linhas; $dev++) {
		if($campos_devolucao_nf[$dev]['id_empresa']==1) {//Albafer
			$dev_alba = $campos_devolucao_nf[$dev]['total'];
		}else if($campos_devolucao_nf[$dev]['id_empresa']==2) {//Tool
			$dev_tool = $campos_devolucao_nf[$dev]['total'];
		}else if($campos_devolucao_nf[$dev]['id_empresa']==4) {//Grupo
			$dev_grupo = $campos_devolucao_nf[$dev]['total'];
		}
	}
//Abatimento Manual e atigas devolu��es
	$sql = "Select if(tipo_lancamento=3,sum(nfsd.valor_duplicata), (sum(nfsd.valor_duplicata)*(-1))) total, 
			nfs.id_empresa 	
			from nfs_devolucoes nfsd
			inner join nfs on nfs.id_nf=nfsd.id_nf
			where substring(nfsd.data_lancamento,1,10) between '$data_inicial' and '$data_final' 
			group by nfsd.tipo_lancamento, nfs.id_empresa order by nfs.id_empresa ";
	$campos_atraso = bancos::sql($sql);
	$linhas = count($campos_atraso);
	for($aba=0;$aba<$linhas;$aba++) {//atraso + reembolso
		if($campos_atraso[$aba]['id_empresa']==1) {//Albafer
			$atraso_alba+=$campos_atraso[$aba]['total'];
		}else if($campos_atraso[$aba]['id_empresa']==2) {//Tool
			$atraso_tool+=$campos_atraso[$aba]['total'];
		}else if($campos_atraso[$aba]['id_empresa']==4) {//Grupo
			$atraso_grupo+=$campos_atraso[$aba]['total'];
		}
	}
	$GLOBALS['fat_alba']+=$dev_alba+$atraso_alba;
	$GLOBALS['fat_tool']+=$dev_tool+$atraso_tool;
	$GLOBALS['fat_grupo']+=$dev_grupo+$atraso_grupo;*/

	$GLOBALS['fat_alba'] = 0;
	$GLOBALS['fat_tool'] = 0;
	$GLOBALS['fat_grupo']= 0;
	return true;
}

//Busca a pr�xima Data do Holerith, maior do que a Data Final digitada pelo usu�rio no Filtro ...
$sql = "Select id_vale_data, data, qtde_dias_uteis_mes, qtde_dias_inuteis_mes 
		from `vales_datas` 
		where data > '$data_final' limit 1 ";
$campos_data = bancos::sql($sql);
if(count($campos_data) == 1) {//Se encontrar na Base de Dados ...
	$id_vale_data = $campos_data[0]['id_vale_data'];
	$data_holerith = $campos_data[0]['data'];
	$qtde_dias_uteis_mes = $campos_data[0]['qtde_dias_uteis_mes'];
	$qtde_dias_inuteis_mes = $campos_data[0]['qtde_dias_inuteis_mes'];
}else {//Se n�o encontrar, ent�o ...
	$qtde_dias_uteis_mes = 0;
	$qtde_dias_inuteis_mes = 0;
}

//Essas vari�veis v�o servir p/ o controle de Impress�o do Relat�rio de Comiss�o em PDF ...
if($qtde_dias_uteis_mes == 0 && $qtde_dias_inuteis_mes == 0) {
?>
		<Script Language = 'JavaScript'>
			alert('O "CAMPO DIAS �TEIS" E O "CAMPO DOMINGOS E FERIADOS" S�O = 0 !\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
			window.close()
		</Script>
<?
	exit;
}else if($qtde_dias_uteis_mes > 0 && $qtde_dias_inuteis_mes == 0) {
?>
		<Script Language = 'JavaScript'>
			alert('O "CAMPO DIAS �TEIS" = 0 !\nENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
			window.close()
		</Script>
<?
	exit;
}else if($qtde_dias_uteis_mes == 0 && $qtde_dias_inuteis_mes > 0) {
?>
		<Script Language = 'JavaScript'>
			alert('O "CAMPO DOMINGOS E FERIADOS" = 0 !\ENTRE EM CONTATO COM O DEPARTAMENTO PESSOAL !')
			window.close()
		</Script>
<?
	exit;
}

//error_reporting(0);
function rotulo($moeda) { // porq chama mais de uma vez por causa da paginacao
	global $pdf, $banco;
	$pdf->SetLeftMargin(1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial', 'B', 8);
	$pdf->Cell($GLOBALS['ph']*18, 5, 'DATA DE EMISS�O (NF)', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*10, 5, 'N� DA NF', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*37, 5, 'CLIENTE', 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*14, 5, 'VENDAS. '.$moeda, 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*8, 5, 'COMIS. '.$moeda, 1, 0, 'C');
	$pdf->Cell($GLOBALS['ph']*13, 5, 'COMIS. M�DIA %', 1, 1, 'C');
}

function Heade($data_inicial, $data_final, $data_holerith, $cmb_representante, $cmb_empresa) {
	global $pdf, $banco;
	$pdf->SetFont('Arial', 'B', 12);
	//Empresa
	if($cmb_empresa == 1) {
		$empresa = 'ALBAFER';
	}else if($cmb_empresa == 2) {
		$empresa = 'TOOL MASTER';
	}else if($cmb_empresa == 4) {
		$empresa = 'GRUPO';
	}else {
		$empresa = 'OUTROS';
	}
	$pdf->Cell(120, 5, 'RELAT�RIO DE COMISS�ES - '.$empresa, 'LBT', 0, 'R');
	//Aqui � Padr�o para todas as Empresas
	$pdf->SetFont('Arial', 'BI', 9);
	$pdf->Cell(85, 5, ' -   Impress�o: '.date('d/m/Y').' - '.date('H:i:s'), 'RBT', 1, 'L');
	//Continuando ...
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(40, 5, 'Data Inicial: '.data::datetodata($data_inicial, '/'), 1, 0, 'C');
	$pdf->Cell(40, 5, 'Data Final: '.data::datetodata($data_final, '/'), 1, 0, 'C');
	$pdf->Cell(49, 5, 'Data de Holerith: '.data::datetodata($data_holerith, '/'), 1, 0, 'C');
	//Busca do Nome do Representante
	$sql = "Select nome_fantasia from representantes where id_representante = '$cmb_representante' limit 1 ";
	$campos = $GLOBALS['bancos']->sql($sql);
	$nome_fantasia = $campos[0]['nome_fantasia'];
	/*****************************************************/
	$pdf->Cell(60, 5, 'Relat�rio por: '.$nome_fantasia, 1, 0, 'C');
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(16, 5, 'P�g: '.$GLOBALS['num_pagina'], 1, 1, 'C');
	$pdf->Ln(1);
	$pdf->Line(1*$GLOBALS['ph'],23,101.5*$GLOBALS['ph'],23);
}

/////////////////////////////////////// IN�CIO PDF /////////////////////////////////////////////////////////
define('FPDF_FONTPATH', 'font/');
$tipo_papel		= "P";  // P=> Retrato L=>Paisagem
$unidade		= "mm"; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel	= "A4"; // A3, A4, A5, Letter, Legal
$pdf=new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->SetLeftMargin(1);
$pdf->Open();
global $pv,$ph; //valor baseado em mm do A4
if($formato_papel=="A4") {
	if($tipo_papel=="P") {
		$pv=295/100;
		$ph=205/100;
	}else {
		$pv=205/100;
		$ph=295/100;
	}
} else {
	echo "Formato n�o definido";
}

if(strtoupper($cmb_empresa)=="T") {
	$empresas[]=1;//alba
	$empresas[]=2;//tool
	$empresas[]=4;//grupo
} else {
	$empresas[]=$cmb_empresa;//empresa selecionada pela combo
}

//Heade($data_inicial, $data_final, $cmb_representante, $cmb_empresa);
$pdf->SetFont('Arial', '', 10);

//Significa que se deseja gerar a comiss�o p/ todos os representantes
if(empty($cmb_representante)) {
	$representante = '%';
}else {//Apenas um �nico representante ...
	$representante = $cmb_representante;
}

$sql = "Select id_representante from representantes 
		where id_representante like '$representante' and ativo=1 order by nome_fantasia ";
$campos_representante = bancos::sql($sql);//traz todos representante
$total_representantes = count($campos_representante);
if($total_representantes>0) {
	if($total_representantes>5) {//se form maior que 5 representante de uma s� vez executo o set time para ampliar o tempo de excu��o
		ini_set('max_execution_time', '1000');
	}
	for($a=0;$a<$total_representantes;$a++) {
		$total_geral_premio=0;//zero por causa do loop da comiss�o quando � gerado para todos os representantes
		$cmb_representante = $campos_representante[$a]['id_representante'];
//Aqui eu verifico se o Representante � um Funcion�rio ...
		$sql = "Select f.id_cargo, f.id_empresa, f.id_pais 
				from representantes r 
				inner join representantes_vs_funcionarios rf on rf.id_representante=r.id_representante 
				inner join funcionarios f on f.id_funcionario=rf.id_funcionario 
				where r.id_representante='$cmb_representante'";
		$campos_rep_func = bancos::sql($sql,0,1);//certifico que o rep � funcionario
		if(count($campos_rep_func)>0) {//Signifca que o representante � funcionario
			$id_pais 			= $campos_rep_func[0]['id_pais'];
			$id_cargo_func   	= $campos_rep_func[0]['id_cargo'];//representante externo id_cargo=>27 ou id_cargo=>25 => supervisor � para tratar como vend. externo nova l�gica 109=> super interno de vendas
			$id_empresa_func 	= $campos_rep_func[0]['id_empresa'];
		}else {
//Se n�o for, ent�o eu busco o id_pais direto da Tabela de Representantes ...
			$sql = "SELECT id_pais 
					FROM `representantes` 
					WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
			$campos				= bancos::sql($sql);
			$id_pais 			= $campos[0]['id_pais'];
			$id_cargo_func   	= 0;
			$id_empresa_func 	= 0;
		}
		$sub_total_supervisor = 0;
//Se o Representante for do Brasil ent�o ...
		$campo_valor 	= ($id_pais == 31) ? ' nfsi.valor_unitario ' : ' nfsi.valor_unitario_exp ';
		$moeda 			= ($id_pais == 31) ? 'R$ ' : 'U$ ';
		for($emp = 0; $emp < count($empresas); $emp++) {//caso for mais de uma empresa criar� um for para disparar
			$id_empresas = $empresas[$emp];
			$sql = "Select nfs.id_nf, nfs.id_empresa, nfs.snf_devolvida, nfs.data_emissao, nfs.suframa, nfs.status, ovi.comissao_perc, sum(round((((nfsi.qtde*nfsi.valor_unitario)*ovi.comissao_perc)/100),2)) valor_comissao, sum(round((nfsi.qtde*nfsi.valor_unitario),2)) tot_mercadoria, 
					if(c.nomefantasia='', c.razaosocial, c.nomefantasia) cliente, c.id_pais, 
					if(nfs.status=6, (sum(round((nfsi.qtde_devolvida*$campo_valor),2))*(-1)), sum(round((nfsi.qtde*$campo_valor),2)) ) tot_mercadoria, 
					if(nfs.status=6, (sum(round((((nfsi.qtde_devolvida*$campo_valor)*ovi.comissao_perc)/100),2))*(-1)), sum(round((((nfsi.qtde*$campo_valor)*ovi.comissao_perc)/100),2)) ) valor_comissao 
					from nfs_itens nfsi 
					inner join nfs on nfs.id_nf=nfsi.id_nf 
					inner join pedidos_vendas_itens pvi on pvi.id_pedido_venda_item=nfsi.id_pedido_venda_item 
					inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item=pvi.id_orcamento_venda_item 
					inner join clientes c on c.id_cliente = nfs.id_cliente 
					where nfs.data_emissao between '$data_inicial' and '$data_final' and ovi.id_representante = '$cmb_representante' and nfs.id_empresa='$id_empresas' 
					group by nfsi.id_nf order by nfs.data_emissao ";
			$campos = $GLOBALS['bancos']->sql($sql);
			$linhas = count($campos);
			//Todo esse controle � para n�o Imprimir Comiss�es com Valores Zerados ...
			if($linhas > 0 || ($linhas == 0 && $id_empresas == 4)) {
				if($linhas > 0) {
					$pdf->AddPage();
				}else {//Esse Controle aqui � somente para o Grupo por causa da Supervis�o ...
					/*Infelizmente esse SQL � uma query que est� l� embaixo, mas para controlar essa parte, fui obrigado 
					a fazer essa redund�ncia ...*/
					$sql = "Select rs.id_representante, 
							if(r.nome_fantasia='',r.nome_representante,r.nome_fantasia) representante 
							from representantes r 
							inner join representantes_vs_supervisores rs on rs.id_representante=r.id_representante 
							where rs.id_representante_supervisor='$cmb_representante' order by representante ";
					$campos_sub= bancos::sql($sql);
					$linhas_sub=count($campos_sub);
					for($r = 0; $r < $linhas_sub; $r++) {
						$id_representante_sub=$campos_sub[$r]['id_representante'];
						//Uma NF que encontrar aqui nesse SQL j� me satisfaz para atender as condi��es mais abaixo ...
						$sql = "SELECT nfs.id_nf, nfs.data_emissao, nfs.id_empresa, ovi.comissao_perc, 
								(SUM(ROUND(((nfsi.qtde*$campo_valor)),2)) - sum(round((nfsi.qtde_devolvida * $campo_valor), 2))) valor_nota 
								FROM `nfs_itens` nfsi 
								INNER JOIN `nfs` ON nfs.id_nf = nfsi.id_nf 
								INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
								INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
								INNER JOIN clientes c on c.id_cliente = nfs.id_cliente 
								WHERE nfs.data_emissao BETWEEN '$data_inicial' AND '$data_final' AND ovi.id_representante = '$id_representante_sub' 
								GROUP BY ovi.id_representante, nfs.id_empresa LIMIT 1 ";
						$campos = bancos::sql($sql);
						if(count($campos) == 1) {
							$r = $linhas_sub;//Aqui � para sair do Loop, n�o precisando vasculhar mais nada ...
							$pdf->AddPage();
						}
					}
				}
				Heade($data_inicial, $data_final, $data_holerith, $cmb_representante, $id_empresas);
				rotulo($moeda);
				$total_geral=0; //zero esta variavel para nao acumular o valor na segunda empresa quando for todas empresas
				$total_geral_desconto=0;
				$sub_tot_mercadoria=0;
				for($i = 0; $i < $linhas; $i++) {
					if($GLOBALS['nova_pagina'] == 'sim') {
						$GLOBALS['nova_pagina'] = 'nao';
						if($i != 0) {
							$pdf->Ln(-5);
						}
					}
					$pdf->SetFont('Arial', '', 10);
//Data de Emiss�o
					$pdf->Cell($GLOBALS['ph']*18, 5, data::datetodata($campos[$i]['data_emissao'], '/'), 1, 0, 'C');
//N� DA NF
					$numero_nf = faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');
					$pdf->Cell($GLOBALS['ph']*10, 5, $numero_nf, 1, 0, 'C');
//Cliente
					if($campos[$i]['status'] == 6) {//Se a NF for do Tipo Devolu��o
						$status = ' (DEVOLU��O)';
					}else {
						$status = '';
					}
					$pdf->Cell($GLOBALS['ph']*37, 5, $campos[$i]['cliente'].$status, 1, 0, 'L');
//Aqui verifica o Tipo de Nota
					if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
						$nota_sgd = 'N';//var surti efeito l� embaixo
					}else {
						$nota_sgd = 'S'; //var surti efeito l� embaixo
					}
//Valor da Mercadoria na Moeda da NF ...
					$tot_mercadoria = $campos[$i]['tot_mercadoria'];
					$sub_tot_mercadoria+=$campos[$i]['tot_mercadoria'];
					$pdf->Cell($GLOBALS['ph']*14, 5, number_format($tot_mercadoria, 2, ',', '.'), 1, 0, 'R');
//COMISS�O na Moeda da NF ...
					$valor_comissao = $campos[$i]['valor_comissao'];
					$pdf->Cell($GLOBALS['ph']*8, 5, number_format($valor_comissao, 2, ',', '.'), 1, 0, 'R');
//Comiss�o M�dia %
					if($tot_mercadoria==0) {
						$comissao_media = 0;	
					}else {
						$comissao_media = ($valor_comissao / $tot_mercadoria) * 100;
					}
					$pdf->Cell($GLOBALS['ph']*13, 5, number_format($comissao_media, 1, ',', '.'), 1, 1, 'R');
//Aqui eu atualizo o campo de Comiss�o da NF de Sa�da p/ pago ...
					$sql = "Update nfs set `status_comissao_pg` = 'S' where `id_nf` = '".$campos[$i]['id_nf']."' limit 1 ";
					bancos::sql($sql);

					$total_geral+=$valor_comissao;
				}
				$total_geral_premio+=$total_geral;
				$pdf->Cell(161.9, 5, 'Vendas '.$moeda.': '.number_format($sub_tot_mercadoria,2, ',', '.'), 1, 0, 'R');
				$pdf->Cell(43, 5, 'Sub-Total '.$moeda.': '.number_format($total_geral,2, ',', '.'), 1, 1, 'R');
                                //Estorno de Comiss�es ...
				$sql = "Select date_format(ce.data_lancamento, '%d/%m/%Y %h:%i:%s') data_lancamento, ce.num_nf_devolvida, ce.tipo_lancamento, ce.porc_devolucao, ce.valor_duplicata, 
						if(c.nomefantasia='', c.razaosocial, c.nomefantasia) cliente, nfs.id_nf, nfs.id_empresa 
						from comissoes_estornos ce 
						inner join nfs on nfs.id_nf = ce.id_nf 
						inner join clientes c on c.id_cliente = nfs.id_cliente 
						where substring(ce.data_lancamento,1,10) between '$data_inicial' and '$data_final' and ce.id_representante = '$cmb_representante' 
						and nfs.id_empresa = '$id_empresas' 
						order by cedata_lancamento ";
				$campos_devolucao=bancos::sql($sql);
				$linhas_devolucao = count($campos_devolucao);
				if($linhas_devolucao>0) {
					$pdf->Ln(5);
					$pdf->SetFont('Arial', 'B', 12);
					$pdf->Cell(205, 5, 'DEVOLU��ES', 1, 1, 'C');
					$pdf->SetFont('Arial', 'B', 8);
					$pdf->Cell(25, 5, 'DATA DE LAN�', 1, 0, 'C');
					$pdf->Cell(25, 5, 'TIPO DE LAN�', 1, 0, 'C');
					$pdf->Cell(20, 5, 'NF', 1, 0, 'C');
					$pdf->Cell(22, 5, 'NF BASEADA', 1, 0, 'C');
					$pdf->Cell(50, 5, 'CLIENTE', 1, 0, 'C');
					$pdf->Cell(25, 5, 'EMPRESA', 1, 0, 'C');
					$pdf->Cell(18, 5, 'VALOR', 1, 0, 'C');
					$pdf->Cell(20, 5, 'COMISS�O', 1, 1, 'C');
		
					for ($i = 0; $i < $linhas_devolucao; $i++) {
						$pdf->SetFont('Arial', '', 7);
						$pdf->Cell(25, 5, $campos_devolucao[$i]['data_lancamento'], 1, 0, 'C');
						$pdf->SetFont('Arial', '', 8);
						if($campos_devolucao[$i]['tipo_lancamento'] == 0) {
							$tipo_lancamento = 'DEVOLU��O DE CANCELAMENTO';
						}else if($campos_devolucao[$i]['tipo_lancamento'] == 1) {
							$tipo_lancamento = 'ATRASO DE PAGAMENTO';
						}else if($campos_devolucao[$i]['tipo_lancamento'] == 2) {
							$tipo_lancamento = 'ABATIMENTO / DIF. PRE�OS';
						}else if($campos_devolucao[$i]['tipo_lancamento'] == 3) {
							$tipo_lancamento = 'REEMBOLSO';
						}
						$pdf->Cell(25, 5, $tipo_lancamento, 1, 0, 'L');
						$pdf->Cell(20, 5, $campos_devolucao[$i]['num_nf_devolvida'], 1, 0, 'C');
						$pdf->Cell(22, 5, faturamentos::buscar_numero_nf($campos_devolucao[$i]['id_nf'], 'D'), 1, 0, 'C');
						$pdf->Cell(50, 5, $campos_devolucao[$i]['cliente'], 1, 0, 'L');
						if($campos_devolucao[$i]['id_empresa']==1) {
							$empresa = 'ALBAFER';
						}else if($campos_devolucao[$i]['id_empresa']==2) {
							$empresa = 'TOOL MASTER';
						}else if($campos_devolucao[$i]['id_empresa']==4) {
							$empresa = 'GRUPO';
						}else {
							$empresa = 'OUTROS';
						}
						$pdf->Cell(25, 5, $empresa, 1, 0, 'C');
						$pdf->Cell(18, 5, $moeda.number_format($campos_devolucao[$i]['valor_duplicata'],2, ',', '.'), 1, 0, 'R');
						$comissao = ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
				
						if($campos_devolucao[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
							$total_geral_desconto+=$comissao;
						}else {//DEVOLU��O, ATRASO DE PAGAMENTO, ABATIMENTO / DIF. PRE�OS
							$total_geral_desconto-=$comissao;
						}
						$pdf->Cell(20, 5, $moeda.number_format($comissao, 2, ',', '.'), 1, 1, 'R');
					}
				}
				if($total_geral_desconto != 0) {
					$pdf->Ln(5);
					$pdf->SetFont('Arial', 'B', 10);
					$pdf->Cell(205, 5, 'SUB-TOTAL '.$moeda.number_format($total_geral_desconto, 2, ',', '.'), 1, 1, 'R');
				}
//////////////////////////////// Parte de Supervis�o	 ////////////////////////////////
				if(($id_cargo_func==25 || $id_cargo_func==109) && $id_empresas==4) { //supervisor  e empresa=grupo, este relat�rio s� ir� aparecer em grupo 109=> super interno de vendas
					$pdf->Ln(5);
					$pdf->SetFont('Arial', 'B', 12);
					$pdf->Cell(205, 5, 'SUPERVIS�O', 1, 1, 'C');
					$pdf->SetFont('Arial', 'B', 8);
					$pdf->Cell(75, 5, 'REPRESENTANTE', 1, 0, 'C');
					$pdf->Cell(40, 5, 'EMPRESA', 1, 0, 'C');
					$pdf->Cell(40, 5, 'VENDAS '.$moeda, 1, 0, 'C');
					$pdf->Cell(50, 5, 'COMISS�O SUP. 1% EM '.$moeda, 1, 1, 'C');
					
					$desconto_dev_super=0;
					$sql = "Select rs.id_representante, 
							if(r.nome_fantasia='',r.nome_representante,r.nome_fantasia) representante 
							from representantes r 
							inner join representantes_vs_supervisores rs on rs.id_representante=r.id_representante 
							where rs.id_representante_supervisor='$cmb_representante' order by representante ";
					$campos_sub= bancos::sql($sql);
					$linhas_sub=count($campos_sub);
					for($r=0;$r<$linhas_sub;$r++) {
						$id_representante_sub=$campos_sub[$r]['id_representante'];
						$sql = "Select nfs.id_nf, nfs.data_emissao, nfs.id_empresa, ovi.comissao_perc, 
								(sum(round(((nfsi.qtde*$campo_valor)),2)) - sum(round((nfsi.qtde_devolvida*$campo_valor),2))) valor_nota 
								from  nfs_itens nfsi 
								inner join nfs on nfs.id_nf=nfsi.id_nf 
								inner join pedidos_vendas_itens pvi on pvi.id_pedido_venda_item=nfsi.id_pedido_venda_item 
								inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item=pvi.id_orcamento_venda_item 
								inner join clientes c on c.id_cliente = nfs.id_cliente 
								where nfs.data_emissao between '$data_inicial' and '$data_final' and ovi.id_representante='$id_representante_sub' 
								group by ovi.id_representante, nfs.id_empresa ";
						$campos= bancos::sql($sql);
						for ($i = 0; $i < count($campos); $i++) {
							$pdf->SetFont('Arial', '', 8);
							$pdf->Cell(75, 5, $campos_sub[$r]['representante'], 1, 0, 'L');
							if($campos[$i]['id_empresa'] == 1) {
								$empresa = 'ALBAFER';
							}else if($campos[$i]['id_empresa'] == 2) {
								$empresa = 'TOOL MASTER';
							}else if($campos[$i]['id_empresa'] == 4) {
								$empresa = 'GRUPO';
							}else {
								$empresa = 'OUTROS';
							}
							$pdf->Cell(40, 5, $empresa, 1, 0, 'C');
							$pdf->Cell(40, 5, number_format($campos[$i]['valor_nota'], 2, ',', '.'), 1, 0, 'R');
							$pdf->Cell(50, 5, number_format($campos[$i]['valor_nota'] * 0.01, 2, ',', '.'), 1, 1, 'R');
				
							$sub_total_supervisor+= $campos[$i]['valor_nota'];
						}
						//Estorno de Comiss�es ...
						$sql= "Select if(ce.tipo_lancamento=3, ce.valor_duplicata, ce.valor_duplicata*(-1)) valor_descontar, nfs.id_nf 
								from comissoes_estornos ce 
								inner join nfs on nfs.id_nf=ce.id_nf 
								inner join clientes c on c.id_cliente = nfs.id_cliente 
								where substring(ce.data_lancamento,1,10) between '$data_inicial' and '$data_final' and ce.id_representante='$id_representante_sub' 
								order by ce.data_lancamento ";
						$campos_dev_super= bancos::sql($sql);
						$linhas_dev_super = count($campos_dev_super);
						for($i=0;$i<$linhas_dev_super;$i++) {
							$desconto_dev_super+=$campos_dev_super[$i]['valor_descontar'];					
						}
					}
				}
				$sub_total_supervisor+=$desconto_dev_super;

				if($sub_total_supervisor>0) {
					$pdf->SetFont('Arial', '', 8);
					$pdf->Cell(115, 5, "TOTAL DE REEMBOLSOS, ATRASOS DE PGTO E ABATIMENTOS DE PRE�OS", 1, 0, 'L');
					//$pdf->Cell(40, 5, $empresa, 1, 0, 'C');
					$pdf->Cell(40, 5, number_format($desconto_dev_super, 2, ',', '.'), 1, 0, 'R');
					$pdf->Cell(50, 5, number_format($desconto_dev_super * 0.01, 2, ',', '.'), 1, 1, 'R');
	
					$pdf->Ln(5);
					$pdf->SetFont('Arial', 'B', 9);
					$pdf->Cell(155, 5, 'SUB-TOTAL '.$moeda.number_format($sub_total_supervisor,2, ',', '.'), 1, 0, 'R');
					$pdf->Cell(50, 5, 'SUB-TOTAL DE 1% '.$moeda.number_format(($sub_total_supervisor*0.01),2, ',', '.'), 1, 1, 'R');
				}

				$pdf->Ln(5);
				$pdf->SetFont('Arial', 'B', 10);
//Sub Total Sobre Vendas Diretas na Moeda da NF
				$pdf->Cell(155, 5, 'SUB TOTAL SOBRE VENDAS DIRETAS '.$moeda, 1, 0, 'R');
				$pdf->Cell(50, 5, number_format($total_geral, 2, ',', '.'), 1, 1, 'R');
				if($id_empresas==4) {//somente se for igual a 4, ou seja, Empresa Grupo 
					//Premia��o do vendedor
					$pdf->Cell(155, 5, "PR�MIO  ".$mes_ref_sg." (".(int)$comissao_meta_atingida_perc[$cmb_representante]."% SOBRE ".number_format($total_geral_premio+$total_geral_desconto, 2, ',', '.').") ".$moeda, 1, 0, 'R');
					$total_premio_rs =(($total_geral_premio+$total_geral_desconto)*$comissao_meta_atingida_perc[$cmb_representante]/100);
					$pdf->Cell(50, 5, number_format($total_premio_rs, 2, ',', '.'), 1, 1, 'R');
					//Premia��o do vendedor Supervisor
					$pdf->Cell(155, 5, "PR�MIO SUP.  ".$mes_ref_sg." (".(int)$comissao_meta_atingida_sup_perc[$cmb_representante]."% SOBRE ".number_format(($sub_total_supervisor*0.01), 2, ',', '.').") ".$moeda, 1, 0, 'R');
					$total_premio_sup_rs =(($sub_total_supervisor*0.01)*$comissao_meta_atingida_sup_perc[$cmb_representante]/100);
					$pdf->Cell(50, 5, number_format($total_premio_sup_rs,2, ',', '.'), 1, 1, 'R');
				} 

//Sub-Total de Supervis�o 1% na Moeda da NF
				$pdf->Cell(155, 5, 'SUB TOTAL DE SUPERVIS�O 1% '.$moeda, 1, 0, 'R');
				$pdf->Cell(50, 5, number_format(($sub_total_supervisor*0.01), 2, ',', '.'), 1, 1, 'R');
//Imposto de Renda
				$pdf->Cell(155, 5, 'IMPOSTO DE RENDA '.$moeda, 1, 0, 'R');
				if(empty($id_cargo_func) && ($id_empresas != 4)) {
					$sql = "Select tipo_pessoa, id_pais, descontar_ir from representantes where id_representante = '$cmb_representante' ";
					$campos_rep =  bancos::sql($sql,0,1);
					if(strtoupper($campos_rep[0]['descontar_ir']=='S')) {// se for juridico fazer o calculo de I.R.
						if($campos_rep[0]['id_pais']==31) {// se for juridico fazer o calculo de I.R.
							if($campos_rep[0]['tipo_pessoa'] == 'J') {
								$ir=-(round(($total_geral*0.015),2));
								if(abs($ir)>10.00) {
									$pdf->Cell(50, 5, number_format($ir, 2, ',', '.'), 1, 1, 'R');
								}else {
									$ir=0;//ignora o desconto pois ele � muito simples
									$pdf->Cell(50, 5, 'Valor Baixo 0,00', 1, 1, 'R');
								}
							}else {
								$ir=0;//ignora o desconto pois ele � muito simples
								$pdf->Cell(50, 5, 'PF 0,00', 1, 1, 'R');//Internacional 0,00
							}
						}else {
							$ir=0;//ignora o desconto pois ele � muito simples
							$pdf->Cell(50, 5, 'Intern. 0,00', 1, 1, 'R');//Marca��o Cadastro 
						}
					}else {
						$ir=0;//ignora o desconto pois ele � muito simples
						$pdf->Cell(50, 5, 'Marca��o CAD. 0,00', 1, 1, 'R');
					}
				}else {
					$ir=0;//ignora o desconto pois ele � muito simples
					$pdf->Cell(50, 5, '0,00', 1, 1, 'R');
				}
//Sub-Total das Devolu��es / Reembolsos na Moeda da NF ...
				$pdf->Cell(155, 5, 'SUB TOTAL DAS DEVOLU��ES / REEMBOLSOS '.$moeda, 1, 0, 'R');
				$pdf->Cell(50, 5, number_format($total_geral_desconto,2, ',', '.'), 1, 1, 'R');
//DSR na Moeda da NF ...
				$pdf->Cell(155, 5, 'DSR '.$moeda, 1, 0, 'R');
				$total_global = $total_geral+$total_premio_rs+$total_premio_sup_rs+($sub_total_supervisor*0.01)+$ir+$total_geral_desconto;
				/*Por enquanto n�o apagar at� porque n�o se tem certeza ...
				if(($id_empresa_func!=$id_empresas || $id_empresas==4) && !empty($id_cargo_func) && $cmb_representante!=14) {
					$aditivo = $total_global*0.20;
					$total_global+=$aditivo;
				}else {
					$aditivo = 0;
				}*/
/*Se a Qtde de Dias �teis ou Qtde de Dias In�teis = 0 ou o Representante for a Mercedes, ent�o n�o existe 
c�lculo p/ o DSR ...*/
				if($qtde_dias_uteis_mes==0 || $qtde_dias_inuteis_mes==0 || empty($id_cargo_func) || $cmb_representante==14) {
					$dsr = 0;
				}else {
					$dsr = $total_global / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
					if($dsr < 0) {
						$dsr = 0;
					}
				}
				$total_global+=$dsr;
				$pdf->Cell(50, 5, number_format($dsr, 2, ',', '.'), 1, 1, 'R');
//Total Geral na Moeda da NF ...
				$pdf->Cell(155, 5, 'TOTAL GERAL '.$moeda, 1, 0, 'R');
				$total_geral_global+=$total_global; //guardo o total geral de comissao que o representante ganhou
				$pdf->Cell(50, 5, number_format($total_global, 2, ',', '.'), 1, 1, 'R');
			
/*S� no caso do Noronha em que a Comiss�o � um pouco diferente, sendo assim eu j� deixo pr�-carregado 
os valores por que pode servir em qualquer um dos ifs e elses abaixo ...*/
				if($cmb_representante==15) {//Noronha ...
					$pdf->Ln(3);
					comissao_noronha($data_inicial, $data_final);
//Aqui eu trago o Valor do Faturamento de acordo com a Empresa 
					if($id_empresas == 1) {//
						//Faturamento da Empresa ...
						$pdf->Cell(155, 5, 'FATURAMENTO '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($GLOBALS['fat_alba'], 2, ',', '.'), 1, 1, 'R');
	
						//COMISS�O x 0,25% na Moeda da NF ...
						$comissao_noronha_alba = ($GLOBALS['fat_alba'] * 0.25) / 100;
						$pdf->Cell(155, 5, 'COMISS�O x 0,25% EM '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($comissao_noronha_alba, 2, ',', '.'), 1, 1, 'R');
	
						//DSR da Empresa ...
						$dsr_noronha_alba = $comissao_noronha_alba / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
						$pdf->Cell(155, 5, 'DSR '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($dsr_noronha_alba, 2, ',', '.'), 1, 1, 'R');
	
						//Aqui nessa vari�vel, eu j� incorporei a Comiss�o ...
						$total_global+= $comissao_noronha_alba;
	
						//Comiss�o Geral, � o "Total Faturado + o DSR"
						$pdf->Cell(155, 5, 'COMISS�O GERAL '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($total_global + $dsr_noronha_alba, 2, ',', '.'), 1, 1, 'R');
	
						/*Aqui eu sobrepondo os valores nessas vari�veis p/ n�o dar erro 
						no Script + abaixo ...*/
						$total_global = $total_global + $dsr_noronha_alba;
						$dsr = $dsr_noronha_alba;
					}else if($id_empresas == 2) {
						//Faturamento da Empresa ...
						$pdf->Cell(155, 5, 'FATURAMENTO '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($GLOBALS['fat_tool'], 2, ',', '.'), 1, 1, 'R');
						
						//COMISS�O x 0,25% na Moeda da NF ...
						$comissao_noronha_tool = ($GLOBALS['fat_tool'] * 0.25) / 100;
						$pdf->Cell(155, 5, 'COMISS�O x 0,25% EM '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($comissao_noronha_tool, 2, ',', '.'), 1, 1, 'R');
	
						//DSR da Empresa ...
						$dsr_noronha_tool = $comissao_noronha_tool / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
						$pdf->Cell(155, 5, 'DSR '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($dsr_noronha_tool, 2, ',', '.'), 1, 1, 'R');
	
						//Aqui nessa vari�vel, eu j� incorporei a Comiss�o ...
						$total_global+= $comissao_noronha_tool;
	
						//Comiss�o Geral, � o "Total Faturado + o DSR"
						$pdf->Cell(155, 5, 'COMISS�O GERAL '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($total_global + $dsr_noronha_tool, 2, ',', '.'), 1, 1, 'R');
						
						/*Aqui eu sobrepondo os valores nessas vari�veis p/ n�o dar erro 
						no Script + abaixo ...*/
						$total_global = $total_global + $dsr_noronha_tool;
						$dsr = $dsr_noronha_tool;
					}else if($id_empresas == 4) {
						//Faturamento da Empresa ...
						$pdf->Cell(155, 5, 'FATURAMENTO '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($GLOBALS['fat_grupo'], 2, ',', '.'), 1, 1, 'R');
	
						//COMISS�O x 0,25% EM na Moeda da NF ...
						$comissao_noronha_grupo = ($GLOBALS['fat_grupo'] * 0.25) / 100;
						$pdf->Cell(155, 5, 'COMISS�O x 0,25% EM '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($comissao_noronha_grupo, 2, ',', '.'), 1, 1, 'R');
	
						//DSR da Empresa ...
						$dsr_noronha_grupo = $comissao_noronha_grupo / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
						$pdf->Cell(155, 5, 'DSR '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($dsr_noronha_grupo, 2, ',', '.'), 1, 1, 'R');
	
						//Aqui nessa vari�vel, eu j� incorporei a Comiss�o ...
						$total_global+= $comissao_noronha_grupo;
	
						//Comiss�o Geral, � o "Total Faturado + o DSR"
						$pdf->Cell(155, 5, 'COMISS�O GERAL '.genericas::nome_empresa($id_empresas).' '.$moeda, 1, 0, 'R');
						$pdf->Cell(50, 5, number_format($total_global + $dsr_noronha_grupo, 2, ',', '.'), 1, 1, 'R');
						
						/*Aqui eu sobrepondo os valores nessas vari�veis p/ n�o dar erro 
						no Script + abaixo ...*/
						$total_global = $total_global + $dsr_noronha_grupo;
						$dsr = $dsr_noronha_grupo;
					}
//Se a Qtde de Dias �teis ou Qtde de Dias In�teis = 0, ent�o n�o existe c�lculo p/ o DSR do Noronha ...
					if($qtde_dias_uteis_mes == 0 || $qtde_dias_inuteis_mes == 0 || empty($id_cargo_func)) {
						$dsr_noronha_emp_cur = 0;
					}else {
						$dsr_noronha_emp_cur = $com_noronha_emp_cur / $qtde_dias_uteis_mes * $qtde_dias_inuteis_mes;
					}
				}
				$total_global = number_format($total_global, 2, '.', '');//Macete (rs)
/******************************************************************************************************/
/**********************Script p/ Grava��o das Comiss�es e Pr�mios do Representante*********************/
/******************************************************************************************************/
				$data_sys_comissao = date('Y-m-d H:i:s');
//1)Busca do Funcion�rio atrav�s do id_representante ...
				$sql = "Select id_funcionario as id_funcionario_rep 
						from `representantes_vs_funcionarios` 
						where `id_representante` = '$cmb_representante' limit 1 ";
				$campos_rep =   bancos::sql($sql);
				if(count($campos_rep) == 1) {//Significa que este Representante � um Funcion�rio ...
/*Aqui eu renomeio essa vari�vel de $id_funcionario p/ $id_funcionario_rep porque j� existe uma vari�vel
com esse nome na sess�o do Sistema, ent�o assim iria dar conflito*/
				$id_funcionario_rep = $campos_rep[0]['id_funcionario_rep'];
//Verifico se retornou algum valor no SQL de busca da pr�xima Data do Holerith, feito l� no in�cio ...
					if(count($campos_data) == 1) {
//Aqui eu guardo na Tabela de Funcion�rios vs Holeriths (Cr�ditos) ...
//Primeiro eu verifico se j� existe esse id_funcionario na Tabela ...
						$sql = "Select id_funcionario_vs_holerith 
								from funcionarios_vs_holeriths 
								where `id_funcionario` = '$id_funcionario_rep' 
								and `id_vale_data` = '$id_vale_data' ";
						$campos2 = bancos::sql($sql);
						if(count($campos2) == 0) {//Ainda n�o existe, ent�o eu gravo na Base de Dados ...
							if($id_empresas == 1) {//Se a Empresa for Albafer ...
								$sql = "Insert funcionarios_vs_holeriths (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `comissao_alba`, `dsr_alba`, `data_sys_comissao`) values (null, '$id_funcionario_rep', '$id_vale_data', '$total_global', '$dsr', '$data_sys_comissao') ";
							}else if($id_empresas == 2) {//Se a Empresa for Tool Master ...
								$sql = "Insert funcionarios_vs_holeriths (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `comissao_tool`, `dsr_tool`, `data_sys_comissao`) values (null, '$id_funcionario_rep', '$id_vale_data', '$total_global', '$dsr', '$data_sys_comissao') ";
							}else if($id_empresas == 4) {//Se a Empresa for Grupo ...
								$sql = "Insert funcionarios_vs_holeriths (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `comissao_grupo`, `dsr_grupo`, `data_sys_comissao`) values (null, '$id_funcionario_rep', '$id_vale_data', '$total_global', '$dsr', '$data_sys_comissao') ";
							}
							bancos::sql($sql);
						}else {//J� existe, sendo assim eu s� altero na Base de Dados ...
							$id_funcionario_vs_holerith = $campos2[0]['id_funcionario_vs_holerith'];
							if($id_empresas == 1) {//Se a Empresa for Albafer ...
								 $sql = "Update funcionarios_vs_holeriths set `comissao_alba` = '$total_global', `dsr_alba` = '$dsr', `data_sys_comissao` = '$data_sys_comissao' where `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' limit 1 ";
							}else if($id_empresas == 2) {//Se a Empresa for Tool Master ...
								$sql = "Update funcionarios_vs_holeriths set `comissao_tool` = '$total_global', `dsr_tool` = '$dsr', `data_sys_comissao` = '$data_sys_comissao' where `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' limit 1 ";
							}else if($id_empresas == 4) {//Se a Empresa for Grupo ...
								$sql = "Update funcionarios_vs_holeriths set `comissao_grupo` = '$total_global', `dsr_grupo` = '$dsr', `data_sys_comissao` = '$data_sys_comissao' where `id_funcionario_vs_holerith` = '$id_funcionario_vs_holerith' limit 1 ";
							}
							bancos::sql($sql);
						}
					}
				}else {//Significa que este representante n�o � Funcion�rio ...
//Verifico se retornou algum valor no SQL de busca da pr�xima Data do Holerith, feito l� no in�cio ...
                                        if(count($campos_data) == 1) {
/*Primeiro eu verifico se j� existe esse "id_representante" na respectiva "Data de Holerith" da data que foi 
filtrada pelo usu�rio nesse relat�rio de Comiss�es nessa respectiva tabela de "representantes_vs_comissoes" ...*/
                                                $sql = "SELECT `id_representante_vs_comissao` 
                                                        FROM `representantes_vs_comissoes` 
                                                        WHERE `id_representante` = '$cmb_representante' 
                                                        AND `id_vale_data` = '$id_vale_data' ";
                                                $campos_representante_comissao = bancos::sql($sql);
                                                if(count($campos_representante_comissao) == 0) {
                                                        $sql = "INSERT INTO `representantes_vs_comissoes` (`id_representante_vs_comissao`, `id_representante`, `id_vale_data`) VALUES (NULL, '$cmb_representante', '$id_vale_data') ";
                                                        $id_representante_vs_comissao = bancos::id_registro();
                                                }else {
                                                        $id_representante_vs_comissao = $campos_representante_comissao[0]['id_representante_vs_comissao'];
                                                }

                                                if($id_empresa_loop == 1) {//Se a Empresa for Albafer ...
                                                        $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_alba` = '$total_global', `data_sys_comissao` = '$data_sys_comissao' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                                                }else if($id_empresa_loop == 2) {//Se a Empresa for Tool Master ...
                                                        $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_tool` = '$total_global', `data_sys_comissao` = '$data_sys_comissao' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                                                }else if($id_empresa_loop == 4) {//Se a Empresa for Grupo ...
                                                        $sql = "UPDATE `representantes_vs_comissoes` SET `comissao_grupo` = '$total_global', `data_sys_comissao` = '$data_sys_comissao' WHERE `id_representante_vs_comissao` = '$id_representante_vs_comissao' LIMIT 1 ";
                                                }
                                                bancos::sql($sql);
                                        }
				}
			}
		}
	}
}
chdir('../../../../../pdf');
$file='../../../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>