<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/gerenciar2/opcoes.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PLR(S) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>PLR(S) ALTERADO(S) COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $chkt_nao_trazer_demitidos  = $_POST['chkt_nao_trazer_demitidos'];
        $cmb_periodo                = $_POST['cmb_periodo'];
        $txt_nome                   = $_POST['txt_nome'];
    }else {
        $chkt_nao_trazer_demitidos  = $_GET['chkt_nao_trazer_demitidos'];
        $cmb_periodo                = $_GET['cmb_periodo'];
        $txt_nome                   = $_GET['txt_nome'];
    }
//Não mostrará os funcionários que já foram demitidos do Sistema ...
    if($chkt_nao_trazer_demitidos == 1) $condicao_demitidos = " AND f.`status` < '2' ";
//Utilizo essas variáveis mais abaixo na hora de fazer os cálculos em JavaScript ...
    $desconto_sindicato_plr_perc = genericas::variavel(37);
    //$data_atual = date('Y-m-d');
    $data_atual = '2014-08-30';//Data Provisória ...    
    $data_sys = date('Y-m-d H:i:s');
//Busca de Dados de acordo com o Período selecionado ...
    $sql = "SELECT id_plr_periodo, date_format(data_inicial, '%d/%m/%Y') as data_inicial_periodo, date_format(data_final, '%d/%m/%Y') as data_final_periodo, date_format(data_pagamento, '%d/%m/%Y') as data_pagamento 
            FROM `plr_periodos` 
            WHERE `id_plr_periodo` = '$cmb_periodo' LIMIT 1 ";
    $campos_periodo         = bancos::sql($sql);
    $periodo                = $campos_periodo[0]['periodo'];
    $data_inicial_periodo   = $campos_periodo[0]['data_inicial_periodo'];
    $data_final_periodo     = $campos_periodo[0]['data_final_periodo'];
    $data_pagamento         = $campos_periodo[0]['data_pagamento'];
//Se a Data Atual for maior do que a Data de Pagamento, então eu ignoro esse trecho de código
    if($data_atual > data::datatodate($data_pagamento, '-')) $ignorar = 1;
/************************************JavaScript************************************/
/*1) Aqui eu já deixo pré-carregado os Valores de Absenteismo no período especificado 
pelo usuário p/ facilitar no JavaScript + abaixo...*/
    $sql = "SELECT ROUND(abs_qtde_faltas_anual / 2, 1) AS abs_qtde_faltas_semestral, ROUND(abs_valor_premio_anual / 2, 1) AS abs_valor_premio_semestral, percentagem_premio AS abs_percentagem_premio 
            FROM `plr_absenteismos` 
            WHERE `id_plr_periodo` = '$cmb_periodo' 
            ORDER BY abs_qtde_faltas_anual ";
    $campos_absenteismo = bancos::sql($sql);
    $linhas_absenteismo = count($campos_absenteismo);
    if($linhas_absenteismo > 0) {
        for($i = 0; $i < $linhas_absenteismo; $i++) {
            $vetor_abs_faltas_semestral.= 		$campos_absenteismo[$i]['abs_qtde_faltas_semestral'].', ';
            $vetor_abs_valor_premio_semestral.= $campos_absenteismo[$i]['abs_valor_premio_semestral'].', ';
            $vetor_abs_percentagem_premio.= 	$campos_absenteismo[$i]['abs_percentagem_premio'].', ';
        }
        $vetor_abs_faltas_semestral 		= substr($vetor_abs_faltas_semestral, 0, strlen($vetor_abs_faltas_semestral) - 2);
        $vetor_abs_valor_premio_semestral 	= substr($vetor_abs_valor_premio_semestral, 0, strlen($vetor_abs_valor_premio_semestral) - 2);
        $vetor_abs_percentagem_premio 		= substr($vetor_abs_percentagem_premio, 0, strlen($vetor_abs_percentagem_premio) - 2);
    }
/*2) Aqui eu já deixo pré-carregado os Valores de Aumento de Produção no período especificado 
pelo usuário p/ facilitar no JavaScript + abaixo...*/
    $sql = "SELECT ROUND(producao_anual / 2, 2) AS producao_semestral, ROUND(valor_premio_anual / 2, 2) AS producao_premio_semestral 
            FROM `plr_aumento_producoes` 
            WHERE `id_plr_periodo` = '$cmb_periodo' ORDER BY valor_premio_anual ";
    $campos_aumento_producao = bancos::sql($sql);
    $linhas_aumento_producao = count($campos_aumento_producao);
    if($linhas_aumento_producao > 0) {
        for($i = 0; $i < $linhas_aumento_producao; $i++) {
                $vetor_producao_semestral.= 		$campos_aumento_producao[$i]['producao_semestral'].', ';
                $vetor_producao_premio_semestral.= 	$campos_aumento_producao[$i]['producao_premio_semestral'].', ';
        }
        $vetor_producao_semestral           = substr($vetor_producao_semestral, 0, strlen($vetor_producao_semestral) - 2);
        $vetor_producao_premio_semestral    = substr($vetor_producao_premio_semestral, 0, strlen($vetor_producao_premio_semestral) - 2);
    }
/*3) Aqui eu busco o 'Total de Produção do Período' e a Qtde de Sub-Períodos do Período 
selecionado pelo usuário ...*/
    $sql = "SELECT SUM(albafer_tool) AS total_producao_periodo, COUNT(albafer_tool) AS qtde_sub_periodos 
            FROM `plr_produtividades` 
            WHERE `id_plr_periodo` = '$cmb_periodo' LIMIT 1 ";
    $campos_produtividade = bancos::sql($sql);
    $total_producao_periodo = $campos_produtividade[0]['total_producao_periodo'];
    $qtde_sub_periodos = $campos_produtividade[0]['qtde_sub_periodos'];
/**********************************************************************************/
//Mostra somente os funcionários que foram demitidos nos último 6 meses ...
    
/*Listo os Funcionários que ainda estão trabalhando e que possuem a marcação de "Tem direito à PLR" ...
* Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas Wilson Baldez 100, também não tem direito ao PLR ...*/
    $sql = "SELECT e.id_empresa, e.nomefantasia, f.id_funcionario, f.nome, DATE_FORMAT(f.data_admissao, '%d/%m/%Y') AS data_admissao, DATE_FORMAT(f.data_demissao, '%d/%m/%Y') AS data_demissao, f.sindicalizado 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e on e.id_empresa = f.id_empresa 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 100, 114) 
            AND f.`tem_direito_plr` = 'S' 
            AND (f.`status` < '3' OR DATE_ADD(f.data_demissao, INTERVAL 180 DAY) >= '$data_atual') $condicao_demitidos 
            ORDER BY e.id_empresa, f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
	<Script Language = 'Javascript'>
		window.location = 'gerenciar.php?cmb_periodo=<?=$_POST['cmb_periodo'];?>&valor=1'
	</Script>
<?	
    }else {
?>
<html>
<head>
<title>.:: Gerenciar PLR Versão 3 ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tratamento com os objetos antes de gravar BD ...
    var elementos = document.form.elements
    var j = 0
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_qtde_faltas[]') {
//Qtde de Faltas ...
            if(document.getElementById('txt_qtde_faltas'+j).value == '') {
                alert('DIGITE A QTDE DE FALTAS !')
                document.getElementById('txt_qtde_faltas'+j).focus()
                return false
            }
            j++
        }
    }
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_faltas[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
//Chama a função de acordo com a qtde de funcionários ...
    for(var j = 0; j < linhas; j++) {
        document.getElementById('txt_qtde_faltas'+j).value = strtofloat(document.getElementById('txt_qtde_faltas'+j).value)
        document.getElementById('txt_qtde_faltas_corrigida'+j).value = strtofloat(document.getElementById('txt_qtde_faltas_corrigida'+j).value)
        document.getElementById('txt_aumento_producao_corrigido'+j).value = strtofloat(document.getElementById('txt_aumento_producao_corrigido'+j).value)
        document.getElementById('txt_vlr_absenteismo_corrigido'+j).value = strtofloat(document.getElementById('txt_vlr_absenteismo_corrigido'+j).value)
        document.getElementById('txt_desconto_sindicato'+j).value = strtofloat(document.getElementById('txt_desconto_sindicato'+j).value)
        document.getElementById('txt_valor_total'+j).value = strtofloat(document.getElementById('txt_valor_total'+j).value)
    }
    document.form.passo.value = 2
}

//Essa função é utilizada somente na hora em que se carrega a tela ...
function chamar_calculo_plr() {
    var elementos = document.form.elements
    var valor_total = 0
    if(typeof(elementos['txt_qtde_faltas[]'][0]) == 'undefined') {
        calcular_plr(0)
        if(document.getElementById('txt_valor_total0').value != '') valor_total+= eval(strtofloat(document.getElementById('txt_valor_total0').value))
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
//Chama a função de acordo com a qtde de funcionários ...
        for(var i = 0; i < linhas; i++) {
            calcular_plr(i)
            if(document.getElementById('txt_valor_total'+i).value != '') valor_total+= eval(strtofloat(document.getElementById('txt_valor_total'+i).value))
        }
    }
    document.getElementById('txt_total_geral_plr').value = valor_total
    document.getElementById('txt_total_geral_plr').value = arred(document.getElementById('txt_total_geral_plr').value, 2, 1)
}

function calcular_plr(indice) {
/**************************************************************************************/
//Variáveis globais da função ...
	var elementos = document.form.elements
	var desconto_sindicato_plr_perc = eval('<?=$desconto_sindicato_plr_perc;?>')
	var data_inicial_periodo = '<?=$data_inicial_periodo;?>'
	var data_final_periodo = '<?=$data_final_periodo;?>'
//Invertendo ...
	data_inicial_periodo_inv = data_inicial_periodo.substr(6,4)+data_inicial_periodo.substr(3,2)+data_inicial_periodo.substr(0,2)
	data_inicial_periodo_inv = eval(data_inicial_periodo_inv)
	data_final_periodo_inv = data_final_periodo.substr(6,4)+data_final_periodo.substr(3,2)+data_final_periodo.substr(0,2)
	data_final_periodo_inv = eval(data_final_periodo_inv)
//Vetores de Absenteismo ...
	var vetor_abs_faltas_semestral 			= new Array(<?=$vetor_abs_faltas_semestral;?>)
	var vetor_abs_valor_premio_semestral 	= new Array(<?=$vetor_abs_valor_premio_semestral;?>)
	var vetor_abs_percentagem_premio 		= new Array(<?=$vetor_abs_percentagem_premio;?>)
//Vetores de Produção ...
	var vetor_producao_semestral 			= new Array(<?=$vetor_producao_semestral;?>)
	var vetor_producao_premio_semestral 	= new Array(<?=$vetor_producao_premio_semestral;?>)
//Dados de Produtividade ...
	var total_producao_periodo 				= eval(<?=$total_producao_periodo;?>)
	var qtde_sub_periodos 					= eval(<?=$qtde_sub_periodos;?>)
/**************************************************************************************/
	if(document.getElementById('txt_qtde_faltas'+indice).value == '') {//Se não tiver nada digit.
		document.getElementById('txt_qtde_meses'+indice).value = ''
		document.getElementById('txt_qtde_faltas_corrigida'+indice).value = ''
		document.getElementById('txt_vlr_absenteismo_corrigido'+indice).value = ''
		document.getElementById('txt_total_producao_corrigido'+indice).value = ''
		document.getElementById('txt_aumento_producao_corrigido'+indice).value = ''
		document.getElementById('txt_desconto_sindicato'+indice).value = ''
		document.getElementById('txt_valor_total'+indice).value = ''
	}else {//Caso o usuário tenha digitado alguma Falta então ...
//Data de Admissão do Funcionário ...
		data_admissao = document.getElementById('hdd_data_admissao'+indice).value
		data_admissao_inv = data_admissao.substr(6,4)+data_admissao.substr(3,2)+data_admissao.substr(0,2)
		data_admissao_inv = eval(data_admissao_inv)
//Data de Demissão do Funcionário ...
		data_demissao = document.getElementById('hdd_data_demissao'+indice).value
		data_demissao_inv = data_demissao.substr(6,4)+data_demissao.substr(3,2)+data_demissao.substr(0,2)
		data_demissao_inv = eval(data_demissao_inv)
//Se o PLR gerado, for um PLR de Demissão, então ...
		if(document.getElementById('chkt_demissao'+indice).checked == true) {
//Verifico se já foi preenchida a Data de Demissão do Funcionário ...
//Significa ainda não foi preenchida uma Data de Demissão p/ este funcionário ... 
			if(data_demissao_inv == 0) {
				alert('DIGITE UMA DATA DE DEMISSÃO P/ ESTE FUNCIONÁRIO !')
				document.getElementById('chkt_demissao'+indice).checked = false
				return false
			}else {
//Verifico se a Data de Admissão do Funcionário, é menor do que a Data de Início do PLR ...
				if(data_admissao_inv < data_inicial_periodo_inv) {//Entrou antes do Período
					var qtde_meses = Math.round(diferenca_datas('<?=$data_inicial_periodo;?>', data_demissao) / 30)	
				}else {//Entrou depois do Período de PLR ...
					var qtde_meses = Math.round(diferenca_datas(data_admissao, data_demissao) / 30)
				}
				var total_producao_periodo_corrigido = total_producao_periodo * 6 / qtde_sub_periodos
				var proporcionalidade = (qtde_meses / 6)
			}
//PLR normal, quando o funcionário já está admitido ...
		}else {
//Verifico se a Data de Admissão do Funcionário, é menor do que a Data de Início do PLR ...
			if(data_admissao_inv < data_inicial_periodo_inv) {//Entrou antes do Período
				var qtde_meses = 6
				var proporcionalidade = 1
			}else {
				var qtde_meses = Math.round(diferenca_datas(data_admissao, '<?=$data_final_periodo;?>')  / 30)
				var proporcionalidade = (qtde_meses / 6)
			}
			var total_producao_periodo_corrigido = total_producao_periodo
		}
//Cálculo da coluna Vlr Aum. Prod. Corrig ...
		var i = 0, producao_premio_semestral = 0
/*Aqui eu verifico qual o valor a ser buscado na tabela de Aumento Produção de acordo com a 
Produtividade que está sendo calculada na linha do usuário corrente...*/
		for(i = 0; i < vetor_producao_semestral.length; i++) {
/*Vou comparando o Valor Total da Produtividade com cada valor de Aumento Produção Mensal,
enquanto a Produtividade for maior então eu vou igualando o resultado na variável ...*/
			if(total_producao_periodo_corrigido >= vetor_producao_semestral[i]) {
				producao_premio_semestral = vetor_producao_premio_semestral[i]
//Quando o Valor Total da Produtividade for menor que o Valor de Produção Mensal, sai fora ...
			}else {
				i = vetor_producao_semestral.length
			}
		}
//Tratamento com a Qtde de Faltas ...
		if(document.getElementById('txt_qtde_faltas'+indice).value == '') {
			var qtde_faltas = 0
		}else {
			var qtde_faltas = eval(strtofloat(document.getElementById('txt_qtde_faltas'+indice).value))
		}
		
//Essa falta é a falta proporcional referente ao tempo de casa que o func. tem na Empresa ...
		if(qtde_meses != 0) {
			var qtde_faltas_corrigida = String(qtde_faltas * 6 / qtde_meses)//Transformo em String p/ poder arredondar ...
		}else {//Se qtde_meses = 0, então eu não divido, p/ não dar erro de Divisão p/ Zero
			var qtde_faltas_corrigida = String(qtde_faltas * 6)//Transformo em String p/ poder arredondar ...
		}
		qtde_faltas_corrigida = eval(strtofloat(arred(qtde_faltas_corrigida, 1, 1)))
		
		var j = 0, valor_abs_valor_premio_semestral = 0
/*Aqui eu verifico qual o valor a ser buscado na tabela de Absenteismo de acordo com a 
qtde de Faltas corrigidas que está sendo calculada na linha do usuário corrente...*/
		while(qtde_faltas_corrigida > vetor_abs_faltas_semestral[j]) j++
		if(j == vetor_abs_faltas_semestral.length) {//Já comparou com todos os valores ...
			vetor_abs_valor_premio_semestral[j] = 0//Jogadinha p/ não dar erro de lógica
		}
		valor_abs_valor_premio_semestral = vetor_abs_valor_premio_semestral[j]
		
		//Atribuo a Proporcionalidade em cima do valor de Absenteísmo e da Produção de Prêmio Semestral	...
		valor_abs_valor_premio_semestral*= proporcionalidade
		producao_premio_semestral*= proporcionalidade
		
//Se o Funcionário está pela Empresa Grupo, então ele não devido não ter registro ...
		if(document.getElementById('hdd_empresa'+indice).value == 4) {
			var desconto_sindicato = 0
		}else {//Se for Alba ou Tool então ...
			if(document.getElementById('hdd_sindicalizado'+indice).value == 'N') {//Se o func não é sindic. então ele contribuiu p/ o PLR
				var desconto_sindicato = (producao_premio_semestral + valor_abs_valor_premio_semestral) * desconto_sindicato_plr_perc / 100
			}else {//Se for sindicalizado, o func não contribui devido contribuir durante o ano ...
				var desconto_sindicato = 0
			}
		}
//Exibindo os valores nas caixinhas ...
		document.getElementById('txt_qtde_meses'+indice).value = qtde_meses
		
		document.getElementById('txt_aumento_producao_corrigido'+indice).value = producao_premio_semestral
		document.getElementById('txt_aumento_producao_corrigido'+indice).value = arred(document.getElementById('txt_aumento_producao_corrigido'+indice).value, 2, 1)
		
		document.getElementById('txt_qtde_faltas_corrigida'+indice).value = qtde_faltas_corrigida
		document.getElementById('txt_qtde_faltas_corrigida'+indice).value = arred(document.getElementById('txt_qtde_faltas_corrigida'+indice).value, 1, 1)
		
		document.getElementById('txt_vlr_absenteismo_corrigido'+indice).value = valor_abs_valor_premio_semestral
		document.getElementById('txt_vlr_absenteismo_corrigido'+indice).value = arred(document.getElementById('txt_vlr_absenteismo_corrigido'+indice).value, 2, 1)
		
		document.getElementById('txt_total_producao_corrigido'+indice).value = total_producao_periodo_corrigido
		document.getElementById('txt_total_producao_corrigido'+indice).value = arred(document.getElementById('txt_total_producao_corrigido'+indice).value, 2, 1)
		
		document.getElementById('txt_desconto_sindicato'+indice).value = desconto_sindicato
		document.getElementById('txt_desconto_sindicato'+indice).value = arred(document.getElementById('txt_desconto_sindicato'+indice).value, 2, 1)
		
		document.getElementById('txt_valor_total'+indice).value = (producao_premio_semestral + valor_abs_valor_premio_semestral) - desconto_sindicato
		document.getElementById('txt_valor_total'+indice).value = arred(document.getElementById('txt_valor_total'+indice).value, 2, 1)
	}
}

function dados_funcionario(id_funcionario_loop) {
	if(document.form.cmd_imprimir_plr.disabled == false) {
//Coloquei esse nome de id_funcionario_loop, p/ não dar conflito com a variável 'id_funcionário' da sessão
		nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop='+id_funcionario_loop+'&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '')
	}else {
		resposta = confirm('ANTES DE ALTERAR ALGUM DADO DE FUNCIONÁRIO, SALVE OS DADOS PRIMEIRO !!!\nDESEJA SALVAR ESSES DADOS ?')
		if(resposta == true) {
			validar()//Aqui tem todo o Tratamento com os campos e tal ...
			document.form.submit()
		}else {
			return false
		}
	}
}

function travar_imprimir() {
//Aqui eu travo o botão de Impressão p/ garantir que o usuário vai salvar os dados primeiros ...
	document.form.cmd_imprimir_plr.disabled = true
	document.form.cmd_imprimir_plr.style.background = '#FFFFE1'
}

function destravar_imprimir() {
//Aqui eu travo o botão de Impressão p/ garantir que o usuário vai salvar os dados primeiros ...
	document.form.cmd_imprimir_plr.disabled = false
	document.form.cmd_imprimir_plr.style.background = '#E2E9FC'
}

function imprimir_plr() {
//Abrindo Pop-Up ...
	nova_janela('relatorios/relatorio.php?cmb_periodo=<?=$cmb_periodo;?>&id_funcs_imprimir='+document.form.id_funcs_imprimir.value, 'CONSULTAR', 'F')
}

/*Criei essa função p/ impedir que o usuário digite nas caixas de texto que estão 
com o layout de desabilitadas, não desabilitei as caixas porque retardava muito o servidor 
na hora de habilitar as caixas via JavaScript na hora enviar p/ o banco de dados*/
function cursor_qtde_faltas(indice) {
	document.getElementById('lnk_funcionario'+indice).focus()
}
</Script>
</head>
<body onload="chamar_calculo_plr();document.getElementById('lnk_funcionario0').focus()">
<form name='form' method='post' action='' onsubmit="return validar()">
<!--******************************Controle de Tela******************************-->
<input type='hidden' name='cmb_periodo' value='<?=$cmb_periodo;?>'>
<input type='hidden' name='txt_nome' value='<?=$txt_nome;?>'>
<input type='hidden' name='chkt_nao_trazer_demitidos' value='<?=$chkt_nao_trazer_demitidos;?>'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='hdd_travar_imprimir' value='1'>
<!--****************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='12'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Gerenciar PLR Versão 3
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='12'>
            <font color='yellow'>
                Período: 
            </font>
            <?=$data_inicial_periodo.' à '.$data_final_periodo;?>
            <font color='yellow'>
                - Data de Pagamento: 
            </font>
            <?
                echo $data_pagamento;
                if($ignorar == 1) {//Significa que já aconteceu a Data de Pagamento ...
                    echo ' - <font color="darkred">JÁ FOI REALIZADO O PAGAMENTO</font>';
                    $class_botao = 'textdisabled';
                    $disabled_botao = 'disabled';
                }else {
                    $class_botao = 'botao';
                    $disabled_botao = '';
                }
            ?>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='12'>
            <font color='yellow'>
                Total de Produção do Período: 
            </font>
            <a href = 'detalhes_produtividade.php?cmb_periodo=<?=$cmb_periodo;?>' class='html5lightbox'>
                <font color='#FFFFFF'>
                    <?='R$ '.number_format($total_producao_periodo, 2, ',', '.');?>
                </font>
                <img src='../../../../imagem/visualizar_detalhes.png' title='Detalhes Produtividade' alt='Detalhes Produtividade' border='0'>
            </a>
            <font color='yellow'>
                - Valor Aumento Produção: 
            </font>
            <a href = 'detalhes_aumento_producao.php?cmb_periodo=<?=$cmb_periodo;?>' class='html5lightbox'>
                <font color='#FFFFFF'>
                <?
/*Busca o Valor do Prêmio Semestral de acordo com o Total da Produtividade e o período 
de PLR especificado pelo usuário ...*/
                    $sql = "SELECT ROUND(producao_anual / 2, 2) AS producao_mensal, ROUND(valor_premio_anual / 2, 2) AS valor_premio_mensal 
                            FROM `plr_aumento_producoes` 
                            WHERE `id_plr_periodo` = '$cmb_periodo' ORDER BY valor_premio_anual ";
                    $campos_aumento_producao = bancos::sql($sql);
                    $linhas_aumento_producao = count($campos_aumento_producao);
                    if($linhas_aumento_producao == 0) {//Não encontrou o valor na tab ...
                            $valor_aumento_producao = 0;
                    }else {//Encontrou o valor então ...
                        for($j = 0; $j < $linhas_aumento_producao; $j++) {
/*Vou comparando o Valor Total da Produtividade com cada valor de Aumento Produção Mensal,
enquanto a Produtividade for maior então eu vou igualando o resultado na variável ...*/
                            if($total_producao_periodo >= $campos_aumento_producao[$j]['producao_mensal']) {
                                $valor_aumento_producao = $campos_aumento_producao[$j]['valor_premio_mensal'];
//Quando o Valor Total da Produtividade for menor que o Valor de Produção Mensal, sai fora ...
                            }else {
                                $j = $linhas_aumento_producao;
                            }
                        }
                    }
                    echo 'R$ '.number_format($valor_aumento_producao, 2, ',', '.');
                ?>
                </font>
                <img src = '../../../../imagem/visualizar_detalhes.png' title='Detalhes Aumento de Produção' alt='Detalhes Aumento de Produção' border='0'>
            </a>
            <font color='yellow'>
                Absenteísmo
            </font>
            <a href = 'detalhes_absenteismo.php?cmb_periodo=<?=$cmb_periodo;?>' class='html5lightbox'>
                <img src = '../../../../imagem/visualizar_detalhes.png' title='Detalhes de Valor Redução Absenteismo' alt='Detalhes de Valor Redução Absenteismo' border='0'>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <font size='1' title='Demissão' style='cursor:help'>
                Dem.
            </font>
        </td>
        <td rowspan='2'>
            Funcionário
        </td>
        <td rowspan='2'>
            Empresa
        </td>
        <td rowspan='2'>
            <font size='1' title='Sindicalizado' style='cursor:help'>
                Sind
            </font>
        </td>
        <td rowspan='2'>
            Qtde de<br>Meses
        </td>
        <td rowspan='2'>
            <font size='1' title='Valor Aumento de Produção Corrigido' style='cursor:help'>
                Vlr Aum. <br>Prod. Corrig.
            </td>
        </td>
        <td colspan='2'>
            Qtde de Faltas
        </td>
        <td rowspan='2'>
            <font color='#FFFFFF' title='Valor Absenteismo Corrigido' style='cursor:help'>
                Vlr Abs <br>Corrigido
            </font>
        </td>
        <td rowspan='2'>
            <font size='1' title='Total de Produção Corrigido' style='cursor:help'>
                Total Prod. <br>Corrig.
            </td>
        </td>
        <td rowspan='2'>
            <font size='1' title='Desconto Sindicato' style='cursor:help'>
                Desconto <br>Sindicato
            </td>
        </td>
        <td rowspan='2'>
            <font size='1' title="Valor Total" style='cursor:help'>
                Vlr Total
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Normal
        </td>
        <td>
            Corrigida
        </td>
    </tr>
<?
//Variáveis utilizadas mais abaixo ...
	$data_inicial_periodo_usa = data::datatodate($data_inicial_periodo, '-');
	$data_final_periodo_usa = data::datatodate($data_final_periodo, '-');
	$cont = 0;//Vai auxiliar nos controles do TabIndex ...
	for($i = 0; $i < $linhas; $i++) {
//Verifico se já foi gerado algum PLR anteriormente p/ o Funcionário ...
            $sql = "SELECT * 
                    FROM `plr_funcionarios` 
                    WHERE `id_plr_periodo` = '$cmb_periodo' 
                    AND `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_plr = bancos::sql($sql);
            if(count($campos_plr) == 0) {//Se não achou dados...
                $checked = '';
            }else {//Caso tenha encontrado ...
                //Se for um PLR de demissão ...
                $checked = ($campos_plr[0]['demissao'] == 'S') ? 'checked' : '';
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='checkbox' name='chkt_demissao[]' id='chkt_demissao<?=$i;?>' value="<?=$campos[$i]['id_funcionario'];?>" onclick="calcular_plr('<?=$i;?>')" class='checkbox' <?=$checked;?>>
        </td>
        <td align='left'>
            <a href="javascript:dados_funcionario('<?=$campos[$i]['id_funcionario'];?>')" title='Detalhes Funcionário' id='lnk_funcionario<?=$i;?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
            &nbsp;
            <!--Esse parâmetro id_funcionario_current é p/ saber que essa Tela de Relatório foi acessada de Dentro do Gerenciar 
            Folha Holerith e vai me auxiliar p/ fazer uns controles diferenciais nessa Tela ...-->
            <img src = '../../../../imagem/visualizar_detalhes.png' border='0' title='Visualizar Relatório' alt='Visualizar Relatório' onclick="nova_janela('../../atraso_falta/relatorio.php?passo=1&txt_data_inicial=<?=$data_inicial_periodo;?>&txt_data_final=<?=$data_final_periodo;?>&id_funcionario_current=<?=$campos[$i]['id_funcionario'];?>', 'CONSULTAR', '', '', '', '', '420', '980', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
        <?
            if($campos[$i]['sindicalizado'] == 'S') {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_qtde_meses[]' id='txt_qtde_meses<?=$i;?>' title='Qtde de Meses' maxlength='5' size='6' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_aumento_producao_corrigido[]' id='txt_aumento_producao_corrigido<?=$i;?>' maxlength='8' size='9' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
        <?
/********************Cálculo da Qtde Horas e Faltas baseados na Portaria Eletrônica********************/
//Aqui eu zero as variáveis p/ não herdar valores do loop anterior ...
            $total_horas = 0;
            $total_faltas = 0;
/*Aqui eu busco todas as Faltas e Atrasos do Funcionário que foram lançadas na da Portaria "Eletrônica" 
no Período do PLR e que estão com a Marcação de Descontar no PLR ...*/
            $sql = "SELECT motivo, hora_inicial_plr, hora_final_plr 
                    FROM `funcionarios_acompanhamentos` 
                    WHERE `id_funcionario_acompanhado` = '".$campos[$i]['id_funcionario']."' 
                    AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial_periodo_usa' AND '$data_final_periodo_usa' 
                    AND `descontar_plr` = 'S' 
                    AND `registro_portaria` = 'S' ORDER BY data_ocorrencia DESC ";
            $campos_portaria = bancos::sql($sql);
            $linhas_portaria = count($campos_portaria);
            for($j = 0; $j < $linhas_portaria; $j++) {
                if($campos_portaria[$j]['motivo'] == 2) {//Falta
                    $total_faltas++;
                }else {//Em alguma outra situação, desconta-se o Horário ...
//1) Cálculo feito em cima das Horas ...
                    $hora_inicial = strtok($campos_portaria[$j]['hora_inicial_plr'], '.');
                    $hora_final = strtok($campos_portaria[$j]['hora_final_plr'], '.');
                    $diferenca_horas = $hora_final - $hora_inicial;

                    echo $diferenca_horas.' - ';

//2) Cálculo feito em cima das Minutos ...
                    $minuto_inicial = substr(strchr($campos_portaria[$j]['hora_inicial_plr'], '.'), 1, 2);
                    $minuto_final = substr(strchr($campos_portaria[$j]['hora_final_plr'], '.'), 1, 2);
                    $diferenca_minutos = ($minuto_final - $minuto_inicial) / 60;

                    echo $diferenca_minutos;
                    echo '<br>';

//3) Cálculo do Total de Horas ...
                    $total_horas+= $diferenca_horas + $diferenca_minutos;
                }
            }
            $total_horas = round($total_horas, 2);
            echo '<br><font color="darkblue"><b>'.$total_faltas.' Falta(s)</b></font>';
            echo '<br><font color="darkblue"><b>'.$total_horas.' hs (Ent/Saída) = '.round($total_horas / 9, 2).' dias</b></font>';
            $qtde_faltas_dias = $total_faltas + ($total_horas / 9);
            echo '<br>'.$qtde_faltas_dias;
            echo '<br>';
/******************************************************************************************************/
        ?>
            <input type='text' name='txt_qtde_faltas[]' id='txt_qtde_faltas<?=$i;?>' value='<?=number_format($qtde_faltas_dias, 1, ',', '.');?>' title='Digite a Qtde de Faltas' onfocus="cursor_qtde_faltas('<?=$i;?>')" maxlength='5' size='7' class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_qtde_faltas_corrigida[]' id='txt_qtde_faltas_corrigida<?=$i;?>' maxlength='6' size='7' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_vlr_absenteismo_corrigido[]' id='txt_vlr_absenteismo_corrigido<?=$i;?>' maxlength='8' size='9' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_total_producao_corrigido[]' id='txt_total_producao_corrigido<?=$i;?>' maxlength='11' size='12' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_desconto_sindicato[]' id='txt_desconto_sindicato<?=$i;?>' maxlength='7' size='8' onfocus="cursor_qtde_faltas('<?=$i;?>')" onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' maxlength='8' size='9' onfocus="cursor_qtde_faltas('<?=$i;?>')" class='textdisabled'>
<!--Variáveis p/ calcular o valor de direito de PLR na função em JavaScript-->
            <input type='hidden' name='hdd_sindicalizado[]' id='hdd_sindicalizado<?=$i;?>' value='<?=$campos[$i]['sindicalizado'];?>'>
            <input type='hidden' name='hdd_data_admissao[]' id='hdd_data_admissao<?=$i;?>' value='<?=$campos[$i]['data_admissao'];?>'>
            <input type='hidden' name='hdd_data_demissao[]' id='hdd_data_demissao<?=$i;?>' value='<?=$campos[$i]['data_demissao'];?>'>
            <input type='hidden' name='hdd_empresa[]' id='hdd_empresa<?=$i;?>' value='<?=$campos[$i]['id_empresa'];?>'>
<!--***********************************************************************-->
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
    </tr>
<?
            $cont++;
//Todos os funcionários encontrados, eu carrego nessa variável utilizada + abaixo ...
            $id_funcs_imprimir.= $campos[$i]['id_funcionario'].', ';
	}
?>
    <tr class='linhadestaque'>
        <td colspan='11' align='right'>
            <font color='yellow' size='2'>
                <b>Vlr Total Geral PLR R$:</b>
            </font>
        </td>
        <td align='right'>
            <input type='text' name='txt_total_geral_plr' id='txt_total_geral_plr' maxlength='10' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'gerenciar.php?cmb_periodo=<?=$cmb_periodo;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form','REDEFINIR');chamar_calculo_plr();destravar_imprimir();document.getElementById('lnk_funcionario0').focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class_botao;?>' <?=$disabled_botao;?>>
            <input type='button' name='cmd_imprimir_plr' value='Imprimir PLR' title='Imprimir PLR' onclick='imprimir_plr()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
<br>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    $sql = "SELECT e.id_empresa, e.nomefantasia, f.id_funcionario, f.nome, DATE_FORMAT(f.data_admissao, '%d/%m/%Y') AS data_admissao, DATE_FORMAT(f.data_demissao, '%d/%m/%Y') AS data_demissao, f.sindicalizado 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 100, 114) 
            AND f.`tem_direito_plr` = 'N' 
            AND (f.`status` < '3' OR DATE_ADD(f.`data_demissao`, INTERVAL 45 day) >= '$data_atual') $condicao_demitidos 
            ORDER BY e.id_empresa, f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Funcionários sem Direito ao PLR
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
//Variáveis utilizadas mais abaixo ...
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="javascript:dados_funcionario('<?=$campos[$i]['id_funcionario'];?>')" title='Detalhes Funcionário' id='lnk_funcionario<?=$i;?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<!--Aqui eu guardo os ids dos funcionários que foram carregados na Tela após do Filtro
que vai facilitar na hora de Impressão do Relatório de PLR em PDF.
-->
<?$id_funcs_imprimir = substr($id_funcs_imprimir, 0, strlen($id_funcs_imprimir) - 2);?>
<input type='hidden' name='id_funcs_imprimir' value='<?=$id_funcs_imprimir?>'>
<!--*************************************************************************************-->
</form>
</body>
</html>
<?
/*Na certa essa Tela já foi submetida quando usuário alterou algum dado do Funcionário 
pelo Link de Pop-Up alterar funcionário, sendo assim, tenho que travar o botão de impressão, 
p/ garantir que os dados sejam salvos primeiro, antes da Impressão do Relatório ...*/
        if(!empty($hdd_travar_imprimir)) echo '<Script Language="JavaScript">travar_imprimir()</Script>';
    }
}else if($passo == 2) {
    $data_sys = date('Y-m-d H:i:s');
/****************************************************************************/
//Coloco esse nome de $id_funcionario_loop na variável p/ não dar conflito com a id_funcionario que está na Sessão ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {//Disparo do Loop ...
//Tratamento com o checkbox de demissão p/ não dar erro ...
        if(is_array($_POST['chkt_demissao'])) {
            $chkt_demissao = (in_array($id_funcionario_loop, $_POST['chkt_demissao'])) ? 'S' : 'N';
        }else {
            $chkt_demissao = 'N';
        }
/*Verifico se já foi gerado um PLR do funcionário corrente no período especificado 
de PLR especificado pelo usuário ...*/
        $sql = "SELECT id_plr_funcionario 
                FROM `plr_funcionarios` 
                WHERE `id_plr_periodo` = ".$_POST['cmb_periodo']." 
                AND `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
        $campos_plr = bancos::sql($sql);
        if(count($campos_plr) == 0) {//Não achou funcionário com PLR nesse período, Insert ...
            $insert_extendido.= " (NULL, '".$_POST['cmb_periodo']."', '$id_funcionario_loop', '".$_POST['txt_qtde_meses'][$i]."', '".$_POST['txt_qtde_faltas'][$i]."', '".$_POST['txt_qtde_faltas_corrigida'][$i]."', '".$_POST['txt_vlr_absenteismo_corrigido'][$i]."', '".$_POST['txt_aumento_producao_corrigido'][$i]."', '".$_POST['txt_desconto_sindicato'][$i]."', '".$_POST['txt_valor_total'][$i]."', '$chkt_demissao', '$data_sys'), ";
        }else {//Achou sendo assim só faço Update ...
            $sql = "UPDATE `plr_funcionarios` SET `qtde_meses` = '".$_POST['txt_qtde_meses'][$i]."', `qtde_faltas` = '".$_POST['txt_qtde_faltas'][$i]."', `qtde_faltas_corrigida` = '".$_POST['txt_qtde_faltas_corrigida'][$i]."', `valor_red_absenteismo` = '".$_POST['txt_vlr_absenteismo_corrigido'][$i]."', `valor_aumento_producao` = '".$_POST['txt_aumento_producao_corrigido'][$i]."', `desconto_sindicato` = '".$_POST['txt_desconto_sindicato'][$i]."', `valor_total` = '".$_POST['txt_valor_total'][$i]."', `demissao` = '$chkt_demissao', `data_sys` = '$data_sys' WHERE `id_plr_funcionario` = '".$campos_plr[0]['id_plr_funcionario']."' LIMIT 1 ";
            bancos::sql($sql);
        }
        $valor = 3;
    }
//Se existir essa variável...
    if(!empty($insert_extendido)) {
        $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando os PLR(s) dos Funcionários ...
        $sql = "INSERT INTO `plr_funcionarios` (`id_plr_funcionario`, `id_plr_periodo`, `id_funcionario`, `qtde_meses`, `qtde_faltas`, `qtde_faltas_corrigida`, `valor_red_absenteismo`, `valor_aumento_producao`, `desconto_sindicato`, `valor_total`, `demissao`, `data_sys`) VALUES 
                $insert_extendido ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
    <Script Language='JavaScript'>
        window.location = 'gerenciar.php?passo=1&txt_nome=<?=$_POST['txt_nome'];?>&chkt_nao_trazer_demitidos=<?=$_POST['chkt_nao_trazer_demitidos'];?>&cmb_periodo=<?=$_POST['cmb_periodo'];?>&valor=<?=$valor;?>'
    </Script>
<?	
}else {
?>
<html>
<head>
<title>.:: Gerenciar PLR Versão 3 ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='cmb_periodo' value="<?=$_GET['cmb_periodo'];?>">
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerenciar PLR Versão 3 no Período: 
            <font color='yellow'>
            <?
//Busca de Dados de acordo com o Período selecionado ...
                $sql = "SELECT id_plr_periodo, DATE_FORMAT(data_inicial, '%d/%m/%Y') AS data_inicial_periodo, DATE_FORMAT(data_final, '%d/%m/%Y') AS data_final_periodo 
                        FROM `plr_periodos` 
                        WHERE `id_plr_periodo` = '$_GET[cmb_periodo]' LIMIT 1 ";
                $campos_periodo = bancos::sql($sql);
                $periodo = $campos_periodo[0]['periodo'];
                $data_inicial_periodo = $campos_periodo[0]['data_inicial_periodo'];
                $data_final_periodo = $campos_periodo[0]['data_final_periodo'];
                echo $data_inicial_periodo.' à '.$data_final_periodo
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcionário
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Funcionário' size='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_nao_trazer_demitidos' id='nao_trazer_demitidos' value='1' title='Não trazer Demitidos' class='checkbox' checked>
            <label for='nao_trazer_demitidos'>Não trazer Demitidos</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Com relação aos funcionários demitidos, só exibe aqueles que foram demitidos no decorrer dos últimos 30 dias.
</pre>