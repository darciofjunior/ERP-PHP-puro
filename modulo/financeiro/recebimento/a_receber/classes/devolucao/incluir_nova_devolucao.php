<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
require('../../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ABATIMENTO / DIF. PREÇOS INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
//Só exibe as NF(s) de Saída do Tipo Despachadas e Canceladas p/ fazer a Devolução ...
//Da Empresa do Menu que foi acessado pelo Financeiro ...
	if(empty($txt_consultar))	$txt_consultar = 0;
	switch($opt_opcao) {
		case 1:
			$sql = "Select nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.razaosocial, c.credito, t.nome as transportadora 
					from nfs 
					inner join nfs_num_notas nnn on nnn.id_nf_num_nota = nfs.id_nf_num_nota and nnn.numero_nf like '$txt_consultar%' 
					inner join transportadoras t on t.id_transportadora = nfs.id_transportadora 
					inner join clientes c on c.id_cliente = nfs.id_cliente 
					where nfs.status in (4, 5) 
					and nfs.id_empresa = '$id_emp2' order by nfs.id_nf desc ";
		break;
		case 2:
			$sql = "Select nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.razaosocial, c.credito, t.nome as transportadora 
					from nfs 
					inner join transportadoras t on t.id_transportadora = nfs.id_transportadora 
					inner join clientes c on c.id_cliente = nfs.id_cliente and c.razaosocial like '%$txt_consultar%' 
					where nfs.status in (4, 5) 
					and nfs.id_empresa = '$id_emp2' order by nfs.id_nf desc ";
		break;
		default:
			$sql = "Select nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.razaosocial, c.credito, t.nome as transportadora 
					from nfs 
					inner join transportadoras t on t.id_transportadora = nfs.id_transportadora 
					inner join clientes c on c.id_cliente = nfs.id_cliente 
					where nfs.status in (4, 5) 
					and nfs.id_empresa = '$id_emp2' order by nfs.id_nf desc ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'Javascript'>
			window.location = 'incluir_nova_devolucao.php?&id_emp2=<?=$id_emp2?>&valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Incluir Abatimento / Dif. Preços ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_nf, prosseguir) {
	if(prosseguir == 0) {//Ainda não foi feita nenhuma Devolução, sendo assim posso inserir uma Devolução ...
		window.location = 'incluir_nova_devolucao.php?passo=2&id_emp2=<?=$id_emp2;?>&id_nf='+id_nf
	}else {//Já foi feita uma Devolução e sendo assim, pergunto se deseja fazer outra ...
		var resposta = confirm('ESSA NOTA JÁ FOI DEVOLVIDA !!!\nDESEJA DEVOLVER OUTRA NOTA COM BASE NESTA ???')
		if(resposta == true) {
			window.location = 'incluir_nova_devolucao.php?id_emp2=<?=$id_emp2;?>&id_nf='+id_nf
		}else {
			return false
		}
	}
}
</Script>
</head>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Incluir 
            <font color="yellow">
                    (NNF)
            </font>
            Abatimento / Dif. Preços para <?=genericas::nome_empresa($id_emp2);?> - Consultar Nota(s) Fiscal(is)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            N.&ordm; da NNF
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
		$vetor = array_sistema::nota_fiscal();
		$tipo_despacho = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX');
		for ($i = 0;  $i < $linhas; $i++) {
			$id_nf = $campos[$i]['id_nf'];
//Aki eu verifico se já foi feito um estorno de Comissão da Nota Fiscal Corrente ...
			$sql = "SELECT id_comissao_estorno 
                                FROM `comissoes_estornos` 
                                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
			$campos_devolucao = bancos::sql($sql);
			if(count($campos_devolucao) == 0) {//Ainda não foi feita, sendo assim posso exibir o link ...
                            $onclick = "javascript:prosseguir(".$id_nf.", 0)";
                            $tag_link = "<a href = '#' class= 'link'>";
			}else {//Já foi feita Nota de Devolução, não posso exibir o link ...
                            $onclick = "javascript:prosseguir(".$id_nf.", 1)";
                            $tag_link = "";
			}
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td onclick="<?=$onclick;?>" width='10' class='link'>
			<?=$tag_link;?>
                            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
			</a>
		</td>
		<td onclick="<?=$onclick;?>" class="link">
			<?=$tag_link;?>
                            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
			</a>
		</td>
		<td>
		<?
			if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
		?>
		</td>
		<td align="left">
                <?
                    echo $campos[$i]['razaosocial'];
                    //Aqui verifica se a NF contém pelo menos 1 item
                    $sql = "SELECT id_nfs_item 
                            FROM `nfs_itens` 
                            WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                    $campos_nfs_item = bancos::sql($sql);
                    if(count($campos_nfs_item) == 0) echo ' <font color="red">(S/ ITENS)</font>';
                ?>
		</td>
		<td>
                    <?=$campos[$i]['transportadora'];?>
		</td>
		<td align="left">
		<?
                    echo $vetor[$campos[$i]['status']];
                    if($campos[$i]['status'] == 4) echo ' ('.$tipo_despacho[$campos[$i]['tipo_despacho']].')';
		?>
		</td>
		<td align="left">
		<?
//Busca da Empresa da NF ...
			$sql = "SELECT nomefantasia 
                                FROM `empresas` 
                                WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
			$campos_empresa = bancos::sql($sql);
			$apresentar = $campos_empresa[0]['nomefantasia'];
			$apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
			if($campos[$i]['vencimento4'] > 0) {
				$prazo_faturamento = '/'.$campos[$i]['vencimento4'];
			}
			if($campos[$i]['vencimento3'] > 0) {
				$prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
			}
			if($campos[$i]['vencimento2'] > 0) {
				$prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
			}else {
				if($campos[$i]['vencimento1'] == 0) {
					$prazo_faturamento = 'À vista';
				}else {
					$prazo_faturamento = $campos[$i]['vencimento1'];
				}
			}
			echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
			$prazo_faturamento = '';
		?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
            <td colspan='7'>
                <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir_nova_devolucao.php?id_emp2=<?=$id_emp2;?>'" class="botao">
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
//Vou utilizar essas datas p/ fazer algumas comparações com a Data de Emissão ...
	$datas          = genericas::retornar_data_relatorio(1);
	$data_inicial 	= data::datatodate($datas['data_inicial'], '-');
	$data_final 	= data::datatodate($datas['data_final'], '-');

//Busca da Data de Emissão e do Número da Nota Fiscal Devolvida ...
	$sql = "SELECT data_emissao 
                FROM `nfs` 
                WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
	$campos_nf      = bancos::sql($sql);
	$data_emissao 	= $campos_nf[0]['data_emissao'];
	$data_icms      = date('Y-m-').'01';//Sempre é o dia 1 do Mês corrente ...
?>
<html>
<head>
<title>.:: Abatimento / Dif. Preços ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var id_emp2 		= eval('<?=$id_emp2;?>')
	var data_emissao 	= '<?=$data_emissao;?>'
	var data_final 		= '<?=$data_final;?>'
	var data_icms 		= '<?=$data_icms;?>'

	data_emissao = data_emissao.substr(0,4)+data_emissao.substr(5,2)+data_emissao.substr(8,2)
	data_final = data_final.substr(0,4)+data_final.substr(5,2)+data_final.substr(8,2)
	data_icms = data_icms.substr(0,4)+data_icms.substr(5,2)+data_icms.substr(8,2)

	data_emissao = eval(data_emissao)
	data_final = eval(data_final)
	data_icms = eval(data_icms)

//Representante
	if(!combo('form', 'cmb_representante', '', 'SELECIONE UM REPRESENTANTE !')) {
		return false
	}
//Tipo de Lançamento
	if(!combo('form', 'cmb_tipo_lancamento', '', 'SELECIONE UM TIPO DE LANÇAMENTO !')) {
		return false
	}
//Então forço o Preenchimento do Tipo de Devolução ...
	/*if(document.form.cmb_tipo_devolucao.value == 0) {//Se for Devolução ...
//Tipo de Devolução
		if(!combo('form', 'cmb_tipo_devolucao', '', 'SELECIONE O TIPO DE DEVOLUÇÃO !')) {
			return false
		}
	}*/
//Se a Empresa for Albafer ou Tool Master, eu forço o preenhcimento do campo de N.º de SNF à Devolver ...
	if(id_emp2 == 1 || id_emp2 == 2) {
//N.º da SNF à Devolver
		if(!texto('form', 'txt_num_nf_devolver', '1', '1234567890', 'N.º DA SNF À DEVOLVER', '2')) {
			return false
		}
	}
//Valor S/ IPI
	if(!texto('form', 'txt_valor_sem_ipi', '1', '1234567890,.', 'VALOR SEM IPI', '2')) {
		return false
	}
/*Aqui eu faço essa verificação p/ ver se realmente vai ser necessário estar preenchendo o Valor de 
Porcentagem da Comissão - Será necessário o preenchimento sempre que a Data de Emissão for menor que 
a Data Final de Comissão que geralmente é sempre é o dia vai até o dia 25 de cada mês, ou seja o último
dia p/ fechamento da Folha*/
	if(data_emissao <= data_final) {
//Se o Tipo de Devolução for Parcial, então eu forço o preenchimento do campo de Porcentagem da Comissão ...
		//if(document.form.cmb_tipo_devolucao.value == 0) {
//Porcentagem da Comissão
			if(!texto('form', 'txt_porc_comissao', '1', '1234567890,.', 'VALOR DE PORCENTAGEM DA COMISSÃO', '2')) {
				return false
			}
//Se a Porcentagem da Comissão = 0, então tem que obrigar a colocar outro valor ...
			if(document.form.txt_porc_comissao.value == '0,00') {
				alert('PORCENTAGEM DA COMISSÃO INVÁLIDA !')
				document.form.txt_porc_comissao.focus()
				document.form.txt_porc_comissao.select()
				return false
			}
		//}
	}
/*Aqui eu destravo esse campo para poder no BD - isso vai servir para o caso de Grupo em que esse campo
vem travado*/
	document.form.txt_num_nf_devolver.disabled = false
//Prepara no formato em que eu posso ler no banco ...
	return limpeza_moeda('form', 'txt_valor_sem_ipi, txt_porc_comissao, ')
}

function calcular() {
//Valor Sem IPI
	if(document.form.txt_valor_sem_ipi.value == '') {
		var valor_sem_ipi = 0
	}else {
		var valor_sem_ipi = eval(strtofloat(document.form.txt_valor_sem_ipi.value))
	}
//Porcentagem Comissão ...
	if(document.form.txt_porc_comissao.value == '') {
		var porc_comissao = 0
	}else {
		var porc_comissao = eval(strtofloat(document.form.txt_porc_comissao.value))
	}
//Valor de Devolução de Comissão ...
	document.form.txt_valor_devolucao_comissao.value = (valor_sem_ipi * porc_comissao) / 100
	document.form.txt_valor_devolucao_comissao.value = arred(document.form.txt_valor_devolucao_comissao.value, 2, 1)
}

function iniciar() {
	var id_emp2 = eval('<?=$id_emp2;?>')
//Se a Empresa for Albafer ou Tool Master, o campo de N.º de NF à Devolver vem destravado ...
	if(id_emp2 == 1 || id_emp2 == 2) {
		document.form.txt_num_nf_devolver.focus()
//Quando a Empresa for Grupo, o campo de N.º de NF à Devolver vem travado ...
	}else {
		document.form.txt_valor_sem_ipi.focus()
	}
}

function habilitar_comissao() {
	if(document.form.cmb_tipo_lancamento.value == '') {//Deixa desabilitado ...
		document.form.txt_porc_comissao.style.color = 'gray'
		document.form.txt_porc_comissao.style.background = '#FFFFE1'
		document.form.txt_porc_comissao.disabled = true
		document.form.txt_porc_comissao.value = ''
	}else {//Habilita sempre que tiver algum Tipo de Lançamento selecionado ...
		document.form.txt_porc_comissao.style.color = 'Brown'
		document.form.txt_porc_comissao.style.background = '#FFFFFF'
		document.form.txt_porc_comissao.disabled = false
		document.form.txt_porc_comissao.focus()
	}
}

/*function habilitar_tipo_devolucao() {
	if(eval(document.form.cmb_tipo_lancamento.value) == 0) {//Se for Devolução habilita ...
		document.form.cmb_tipo_devolucao.style.color = 'Brown'
		document.form.cmb_tipo_devolucao.style.background = '#FFFFFF'
		document.form.cmb_tipo_devolucao.disabled = false
		document.form.cmb_tipo_devolucao.focus()
	}else {//Se for outro Tipo desabilita ...
		document.form.cmb_tipo_devolucao.style.color = 'gray'
		document.form.cmb_tipo_devolucao.style.background = '#FFFFE1'
		document.form.cmb_tipo_devolucao.disabled = true
		document.form.cmb_tipo_devolucao.value = ''
	}
}

function habilitar_comissao() {
	if(eval(document.form.cmb_tipo_devolucao.value) == 0) {//Se for Parcial habilita ...
		document.form.txt_porc_comissao.style.color = 'Brown'
		document.form.txt_porc_comissao.style.background = '#FFFFFF'
		document.form.txt_porc_comissao.disabled = false
		document.form.txt_porc_comissao.focus()
	}else {//Se for outro Tipo, desabilita ...
		document.form.txt_porc_comissao.style.color = 'gray'
		document.form.txt_porc_comissao.style.background = '#FFFFE1'
		document.form.txt_porc_comissao.disabled = true
		document.form.txt_porc_comissao.value = ''
	}
}*/
</Script>
</head>
<body onload="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar()">
<input type='hidden' name="id_emp2" value="<?=$id_emp2;?>">
<input type='hidden' name="id_nf" value="<?=$_GET[id_nf];?>">
<table width='95%' border="0" cellspacing="1" cellpadding="1" align="center">
	<tr align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                Incluir Abatimento / Dif. Preços para <?=genericas::nome_empresa($id_emp2);?>
            </td>
	</tr>
	<tr class="linhanormal">
            <td width='30%'>
                <b>NNF N.º:</b>
            </td>
            <td width='70%'>
                <a href="javascript:nova_janela('../../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$id_nf;?>&nao_verificar_sessao=1', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c')" title="Visualizar Detalhes" class="link">
                    <?=faturamentos::buscar_numero_nf($id_nf, 'S');?>
                </a>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <b>Data da Última Comissão Paga:</b>
            </td>
            <td>
                <?=data::datetodata($data_final, '/');?>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <b>Data de Emissão da NNF Devolvida:</b>
            </td>
            <td>
                <?=data::datetodata($data_emissao, '/');?>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <b>Cliente:</b>
            </td>
            <td>
            <?
//Busca do Nome do Cliente da Nota que está sendo Devolvida ...
                    $sql = "SELECT c.id_cliente, c.razaosocial 
                            FROM `nfs` 
                            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                            WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
                    $campos_cliente 	= bancos::sql($sql);
                    $id_cliente 		= $campos_cliente[0]['id_cliente'];//Vou utilizar essa variável + abaixo ...
                    echo $campos_cliente[0]['razaosocial'];
            ?>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>Representante:</b>
                </font>
            </td>
            <td>
                <select name="cmb_representante" title="Selecione o Representante" class="combo">
                <?
//Aqui eu busco todos representantes diretamente da Nota Fiscal ...
                        $sql = "SELECT DISTINCT(r.id_representante), CONCAT(r.nome_fantasia, ' / ', r.zona_atuacao) AS dados 
                                FROM `representantes` r 
                                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = '$_GET[id_nf]' 
                                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item AND pvi.id_representante = r.id_representante 
                                WHERE r.ativo = 1 ORDER BY r.nome_fantasia ";
                        $campos_representante = bancos::sql($sql);
                        if(count($campos_representante) == 0) {//Caso não encontre, então busco todos os representantes do Cliente ...
                                $sql = "SELECT distinct(r.id_representante), CONCAT(r.nome_fantasia, ' / ', r.zona_atuacao) AS dados  
                                        FROM `representantes` r 
                                        INNER JOIN `clientes_vs_representantes` cr ON cr.id_cliente = '$id_cliente' 
                                        AND cr.`id_representante` = r.`id_representante` 
                                        WHERE r.`ativo` = '1' ORDER BY r.nome_fantasia ";
                        }
                        echo combos::combo($sql);
                ?>
                </select>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>Tipo de Lançamento:</b>
                </font>
            </td>
            <td>
                <select name="cmb_tipo_lancamento" title="Selecione o Tipo de Lançamento" onchange="habilitar_comissao()" class="combo">
                        <option value="" style="color:red">SELECIONE</option>
                        <option value="0">DEVOLUÇÃO DE CANCELAMENTO</option>
                        <!--<option value="1">ATRASO DE PAGAMENTO</option>-->
                        <option value="2">ABATIMENTO / DIF. PREÇOS</option>
                        <!--<option value="3">REEMBOLSO</option>-->
                        <!--<option value="4">NF DE ENTRADA</option>-->
                </select>
            </td>
	</tr>
	<!--<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>Tipo de Devolução:</b>
                </font>
            </td>
            <td>
                <select name="cmb_tipo_devolucao" title="Selecione o Tipo de Devolução" onchange="habilitar_comissao()" class="textdisabled" disabled>
                    <option value="" style="color:red">SELECIONE</option>
                    <option value="0">PARCIAL</option>
                    <option value="1">TOTAL</option>
                </select>
            </td>
	</tr>-->
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>N.º da SNF à Devolver:</b>
                </font>
            </td>
            <?
//Se a Empresa for Albafer ou Tool Master, o campo de N.º de SNF à Devolver vem destravado ...
                if($id_emp2 == 1 || $id_emp2 == 2) {
                    $class = 'caixadetexto';
                    $disabled = '';
                    $num_nf_devolver = '';
//Quando a Empresa for Grupo, o campo de N.º de NF à Devolver vem travado ...
                }else {
                    $class = 'disabled';
                    $disabled = 'disabled';
                    $num_nf_devolver = '';
                }
            ?>
            <td>
                <input type="text" name="txt_num_nf_devolver" value="<?=$num_nf_devolver;?>" title="Digite o N.º de NF à Baseada" size="12" maxlength="10" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" class="<?=$class;?>" <?=$disabled;?>>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>Valor S/ IPI:</b>
                </font>
            </td>
            <td>
                <input type="text" name="txt_valor_sem_ipi" title="Digite o Valor Sem IPI" size="12" maxlength="10" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular()" class="caixadetexto">
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    % Comissão: <font color="green">*</font>
                </font>
            </td>
            <td>
                <input type="text" name="txt_porc_comissao" title="Digite a % Comissão" size="12" maxlength="10" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular()" class="textdisabled" disabled>
            </td>
	</tr>
	<tr class="linhanormal">
            <td>
                <font color="darkblue">
                    <b>Valor do Abatimento / NF Entrada:</b>
                </font>
            </td>
            <td>
                <input type="text" name="txt_valor_devolucao_comissao" title="Valor de Devolução da Comissão" size="12" maxlength="10" class="textdisabled" disabled>
            </td>
	</tr>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                    <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir_nova_devolucao.php<?=$parametro;?>'" class="botao">
                    <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');iniciar()" style="color:#ff9900;" class="botao">
                    <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
                    <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="return fechar(window)" class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
	$data_sys = date('Y-m-d H:i:s');
//Inserção da Nota de Devolução ...
	$sql = "INSERT INTO `comissoes_estornos` (`id_comissao_estorno`, `id_nf`, `id_representante`, `num_nf_devolvida`, `data_lancamento`, `tipo_lancamento`, `porc_devolucao`, `valor_duplicata`) VALUES (NULL, '$id_nf', '$cmb_representante', '$txt_num_nf_devolver', '$data_sys', '$cmb_tipo_lancamento', '$txt_porc_comissao', '$txt_valor_sem_ipi') ";
	bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_nova_devolucao.php?id_emp2=<?=$id_emp2;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Abatimento / Dif. Preços ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		for(i = 0; i < 2; i++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		for(i = 0; i < 2; i++) {
			document.form.opt_opcao[i].disabled = false
		}
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
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
<body onload="limpar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Incluir 
				<font color="yellow">
					(NNF)
				</font>
				Abatimento / Dif. Preços para <?=genericas::nome_empresa($id_emp2);?> - Consultar Nota(s) Fiscal(is)
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" title="Consultar Nota Fiscal" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Nota Fiscal por: Número da Nota Fiscal" onclick="limpar()" id='label1'>
			<label for="label1">Número da Nota Fiscal</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar Nota Fiscal por: Cliente" onclick="limpar()" id='label2' checked>
			<label for="label2">Cliente</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='checkbox' name='opcao' value='1' title="Consultar todos as Notas Fiscais" onclick='limpar()' title='Selecionar Todos as Notas Fiscais' class="checkbox" id='label3'>
			<label for="label3">Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class="botao">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
		</td>
	</tr>      
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
- Só exibe Nota(s) Fiscal(is) do Tipo Despachada e Cancelada.

<font color="green">* </font>Será necessário o preenchimento do campo "% Comissão", sempre que a Data de Emissão for menor que a Data 
Final de Comissão. Geralmente esta data vai até o dia 25 de cada mês, ou seja o último dia p/ fechamento da Folha