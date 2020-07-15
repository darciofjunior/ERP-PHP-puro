<?
require('../lib/segurancas.php');
require('../lib/data.php');
require('../lib/scan_erp.php');
require('/var/www/erp/albafer/lib/cache/Cache.class.php');
session_start('funcionarios');

new scan_erp();// Esta Classe verifica o Agendamento do SCAN ERP.

/**Quando o usuário muda o Módulo no Mural então registro o novo módulo na Sessão e tenho que recriar um Novo Cache**/
if(!empty($_GET['id_modulo'])) {
    //Cada funcionário terá o seu respectivo menu ...
    $MenuCache = new Cache('menu_'.$_SESSION[login]);
    $MenuCache->Limpa_cache();
    $_SESSION['id_modulo'] = $_GET['id_modulo'];
}

//Eu faço requisição do Menu depois que registro a Nova Sessão porque o mesmo o Menu só lê o id_modulo que está na Sessão ...
require('../lib/menu/menu.php');

/***********************************************************************************************/
//Exclusão dos Itens de Orçamento ...
if(!empty($id_orcamento_venda_item)) {
    //Exclui da Tabela de mensagens ESP ...
    $sql = "DELETE FROM `mensagens_esps` WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
    bancos::sql($sql);
    //Exclui da Tabela de Itens de Orçamento ...
    $sql = "DELETE FROM `orcamentos_vendas_itens` WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' limit 1";
    bancos::sql($sql);
}
/***********************************************************************************************/
//Exclusão de Avisos referente a(s) Mensagem(ns) de Produto(s) do Tipo ESP de liberação do Custo
if(!empty($id_mensagem_esp)) {
    $sql = "DELETE FROM `mensagens_esps` WHERE `id_mensagem_esp` = '$id_mensagem_esp' LIMIT 1 ";
    bancos::sql($sql);
}
/***********************************************************************************************/
//Exclusão da Última URL Acessada ...
if(!empty($_GET['excluir_ultima_url_acessada'])) {
    //Aqui eu trago a última URL que foi acessada pelo Usuário antes de cair a Sessão ...
    $sql = "SELECT ultima_url_acessada 
            FROM `logins` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_ultima_url_acessada = bancos::sql($sql);
    //Aqui eu deleto essa URL, porque o usuário já irá acessar a mesma de forma automática pelo comando mais abaixo ...
    $sql = "UPDATE `logins` SET `ultima_url_acessada` = '' WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language='JavaScript'>
        window.location = '<?=$campos_ultima_url_acessada[0]['ultima_url_acessada'];?>'
    </Script>
<?
}
/***********************************************************************************************/

//Aki é um controle para deixar fixado um módulo padrão para o usuário que estiver logado
if(!empty($_POST['chkt_modulo_padrao'])) {
    if(!empty($_POST['cmb_modulos'])) {
        $sql = "UPDATE `logins` SET `id_modulo` = '$_POST[cmb_modulos]' WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        bancos::sql($sql);
    }
}
$data = date('Y-m-d h:i:s',time());

if(($_SESSION['id_login'] == '') || ($_SESSION['id_modulo'] == '') || ($_SESSION['id_empresa'] == '')) {
    echo "<Script Language='JavaScript'>window.parent.location = 'http://".$_SERVER['HTTP_HOST']."/erp/albafer/default.php?deslogar=s&valor=3&largura='+screen.width</Script>";
    exit;
}
?>
<html>
<head>
<title>.:: MURAL ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
$(document).ready(function() {
    $(".little_bubble a").hover(function() {
        $(this).next("em").animate({
        opacity:"show", top: "-75"}, "slow")
    },
    function() {
        $(this).next("em").animate({
        opacity:"hide", top: "-85"}, "fast")
    })
})

function modulo() {
    if(document.form.cmb_modulos.value == 0) {
        alert('SELECIONE UM MÓDULO !')
    }else {
        document.location = 'mural.php?id_modulo='+document.form.cmb_modulos.value
    }
}

function fixar_modulo() {
    if(document.form.cmb_modulos.value == 0) {
        alert('SELECIONE UM MÓDULO !')
    }else {
        document.form.submit()
    }
}

//Exclusão de Avisos
function excluir_aviso(id_mensagem_esp) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE AVISO ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_mensagem_esp.value = id_mensagem_esp
        document.form.submit()
    }
}

function excluir_item_orcamento(id_orcamento_venda_item) {
    var mensagem = confirm('ESSE P.A. DESSE ITEM DE ORÇAMENTO ESTÁ COMO NÃO PRODUZIDO !!!\nDESEJA REALMENTE EXCLUIR ESTE ITEM DE ORÇAMENTO ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_orcamento_venda_item.value = id_orcamento_venda_item
        document.form.submit()
    }
}
</Script>
<body topmargin='40%'>
<form name='form' method='post' action=''>
<input type='hidden' name='id_mensagem_esp'>
<input type='hidden' name='id_orcamento_venda_item'>
<?
/***************************************************************************************/
/*************************Lógica da Portaria Eletrônica*********************************/
/***************************************************************************************/
//Verifico se o usuário logado é Chefe, qual é o seu Departamento e se o mesmo tem login no ERP ...
    $sql = "SELECT DISTINCT(id_funcionario) 
            FROM `funcionarios` 
            WHERE `id_funcionario_superior` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_chefe = bancos::sql($sql);
//Verifico qual o Departamento do Funcionário logado ...
    $sql = "SELECT id_departamento 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_departamento = bancos::sql($sql);
//Verifico se o usuário logado tem Login ...
    $sql = "SELECT DISTINCT(id_login) 
            FROM `logins` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    if(count($campos_chefe) == 1 && count($campos_login) == 1) {//O Funcionário logado é chefe e tem login ...
/*Verifico todas as ocorrências dos funcionários "que ainda trabalham na Empresa" do usuário logado, no caso 'Chefe' que possuem 
pendência de Portaria, no estágio de Chefia Liberar ...*/
        $sql = "SELECT fa.id_funcionario_acompanhamento 
                FROM `funcionarios` f 
                INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                WHERE f.`id_funcionario_superior` = '$_SESSION[id_funcionario]' 
                AND f.`status` < '3' LIMIT 1 ";
        $campos_pendencia = bancos::sql($sql);
        //Existem pendências - estágio de Portaria ...
        if(count($campos_pendencia) == 1) $exibir_link = 1;
/*************************Chefes de Férias ou Afastados**************************/
/*Verifico se esse Chefe logado tem funcionários subordinados a ele e se algum desses funcionários subordinados estão de Férias ou Afastados ...*/
        $sql = "SELECT DISTINCT(f.`id_funcionario`) 
                FROM `funcionarios` f 
                WHERE f.`id_funcionario_superior` = '$_SESSION[id_funcionario]' 
                AND (f.`status` = '0' OR f.`status` = '2') ";
        $campos_subordinados = bancos::sql($sql);
        $linhas_subordinados = count($campos_subordinados);
//Existem funcionários de Férias ...
        for($i = 0; $i < $linhas_subordinados; $i++) {
/*Verifico se algum desses funcionários subordinados que está de Férias, também são chefes 
de outros funcionários ... - No caso este seria um sub-chefe ...*/
            $sql = "SELECT DISTINCT(f.`id_funcionario`) 
                    FROM `funcionarios` f 
                    WHERE f.`id_funcionario_superior` = ".$campos_subordinados[$i]['id_funcionario']." LIMIT 1 ";
            $campos_sub_chefe = bancos::sql($sql);
//Significa que este funcionário é um sub-chefe, ou seja subordinado ao Chefe Principal ...
            if(count($campos_sub_chefe) == 1) {
/*Verifico se os funcionários "que ainda trabalham na Empresa" que são subordinados aos Chefes que são subordinados aos seus Chefes (rs) 
possuem pendência no estágio de Chefia Liberar ...*/
                $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                        WHERE f.`id_funcionario_superior` = '".$campos_subordinados[$i]['id_funcionario']."' 
                        AND f.`status` < '3' LIMIT 1 ";
                $campos_pendencia = bancos::sql($sql);
                if(count($campos_pendencia) == 1) {//Existem pendências - estágio de Portaria ...
                    $exibir_link = 1;
                    $i = $linhas_subordinados;
                }
            }
        }
/*******************************************************************/
    }
/****************************************************************************************************************/
/*********************************************Parte do Depto Pessoal*********************************************/
/****************************************************************************************************************/
/*O rh "Departamento Pessoal", enxerga somente as ocorrências na qual ele mesmo é chefe, ocorrências de funcionários que 
são chefe que não tem login no ERP exemplo Ademar e todas as Ocorrências que estão com estágio acima de "RH Liberar" ...*/

    //Verifico se id_funcionario logado trabalha no Departamento de RH ...
    $sql = "SELECT `id_departamento` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_depto = bancos::sql($sql);
    if($campos_depto[0]['id_departamento'] == 24) {//Recursos Humanos ...
        //Listagem de todos os funcionários que são Chefe e possuem logins no ERP ...
        $sql = "SELECT DISTINCT(f.`id_funcionario_superior`) AS id_funcionario_superior 
                FROM `funcionarios` f 
                INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario_superior` 
                /**********************Modificado no dia 22/06/2017**********************
                Não trago propositalmente os Diretores Roberto 62, Sandra 66, Wilson 68 p/ que a pessoa do RH possa liberar 
                a ocorrência desses 3 também - a variável 'id_func_acomp_ignorar' não carregar esses 3 valores aqui, consequentemente 
                vai tratar com os 3 diretores mais abaixo ...*/
                /**********************Modificado no dia 02/07/2018**********************
                Agora propositalmente não trago a Agueda 32 ...*/
                AND f.`id_funcionario_superior` NOT IN (32, 62, 66, 68) ";
        $campos_chefe = bancos::sql($sql);
        $linhas_chefe = count($campos_chefe);
        for($i = 0; $i < $linhas_chefe; $i++) {
/*Busca de todas as ocorrências dos funcionários "que ainda trabalham na Empresa" que são subordinados a esses chefes que possuem login 
e que possuem pendência de Portaria no estágio de Chefia Liberar ...*/
            $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                    WHERE f.`id_funcionario_superior` = '".$campos_chefe[$i]['id_funcionario_superior']."' 
                    AND f.`status` < '3' ";
            $campos_subordinados = bancos::sql($sql);
            $linhas_subordinados = count($campos_subordinados);
            for($j = 0; $j < $linhas_subordinados; $j++) $id_func_acomp_ignorar.= $campos_subordinados[$j]['id_funcionario_acompanhamento'].', ';
        }
//Significa que não carregou essa variável no Loop ...
        if(strlen($id_func_acomp_ignorar) == 0) {
            $id_func_acomp_ignorar = 0;
        }else {
            $id_func_acomp_ignorar = substr($id_func_acomp_ignorar, 0, strlen($id_func_acomp_ignorar) - 2);
        }
/*Listagem de todas as ocorrências de todos os funcionários "que ainda trabalham na Empresa" subordinados que possuem pendência de Portaria, 
no estágio de Chefia Liberar, RH Chefia e seus chefes não possuem login no Sistema  ...*/
        $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                FROM `funcionarios` f 
                /**********************Modificado no dia 22/06/2017**********************
                Como a própria pessoa do RH agora tem acesso para liberar as ocorrências dos diretores, só não exibo essa porque ela não pode
                liberar suas próprias ocorrências sem um parecer de seu superior ...*/
                INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND ((fa.`status_andamento` = '0' AND fa.`id_funcionario_acompanhado` <> '111') OR fa.`status_andamento` = '1') AND fa.`id_funcionario_acompanhamento` NOT IN ($id_func_acomp_ignorar) 
                WHERE f.`id_funcionario_superior` <> '0' 
                AND f.`status` < '3' LIMIT 1 ";
        $campos_pendencia = bancos::sql($sql);
        if(count($campos_pendencia) == 1) {//Existem pendências - estágio de Chefia e Portaria ...
            $exibir_link = 1;
        }
    }
/****************************************************************************************************************/
/*Mostra p/ o usuário logado, no caso 'Chefe', todas as pendências 
que existem a ser liberadas de seus funcionários que chegaram em Atraso, 
ou algum outro motivo ...*/
    if($exibir_link == 1) {
/*Direcionará o chefe p/ a Tela de Pendências aonde, exibirá somente os seus funcionários 
que possuem Atraso(s) / Falta(s) / Saída(s) a ser(em) solucionado(s) ...*/
?>
<table width='82%' cellspacing='0' cellpadding='1' border='1' bordercolor='darkblue' align='center'>
    <tr align='center'>
        <td>
            <a href="../modulo/rh/atraso_falta/alterar.php" class='link'>
                Existe(m) Atraso(s) / Falta(s) / Saída(s) a ser(em) solucionado(s)&nbsp;
                <img src = "../imagem/lapis_pendencias.jpg" title="Existe(m) Atraso(s) / Falta(s) / Saída(s) a ser(em) solucionado(s)" width='75' height='25' border='0'>
            </a>
        </td>
    </tr>
</table>
<br>
<?
    }
/***************************************************************************************/
/************************ Lógica MSNs Produtos Tipo ESP ********************************/
/***************************************************************************************/
/*Verifico se existe(m) Mensagem(ns) p/ Produto(s) do Tipo ESP, relacionado(s) a liberação do Custo
para o usuário logado*/
    $sql = "SELECT * 
            FROM `mensagens_esps` 
            WHERE `status` = '1' 
            AND `id_login` = '$_SESSION[id_login]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Disparo do Loop
    if($linhas > 0) {
        for($i = 0; $i < $linhas; $i++) {
/*Verifico se esse item de P.A. do Orçamento, está com a marcação de 'status_nao_produzir' se o Orçamento está 
congelado e também qual o Funcionário que congelou esse orçamento ...*/
            $sql = "SELECT ov.`congelar`, ov.`id_funcionario`, pa.`status_nao_produzir` 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE ovi.`id_orcamento_venda_item` = ".$campos[$i]['id_orcamento_venda_item'];
            $campos_nao_produzir 	= bancos::sql($sql);
            $qtde_registrou 		= count($campos_nao_produzir);
            if($qtde_registrou == 1) {
                $congelar                   = $campos_nao_produzir[0]['congelar'];
                $id_funcionario_congelou    = $campos_nao_produzir[0]['id_funcionario'];
                $status_nao_produzir        = $campos_nao_produzir[0]['status_nao_produzir'];
            }
?>
<table width='82%' cellspacing='2' cellpadding='2' border='1' bordercolor='red' align='center'>
    <tr>
        <td>
            <img src = '../imagem/atencao.jpg' height='28' width='28' title='Aviso' alt='Aviso' border='0'>
            &nbsp;
            <font size='2'>
                <b><?=$campos[$i]['mensagem'];?></b>
            </font>
        </td>
        <?
/*Caso não tenha encontrado esse Item na Tabela de Orçamentos ou ... 

Se o P.A. desse item de Orçamento, está com a marcação de 'status_nao_produzir', ou então se o Orçamento está 
congelado e o Funcionário que congelou for diferente do que está logado então eu apresento 
esse link de exclusão ...*/
            if($qtde_registrou == 0 || $status_nao_produzir == 1 || ($congelar == 'S' && $id_funcionario != $id_funcionario_congelou)) {
        ?>
        <td>
            <img src = '../imagem/menu/excluir.png' border='0' title='Excluir Item de Orçamento' alt='Excluir Item de Orçamento' style='cursor:pointer' onclick="excluir_item_orcamento('<?=$campos[$i]['id_orcamento_venda_item'];?>')">
        </td>
        <?
            }
        ?>
        <td width='25' align='center'>
            <img src = '../imagem/help.jpg' border='0' width='20' height='20' title='Ajuda - Clique Aqui' alt='Ajuda - Clique Aqui' onclick="alert('OS AVISOS SUMIRÃO SOMENTE AO CONGELAR ORÇAMENTO !')">
        </td>
    </tr>
</table>
<?
//Para dar uma distância entre as Tabelas ...
            echo '<font size="-2">&nbsp;</font>';
        }
    }
/***************************************************************************************/
/*************** Lógica - Ocorrência Diária atendida por outro Vendedor ****************/
/***************************************************************************************/
	//Aqui eu busco todos os Orçamentos que o Vendedor tem como pendência para resolver ...
        $sql = "SELECT id_representante 
                FROM `representantes_vs_funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_representante   = bancos::sql($sql);
	if(count($campos_representante) > 0) {//Se o funcionário realmente for um Representante ...
            $sql = "SELECT vp.id_orcamento_venda, ov.data_emissao 
                    FROM `vendedores_pendencias` vp 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = vp.`id_orcamento_venda` AND ov.`status` < '2' 
                    WHERE vp.`id_orcamento_venda` > '0' 
                    AND vp.`id_representante` IN (".$campos_representante[0]['id_representante'].") ORDER BY vp.id_orcamento_venda LIMIT 5 ";
            $campos_orcamentos = bancos::sql($sql);
            $linhas_orcamentos = count($campos_orcamentos);
            //Aqui eu busco todos os Pedidos que o Vendedor tem como pendência para resolver ...
            $sql = "SELECT vp.id_pedido_venda, pv.data_emissao 
                    FROM `vendedores_pendencias` vp 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = vp.`id_pedido_venda` 
                    WHERE vp.`id_pedido_venda` > '0' 
                    AND vp.`id_representante` IN (".$campos_representante[0]['id_representante'].") ORDER BY vp.id_pedido_venda LIMIT 5 ";
            $campos_pedidos = bancos::sql($sql);
            $linhas_pedidos = count($campos_pedidos);
            if($linhas_orcamentos > 0 || $linhas_pedidos > 0) $exibir_pend_acomp = 1;
	}
/*Mostra p/ o usuário logado, no caso 'Vendedor', todos as Pendências de Acompanhamentos 
que existem a ser liberadas como sendo de Orçamentos ou Pedidos ...*/
	if($exibir_pend_acomp == 1) {
            $data_atual = date('Y-m-d');//Será utilizado mais abaixo
?>
<table width="82%" cellspacing='0' cellpadding='0' border='5' bordercolor='darkblue' align='center'>
	<tr align='center'>
		<td bgcolor='#CECECE' colspan="2">
			<img src = "../imagem/exclamacao.gif" title="Ocorrências" alt="Ocorrências" height="26" border="0">
			&nbsp;
			<font face='Verdana, Arial, Helvetica, sans-serif' color="red" size="0">
				<b>OCORRÊNCIA(S) DIÁRIA(S) ATENDIDA(S) POR OUTRO VENDEDOR</b>
			</font>
			&nbsp;
			<img src = "../imagem/exclamacao.gif" title="Ocorrências" alt="Ocorrências" height="26" border="0">
		</td>
	</tr>
	<tr align='center'>
		<td width="50%">
			<font face='Verdana, Arial, Helvetica, sans-serif' color="darkgreen" size="0">
				<b>ORÇAMENTO(S) => </b>
			</font>
			<?
				for($i = 0; $i < $linhas_orcamentos; $i++) {
					$diferenca_dias = data::diferenca_data($campos_orcamentos[$i]['data_emissao'], $data_atual);
					$color = ($diferenca_dias[0] >= 3) ? 'red' : 'darkblue';
					if($diferenca_dias[0] >= 3) $alert = 1;
			?>
				<a href="../modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$campos_orcamentos[$i]['id_orcamento_venda'];?>" class='link'>
					<font color='<?=$color;?>'>
						<?=$campos_orcamentos[$i]['id_orcamento_venda'];?>
					</font>
				</a>
				&nbsp;
			<?	
				}
			?>
		</td>
		<td width="50%">
			<font face='Verdana, Arial, Helvetica, sans-serif' color="darkgreen" size="0">
				<b>PEDIDO(S) => </b>
			</font>
			<?
				for($i = 0; $i < $linhas_pedidos; $i++) {
					$diferenca_dias = data::diferenca_data($campos_pedidos[$i]['data_emissao'], $data_atual);
					$color = ($diferenca_dias[0] >= 3) ? 'red' : 'darkblue';
					if($diferenca_dias[0] >= 3) $alert = 1; 
			?>
				<a href="../modulo/vendas/pedidos/itens/index.php?id_pedido_venda=<?=$campos_pedidos[$i]['id_pedido_venda'];?>" class='link'>
					<font color='<?=$color;?>'>
						<?=$campos_pedidos[$i]['id_pedido_venda'];?>
					</font>
				</a>
				&nbsp;
			<?
				}
				//Aqui eu informo o usuário de que existem ocorrências pendentes ...
				if($alert == 1) {
			?>
				<Script Language = 'JavaScript'>
					alert('EXISTE(M) OCORRÊNCIA(S) DIÁRIA(S) ATENDIDA(S) POR OUTRO VENDEDOR REALIZADA(S) A MAIS DE 3 DIAS !')
				</Script>
			<?					
				}
			?>
		</td>
	</tr>
</table>
<br>
<?
	}
/***************************************************************************************/
/************************ Lógica da Projeção Trimestral ********************************/
/***************************************************************************************/
	//Essa verificação é feita apenas no último mês de cada Trimestre ...
	if($id_representante > 0 && (date('m') == 3 || date('m') == 6 || date('m') == 9 || date('m') == 12)) {
		if(date('m') == 3) {//Significa que o Mês é pertinente ao 1º Trimestre ...
			$condicao_periodo_projetado = " SUBSTRING(pt.data_sys, 1, 10) BETWEEN '".date('Y')."-01-01' AND '".date('Y')."-03-31' ";
			$condicao_periodo_pedido 	= " data_emissao BETWEEN '".date('Y')."-01-01' AND '".date('Y')."-03-31' ";
		}else if(date('m') == 6) {//Significa que o Mês é pertinente ao 2º Trimestre ...
			$condicao_periodo_projetado = " SUBSTRING(pt.data_sys, 1, 10) BETWEEN '".date('Y')."-04-01' AND '".date('Y')."-06-30' ";
			$condicao_periodo_pedido 	= " data_emissao BETWEEN '".date('Y')."-04-01' AND '".date('Y')."-06-30' ";
		}else if(date('m') == 9) {//Significa que o Mês é pertinente ao 3º Trimestre ...
			$condicao_periodo_projetado = " SUBSTRING(pt.data_sys, 1, 10) BETWEEN '".date('Y')."-07-01' AND '".date('Y')."-09-30' ";
			$condicao_periodo_pedido 	= " data_emissao BETWEEN '".date('Y')."-07-01' AND '".date('Y')."-09-30' ";
		}else {//Significa que o Mês é pertinente ao 4º Trimestre ...
			$condicao_periodo_projetado = " SUBSTRING(pt.data_sys, 1, 10) BETWEEN '".date('Y')."-10-01' AND '".date('Y')."-12-31' ";
			$condicao_periodo_pedido 	= " data_emissao BETWEEN '".date('Y')."-10-01' AND '".date('Y')."-12-31' ";
		}
//Busca as Projeções de Venda do Representante logado no Trimestre e Ano Atual que ainda não possuem Justificativas ...
		$sql = "SELECT DISTINCT(pt.id_cliente), pt.id_projecao_trimestral, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) as cliente 
				FROM `projecoes_trimestrais` pt 
				INNER JOIN `clientes` c ON c.id_cliente = pt.id_cliente 
				INNER JOIN `clientes_vs_representantes` cr ON cr.id_cliente = pt.id_cliente AND cr.id_representante = '$id_representante' 
				WHERE $condicao_periodo_projetado 
				AND pt.justificativa = '' ";
		$campos_projetado = bancos::sql($sql);
		$linhas_projetado = count($campos_projetado);
		for($i = 0; $i < $linhas_projetado; $i++) {
			/*Aqui eu verifico se já existe pelo menos 1 Pedido do Cliente do Loop com Projeção Realizada 
			dentro do respectivo Trimestre ...*/
			$sql = "SELECT id_pedido_venda 
					FROM `pedidos_vendas` 
					WHERE $condicao_periodo_pedido 
					AND id_cliente = '".$campos_projetado[$i]['id_cliente']."' 
					AND projecao_vendas = 'S' LIMIT 1 ";
			$campos_pedidos = bancos::sql($sql);
			if(count($campos_pedidos) == 0) {//Significa que não foi feita nenhuma projeção para o devido Cliente ...
				$clientes_sem_projecao.= $campos_projetado[$i]['cliente'].'\n';
				$id_projecao_trimestral.= $campos_projetado[$i]['id_projecao_trimestral'].', ';
			}
		}
		$mensagem_projecao = 'O(S) CLIENTE(S) ABAIXO POSSUE(M) PROJEÇÃO(ÕES) GERADA(S): \n\n';
		$mensagem_projecao.= $clientes_sem_projecao;
		$mensagem_projecao.= '\nMAS ATÉ O MOMENTO, NÃO FOI GERADO NENHUM PEDIDO EM CIMA DESTA(S) PROJEÇÃO(ÕES).\nDESEJA JUSTIFICAR A(S) MESMA(S) ?';
		
		if(!empty($clientes_sem_projecao)) {
			$id_projecao_trimestral = substr($id_projecao_trimestral, 0, strlen($id_projecao_trimestral) - 2);
?>
		<Script Language = 'JavaScript'>
			var resposta = confirm('<?=$mensagem_projecao;?>')
			if(resposta == true) {
				window.location = '../modulo/vendas/relatorio/projeto_trimestral/justificar_projeto_trimestral.php?id_projecao_trimestral=<?=$id_projecao_trimestral;?>'
			}
		</Script>
<?
		}
	}
/***************************************************************************************/
/******************** Lógica do Relatório de Atendimento Diário ************************/
/***************************************************************************************/
/*Aqui eu trago todos os Registros Diários que o "Representante" do Cliente ainda não deu nenhum Feedback e que 
o Autor do Registro seja diferente do usuário logado que terá que responder ...*/
	$sql = "SELECT id_atendimento_diario 
                FROM `atendimentos_diarios` 
                WHERE `id_funcionario_responder` = '$_SESSION[id_funcionario]' 
                AND `id_funcionario_registrou` <> `id_funcionario_responder` 
                AND `feedback` = '' LIMIT 1 ";
	$campos_atendimentos_diarios = bancos::sql($sql);
	if(count($campos_atendimentos_diarios) == 1) {
?>
<table width="82%" cellspacing='0' cellpadding='0' border='1' bordercolor='black' align='center'>
	<tr class="erro" align='center'>
		<td bgcolor='#FFFFE0'>
			*** EXISTE(M) OCORRÊNCIAS DE ATENDIMENTO DIÁRIO PARA RESPONDER => 
			<a href="../modulo/vendas/atendimento_diario/responder_feedback.php" class='link'>
				<img src = "../imagem/feedback.png" title="Existe(m) Ocorrências de Atendimento Diário para Responder Feedback" border='0'>
			</a>
		</td>
	</tr>
</table>
<?
	}
/***************************************************************************************/
/************************ Lógica de Restaurar Último Acesso ****************************/
/***************************************************************************************/
//Aqui eu verifico se existe uma última URL que foi acessada pelo Usuário antes de cair a Sessão ...
	$sql = "SELECT ultima_url_acessada 
                FROM `logins` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' 
                AND `ultima_url_acessada` <> '' LIMIT 1 ";
	$campos_ultima_url_acessada = bancos::sql($sql);
	if(count($campos_ultima_url_acessada) == 1) {
?>
<table width="25%" cellspacing='0' cellpadding='0' border='1' align='center'>
    <tr class="confirmacao" align='center' valign="top">
        <td>
            * RESTAURAR ÚLTIMO ACESSO => 
            <a href="<?=$PHP_SELF.'?excluir_ultima_url_acessada=1';?>" class='link'>
                <img src = "../imagem/restaurar.png" title="Restaurar Último Acesso" border='0'>
            </a>
        </td>
    </tr>
</table>
<?
	}
?>
<table width="82%" cellspacing='2' cellpadding='1' border="0" align='center'>
	<tr class="linhadestaque" align='center'>
		<td colspan='3'>
			<img src="../imagem/marcas/Logo_60_anos.jpg" height='170' width='100%'>
		</td>
	</tr>
        <tr class='linhanormal'>
                <td width='30%' onclick="window.open('http://www.grupoalbafer.com.br', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
                    <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
                    <b><a href='#' class='link'>
                            <font size='-2'>
                                    Grupo Albafer
                            </font>
                    </a></b>
		</td>
		<td width='30%' onclick="window.open('http://babelfish.altavista.com', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Tradutor Web (Altavista)
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"> 
			<img src="../imagem/icones2/30.gif" width='14' heigth='14'>&nbsp;
			<b><a href="../modulo/rh/aniversariante/consultar.php" class='link'>
				<font size='-2'>
					Lista de Aniversariantes
				</font>
			</a></b>
		</td>
	</tr>
        <tr class='linhanormal'>
                <td width='30%' onclick="window.open('http://www4.bcb.gov.br/pec/taxas/port/ptaxnpesq.asp?id=txcotacao&id=txcotacao', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					BCB (Banco Central do Brasil)
				</font>
			</a></b>
		</td>    
		<td width='30%' onclick="window.open('https://maps.google.com.br/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Google Maps
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="window.open('http://www.gndi.com.br/beneficiario/saude/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src="../imagem/cruz_vermelha.png" width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Agendamento de Consultas - Grupo NotreDame Intermédica
				</font>
			</a></b>
		</td>
	</tr>
	<tr class='linhanormal'>
                <td width='30%' onclick="window.open('http://economia.uol.com.br/cotacoes/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=no, scrollbars=yes, toolbar=no, location=no, directories=no, status=no, menubar=no, fullscreen=no');cor_clique_celula(this, '#C6E2FF');return false;" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					UOL Câmbio
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="window.open('http://www.freetranslation.com', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Tradutor Web (freetranslation)
				</font>
			</a></b>
		</td>
                <td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"> 
			<img src="../imagem/cifrao.png" width='14' heigth='14'>&nbsp;
			<b><a href="../modulo/financeiro/cambio/consultar_mural.php" class='link'>
				<font size='-2'>
					Câmbio
				</font>
			</a></b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width='30%' onclick="window.open('http://www.buscacep.correios.com.br/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8');">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Consultar Cep (Correios)
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="window.open('http://www.itau.com.br', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Itaú
				</font>
			</a></b>
		</td>
                <?
                    /*************************************Ouvidoria*************************************/
                    /*Dona Sandra é a única que não tem Ouvidoria, devido alguns assuntos desagradáveis 
                    que escreveram anteriormente ...*/
                    if($_SESSION['id_funcionario'] == 66) {
                ?>
                <td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
                    &nbsp;
		</td>
                <?
                    }else {
                ?>
		<td width='30%' onclick="html5Lightbox.showLightbox(7, '../modulo/classes/ouvidorias/incluir.php');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<b><a href='#' class='link'>
				<font color="red" size='-2'>
					*** OUVIDORIA
				</font>
			</a></b>
		</td>
                <?
                    }
                    /***********************************************************************************/
                ?>
	</tr>
	<tr class='linhanormal'>
            <td width='30%' onclick="window.open('http://www2.correios.com.br/sistemas/precosPrazos/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8');">
                <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
                <b><a href='#' class='link'>
                    <font size='-2'>
                        Consultar Sedex (Correios)
                    </font>
                </a></b>
            </td>
            <td width='30%' onclick="window.open('http://www.bradesco.com.br/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
                <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
                <b><a href='#' class='link'>
                    <font size='-2'>
                        Bradesco
                    </font>
                </a></b>
            </td>
        <?
//Redicionamento Monitoramento da Empresa
          if (substr($_SERVER['HTTP_REFERER'], 7, 3) == 189) {//Caminho do Externo
               //Tratamento com a variável endereço ...
               $endereco = substr($_SERVER['HTTP_REFERER'], 7);//Ignorei o HTTP://
               $endereco = strtok($endereco, ':');
               $endereco.= ':5500/';
               
               $url = $endereco;
          }else {//Caminho do Interno
                $url = '192.168.1.252/Multiview.htm';
          }
        ?>
        <td width='30%' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            ???
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%' onclick="window.open('https://www2.correios.com.br/sistemas/rastreamento/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
            <b><a href='#' class='link'>
                <font color='black' size='-2'>
                    Consultar Sedex (Rastreamento)
                </font>
            </a></b>
        </td>
        <td width='30%' onclick="window.open('http://www.bradescopessoajuridica.com.br', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
            <b><a href='#' class='link'>
                <font size='-2'>
                    Bradesco (Empresarial)
                </font>
            </a></b>
        </td>
        <td width='30%' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            ???
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%' onclick="window.open('http://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATBHE/ConsultaOptantes.app/ConsultarOpcao.aspx', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
            <b><a href='#' class='link'>
                <font size='-2' color='green'>
                    Consultar Simples Nacional - (Faturamento)
                </font>
            </a></b>
        </td>
        <td width='30%' onclick="window.open('https://www.google.com.br/', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
            <b><a href='#' class='link'>
                <font size='-2'>
                    Google (Site de Pesquisa)
                </font>
            </a></b>
        </td>
        <td width='30%' onclick="window.open('http://<?=$url;?>', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <img src="../imagem/icones2/29.gif" width='14' heigth='14'>&nbsp;
            <b><a href='#' class='link'>
                <font size='-2'>
                    Monitoramento da Empresa
                </font>
            </a></b>
        </td>
    </tr>
	<tr class='linhanormal'>
		<td width='30%' onclick="window.open('http://www.fazenda.rj.gov.br', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2' color='green'>
					Gerar DARJ(s) - (Faturamento)
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="window.open('https://sitenet.serasa.com.br/Logon/index.jsp', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2'>
					Serasa
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"> 
                    <img src = '../imagem/calendario.gif' width='11' heigth='11'>
                    &nbsp;
                    <a href='calendario/calendario.php' class='link'>
                        <font size='-2'>
                            Calendário Semanal
                        </font>
                    </a>
		</td>
	</tr>
	<tr class='linhanormal'>
                <td width='30%' onclick="window.open('http://www.gnre.pe.gov.br/gnre/index.html', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
			<b><a href='#' class='link'>
				<font size='-2' color='green'>
					Gerar GNRE(s) - (Faturamento)
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
			<img src="../imagem/cadeado.gif" width='10' heigth='10'>&nbsp;
			<b><a href="../modulo/sistema/logins/alterar_senha.php" class='link'>
				<font size='-2'>
					Alterar Senha
				</font>
			</a></b>
		</td>
		<td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
                    <img src="../imagem/cep.jpg" width='14' heigth='14'>&nbsp;
                    <a href = "javascript:alert('INDISPONÍVEL !')">
                        <font size='-2'>
                            Ceps do ERP
                            <font color='red'>
                                <b>(INDISPONÍVEL)</b>
                            </font>
                        </font>
                    </a>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width='30%' onclick="window.open('https://nfe.fazenda.sp.gov.br/ConsultaNFe/consulta/publica/ConsultarNFe.aspx', 'SITE', 'top=2, left=2, height='+(screen.height-25)+', width='+(screen.width)+', resizable=yes, scrollbars=yes, toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, fullscreen=no');cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
                    <img src = '../imagem/icones2/32.gif' width='14' heigth='14'>&nbsp;
                    <a href='#' class='link'>
                        <font size='-2' color='green'>
                            Carta de Correção - (Faturamento)
                        </font>
                    </a>
		</td>
		<td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"> 
                    <a href = 'reip.php' class='html5lightbox'>
                        <font color='black' size='-2'>
                            *** REIP (Uso do Depto. de TI)
                        </font>
                    </a>
		</td>
                <td width='30%' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"> 
			<img src="../imagem/icones2/33.gif" width='14' heigth='14'>&nbsp;
			<?
                            //Esse link, só poderá abrir p/ alguns usuários que por enquanto são: Rivaldo 67, Roberto 62 e Dárcio 98 porque programa ...
                            if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
                                $url = "../modulo/rh/funcionario/consultar_resumido.php";
                            }else {
                                $url = "javascript:alert('USUÁRIO SEM PERMISSÃO P/ ESTE LINK !')";
                            }
			?>
			<b><a href="<?=$url;?>" class='link'>
				<font size='-2'>
					Dados de Funcionários
				</font>
			</a></b>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan="3">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='red'>
			<?
				if(empty($valor)) $valor = 0;
			?>
				<?=$mensagem[$valor];?>
			</font>
			<marquee scrolldelay="100" loop="100" scrollamount="5">
			<?
				$cores = array('green', '#ff9900', 'red');
				$indice = 0;
				$espacos = '';

				for($j = 0; $j < 180; $j++) $espacos.= '&nbsp';

				$data_sys = date('Y-m-d H:i:s');
//Aqui eu listo todas as Mensagens que foram cadastradas para o Mural ...
				$sql = "SELECT mensagem 
						FROM mural_msgs 
						WHERE ((tipo_apresentacao = 'C') 
						OR (tipo_apresentacao = 'T' AND `data_show_inicial` <= '$data_sys' AND `data_show_final` >= '$data_sys')) 
						AND ativo = 1 ";
				$campos = bancos::sql($sql);
				$linhas = count($campos);
				if($linhas > 0) {//Se achar pelo menos 1 mensagem cadastrada ...
				
//Aqui eu sorteio dos indíces das Mensagens p/ que estas passem no Mural de forma "Randômica" ...
					for($i = 0; $i < $linhas; $i++) $array_indices[] = $i;
					shuffle($array_indices);
//Disparo das Mensagens
					for($i = 0; $i < $linhas; $i++) {
///Essa variável vai me auxiliar p/ fazer o controle de cores ...
						if($indice == 3) $indice = 0;
//Se o usuário = 'Roberto 62 ou Dárcio 98 porque programa', então o Marquee se torna um link, p/ que se possa trocar a mensagem ...
					if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
							echo "<a href = 'incluir_mensagens.php' class='html5lightbox'>"."<font color='".$cores[$indice]."' size=2><b>".$campos[$array_indices[$i]]['mensagem']."</a>".$espacos."</b></font>";
						}else {
							echo "<font color=".$cores[$indice]." size='2'><b>".$campos[$array_indices[$i]]['mensagem'].'</a>'.$espacos."</b></font>";
						}
						$indice++;
					}
				}else {//Se não achar nenhuma mensagem então ...
//Se o usuário = 'Roberto 62 ou Dárcio 98 porque programa', então o Marquee se torna um link, p/ que se possa trocar a mensagem ...
					if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
						echo "<a href = 'incluir_mensagens.php' class='html5lightbox'>"."<font color='brown' size='2'><b>*** BEM VINDO AO GRUPO ALBAFER ***</b></a></font>";
					}else {
						echo "<font color='brown' size='2'><b>*** BEM VINDO AO GRUPO ALBAFER ***</b></a></font>";
					}
				}
			?>
			</marquee>
		</td>
	</tr>
<table width="82%" cellspacing=5 cellpadding=5 align='center' border=1>
	<tr class="linhadestaque" align='center'>
        <?
//Aki eu verifico qual é o Módulo Padrão do usuário logado
            $sql = "SELECT id_modulo 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $id_modulo_default = $campos[0]['id_modulo'];
        ?>
		<td width="45%"><b>
			<font size="2">
				M&oacute;dulos: 
				<select name='cmb_modulos' onchange='modulo()' class='combo'>
				<?
                                    $sql = "SELECT id_modulo, modulo 
                                            FROM `modulos` 
                                            ORDER BY modulo ";
                                    echo combos::combo($sql, $id_modulo);
				?>
				</select>
			</font>
			&nbsp;
			<?
/*Quando o módulo Corrente for diferente do Módulo Padrão do usuário que ele escolheu anteriormente, 
então mostra essa mensagem*/
				if(!empty($cmb_modulos)) {
                                    if($id_modulo_default != $cmb_modulos) {
					
			?>
			<input type='checkbox' name='chkt_modulo_padrao' value='1' title='Fixar Módulo Selecionado como Padrão' id='modulo_padrao' onclick='fixar_modulo()' class='checkbox'>
			<label for='modulo_padrao'>Fixar Módulo Selecionado como Padrão</label>
			<?
                                    }
				}else {
/*Quando o módulo Padrão do usuário for diferente do Módulo que carregou de início pela primeira vez na tela
então mostra essa mensagem*/
                                    if($id_modulo_default != $id_modulo) {
			?>
			<input type='checkbox' name='chkt_modulo_padrao' value='1' title='Fixar Módulo Selecionado como Padrão' id='modulo_padrao' onclick='fixar_modulo()' class='checkbox'>
			<label for='modulo_padrao'>Fixar Módulo Selecionado como Padrão</label>
			<?
                                    }
				}
			?>
		</b></td>
	</tr>
</table>
<table align='center'>
    <!--<tr>
        <td>
            <ul class="little_bubble">
                <li>
                    <a>Exemplo Bubble</a>
                    <em>Balãozinho ... </em>
                </li>
            </ul>
        </td>
    </tr>-->
</table>
</form>
</body>
</html>