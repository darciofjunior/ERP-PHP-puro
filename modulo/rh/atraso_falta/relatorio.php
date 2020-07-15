<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
session_start('fucionarios');
//Se essa Tela foi acessada pelo Próprio Menu de Relatório, então eu faço essa Segurança ...
if(empty($id_funcionario_current)) {
    require('../../../lib/menu/menu.php');
    segurancas::geral($PHP_SELF, '../../../');
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
/****************************************************/
/***********************Email************************/
/****************************************************/
    if(!empty($_POST['hdd_funcionario'])) {
//Verifico se o Chefe está de Férias ...
        $sql = "SELECT `id_funcionario_superior`, `nome`, `status` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '".$_POST['hdd_funcionario']."' limit 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['status'] == 0) {//Significa que o Chefe está de férias ...
            $nome_chefe_ferias = $campos[0]['nome'];
            $id_funcionario_receber_email = $campos[0]['id_funcionario_superior'];
            $corpo_email = '<br>O funcionário <b>'.$nome_chefe_ferias.'</b> está de férias e existem Atraso(s) / Falta(s) / Saída(s) a ser(em) resolvido(s) de seus funcionário(s).</b>';
        }else {//Significa que o Chefe está trabalhando normalmente ...
            $id_funcionario_receber_email = $_POST['hdd_funcionario'];
            $corpo_email = '<br><b>Existem Atraso(s) / Falta(s) / Saída(s) a ser(em) resolvido(s) de seus funcionário(s).</b>';
        }
//Busca do nome do Chefe ou Sub-Chefe que irá receber o e-mail ...
        $sql = "SELECT `nome`, `email_interno` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_receber_email' LIMIT 1 ";
        $campos_chefe       = bancos::sql($sql);
        $funcionario_chefe  = $campos_chefe[0]['nome'];
        $email_interno      = $campos_chefe[0]['email_interno'];
        $corpo_email.= '<br><br>Atenciosamente<br>Depto. Pessoal';
        $destino    = $email_interno;
        $mensagem   = $corpo_email;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Pendências - Atraso(s) / Falta(s) / Saída(s)', $mensagem);
    }
/****************************************************/
/***********************Estorno**********************/
/****************************************************/
/*Controle de Estorno de Atraso / Falta / Saída, estorna sempre um nível abaixo 
da Situação atual da Ocorrência ...*/
    if(!empty($_POST['hdd_funcionario_andamento'])) {
//Verifico em qual nível que está o Andamento da Ocorrência ...
        $sql = "SELECT `status_andamento` 
                FROM `funcionarios_acompanhamentos` 
                WHERE `id_funcionario_acompanhamento` = ".$_POST['hdd_funcionario_andamento']." LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['status_andamento'] == 2) {//Se estiver RH Liberado ...
            $status_andamento = 1;//Vira RH Liberar ...
            $abonar = '';
        }else {//Se estiver RH Liberar ...
            $status_andamento = 0;//Vira Chefia Liberar ...
            $abonar = ' `abonar` = "" , ';
        }
//Se a Observação estiver preenchida ...		
        if(!empty($_POST['hdd_observacao'])) $observacao = " `observacao` = CONCAT(`observacao`, '<br><b>Estorno do RH: </b>".$_POST['hdd_observacao']."'),  ";
//Estornando o Estorno de Atraso / Falta / Saída ...
        $sql = "UPDATE `funcionarios_acompanhamentos` SET $observacao `status_andamento` = '$status_andamento', $abonar `descontar_plr` = '', `hora_inicial_plr` = '0.00', `hora_final_plr` = '0.00', `atestado` = '', `hora_inicial_atestado` = '0.00', `hora_final_atestado` = '0.00', `descontar` = '', `hora_inicial_descontar` = '0.00', `hora_final_descontar` = '0.00' WHERE `id_funcionario_acompanhamento` = ".$_POST['hdd_funcionario_andamento']." LIMIT 1 ";
        bancos::sql($sql);
    }
/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_nome               = $_POST['txt_nome'];
        $id_funcionario_current = $_POST['id_funcionario_current'];
        $hdd_motivo             = $_POST['hdd_motivo'];
        $hdd_status_andamento   = $_POST['hdd_status_andamento'];
        $cmb_atestado           = $_POST['cmb_atestado'];
        $txt_data_inicial       = $_POST['txt_data_inicial'];
        $txt_data_final         = $_POST['txt_data_final'];
        $cmb_chefe              = $_POST['cmb_chefe'];
        $cmb_motivo             = $_POST['cmb_motivo'];
        $cmb_status_andamento   = $_POST['cmb_status_andamento'];
        $chkt_sem_cracha        = $_POST['chkt_sem_cracha'];
    }else {
        $txt_nome               = $_GET['txt_nome'];
        $id_funcionario_current = $_GET['id_funcionario_current'];
        $hdd_motivo             = $_GET['hdd_motivo'];
        $hdd_status_andamento   = $_GET['hdd_status_andamento'];
        $cmb_atestado           = $_GET['cmb_atestado'];
        $txt_data_inicial       = $_GET['txt_data_inicial'];
        $txt_data_final         = $_GET['txt_data_final'];
        $cmb_chefe              = $_GET['cmb_chefe'];
        $cmb_motivo             = $_GET['cmb_motivo'];
        $cmb_status_andamento   = $_GET['cmb_status_andamento'];
        $chkt_sem_cracha        = $_GET['chkt_sem_cracha'];
    }
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
    if($hdd_motivo == 1) {//Entrada
        $cmb_motivo = 0;
    }else if($hdd_motivo == 2) {//Saída
        $cmb_motivo = 1;
    }else if($hdd_motivo == 3) {//Falta
        $cmb_motivo = 2;
    }else {//Independente do Motivo
        if($cmb_motivo == '') { $cmb_motivo = "%";}
    }
//Segunda adaptação
    if($hdd_status_andamento == 1) {//Status Andamento = Chefia Liberar
        $cmb_status_andamento = 0;
    }else if($hdd_status_andamento == 2) {//Status Andamento = Chefia Liberar
        $cmb_status_andamento = 1;
    }else if($hdd_status_andamento == 3) {//Status Andamento = Chefia Liberar
        $cmb_status_andamento = 2;
    }else {//Independente do Status Andamento ...
        if($cmb_status_andamento == '') $cmb_status_andamento = '%';
    }
    if(!empty($cmb_atestado)) $condicao_atestado = " AND fa.`atestado` = '$cmb_atestado' ";

    if($cmb_chefe == '') $cmb_chefe = '%';
//Busca as Ocorrências de um Funcionário em específico ...
    if(!empty($id_funcionario_current)) {
        $sql = "SELECT id_funcionario_superior 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_current' ";
        $campos     = bancos::sql($sql);
        $cmb_chefe  = $campos[0]['id_funcionario_superior'];

        $condicao_funcionario   = " AND fa.`id_funcionario_acompanhado` = '$id_funcionario_current' ";
        $class_combo            = 'textdisabled';
        $disabled_combo         = 'disabled';
/*Somente a pessoa encarregada no Depto. Pessoal que pode estar mandando E-mail ou 
estornando os dados do funcionário ...*/
        $executar_funcao = 0;
//Significa que estou acessando essa Tela pelo Menu, sendo assim, posso ver mais de 1 funcionário ...
    }else {
        //Verifico o departamento "$_SESSION[id_funcionario]" ...
        $sql = "SELECT id_departamento 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_depto = bancos::sql($sql);
        if($campos_depto[0]['id_departamento'] == 21 || $campos_depto[0]['id_departamento'] == 24) {//Se o Depto. = 'Portaria' ou 'Recursos Humanos' ...
            //Com certeza tem que ter acesso a todas as opções desse relatório ...
            $class_combo        = 'combo';
            $disabled_combo     = '';
            $executar_funcao    = 1;
        }else {//Não é do RH ...
            //Verifico se o usuário logado é chefe ...
            $sql = "SELECT f.id_funcionario_superior 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario_superior` = '$_SESSION[id_funcionario]' ORDER BY f.id_funcionario_superior LIMIT 1 ";
            $campos_depto = bancos::sql($sql);
            if(count($campos_depto) == 1) {//Significa que o funcionário logado é chefe e tem login ...
                //Roberto 62, Sandra 66, Wilson 68 e Dárcio 98 ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98) {
                    //A diretoria pode ter acesso a todas as opções da pessoa responsável pelo RH ...
                    $class_combo        = 'combo';
                    $disabled_combo     = '';
                    $executar_funcao    = 1;
                }else {//Se for um outro chefe normal ...
                    $cmb_chefe          = $_SESSION['id_funcionario'];

                    $class_combo        = 'textdisabled';
                    $disabled_combo     = 'disabled';
                    //Somente a pessoa encarregada no Depto. Pessoal que pode estar mandando E-mail ou estornando os dados do funcionário ...
                    $executar_funcao = 0;
                }
            }else {//Não é chefe ...
                //Se o funcionário for Dárcio 98, então é o único que pode ter todas as opções porque programa ... 
                if($_SESSION['id_funcionario'] == 98) {
                    $class_combo        = 'combo';
                    $disabled_combo     = '';
                    $executar_funcao    = 1;
                }
            }
        }
    }
//Busca das Pendências no Período especificado ...
    if(!empty($txt_data_inicial)) {
        $txt_data_inicial_usa = data::datatodate($txt_data_inicial, '-');
        $txt_data_final_usa = data::datatodate($txt_data_final, '-');
        $condicao = " AND SUBSTRING(fa.`data_ocorrencia`, 1, 10) BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' "; 
    }
//Controle com o Checkbox ...
    if(!empty($chkt_sem_cracha)) $condicao_sem_cracha = " AND fa.`sem_cracha` = 'S' ";

    $sql = "SELECT c.cargo, e.id_empresa, e.nomefantasia, f.id_funcionario, f.id_funcionario_superior, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, fa.* 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
            INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`motivo` LIKE '$cmb_motivo' AND fa.`status_andamento` LIKE '$cmb_status_andamento' $condicao_atestado $condicao $condicao_funcionario $condicao_sem_cracha 
            WHERE f.`id_funcionario_superior` LIKE '$cmb_chefe' 
            AND f.`nome` LIKE '%$txt_nome%' ORDER BY e.id_empresa, f.nome, fa.data_ocorrencia DESC ";
    $campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
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

function estornar_atraso_falta_saida(id_funcionario_acompanhamento, status_andamento) {
	if(status_andamento == 1) {//Quando o Registro está no Estágio de RH Liberar ...
		var resposta = confirm('ESSE REGISTRO VOLTARÁ P/ O CHEFE DESSE FUNCIONÁRIO !!!\n\nTEM CERTEZA DE QUE DESEJA ESTORNAR ESSE REGISTRO ?')
		if(resposta == true) {
			var observacao = prompt('DIGITE UMA OBSERVAÇÃO P/ ESTORNAR ESSE REGISTRO P/ O CHEFE: ')
			document.form.hdd_observacao.value = observacao
//Controle com a Observação ...
			if(document.form.hdd_observacao.value == '' || document.form.hdd_observacao.value == 'null' || document.form.hdd_observacao.value == 'undefined') {
				alert('OBSERVAÇÃO INVÁLIDA !!!\n\nDIGITE UMA OBSERVAÇÃO P/ ESTORNAR ESSE REGISTRO P/ O CHEFE !')
				return false
			}
			document.form.hdd_funcionario_andamento.value = id_funcionario_acompanhamento
			document.form.submit()
		}else {
			return false
		}
	}else if(status_andamento == 2) {//Quando o Registro está no Estágio de RH Liberado ...
		var resposta = confirm('TEM CERTEZA DE QUE DESEJA ESTORNAR ESSE REGISTRO ?')
		if(resposta == true) {
			document.form.hdd_funcionario_andamento.value = id_funcionario_acompanhamento
			document.form.submit()
		}else {
			return false
		}
	}
}

function enviar_email_chefe(id_funcionario_superior) {
	var resposta = confirm('TEM CERTEZA DE DESEJA ENVIAR UM E-MAIL P/ ESTE CHEFE ?')
	if(resposta == true) {
		document.form.hdd_funcionario.value = id_funcionario_superior
		document.form.submit()
	}else {
		return false
	}
}

//Controle com as combos ...
function controle_combos() {
	var motivo = document.form.cmb_motivo[document.form.cmb_motivo.selectedIndex].text
//Se não estiver selecionado nenhum Motivo ...
	if(motivo == 'SELECIONE') {
		document.form.hdd_motivo.value = ''
	}else if(motivo == 'Entrada') {
		document.form.hdd_motivo.value = 1
	}else if(motivo == 'Saída') {
		document.form.hdd_motivo.value = 2
	}else if(motivo == 'Falta') {
		document.form.hdd_motivo.value = 3
	}

	var status_andamento = document.form.cmb_status_andamento[document.form.cmb_status_andamento.selectedIndex].text
//Se não estiver selecionado nenhum Status ...
	if(status_andamento == 'SELECIONE') {
		document.form.hdd_status_andamento.value = ''
	}else if(status_andamento == 'Chefia Liberar') {
		document.form.hdd_status_andamento.value = 1
	}else if(status_andamento == 'Rh Liberar') {
		document.form.hdd_status_andamento.value = 2
	}else if(status_andamento == 'Rh Liberado') {
		document.form.hdd_status_andamento.value = 3
	}
}

function alterar_registro(id_funcionario_acompanhamento) {
/*Esse parâmetro $veio_tela_relatorio é p/ saber que essa Tela do Alterar foi acessada de Dentro do Relatório 
de Atraso / Falta / Saída do Funcionário e vai me auxiliar p/ fazer uns controles diferenciais nessa Tela ...*/
	nova_janela('alterar.php?passo=2&veio_tela_relatorio=1&id_funcionario_acompanhamento='+id_funcionario_acompanhamento, 'CONSULTAR', '', '', '', '', '420', '980', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body onload="controle_combos();document.form.txt_data_inicial.focus()">
<form name="form" method="post" action="<?=$PHP_SELF;?>" onsubmit="return validar()">
<!--***************************Controle(s) de Tela***************************-->
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='hdd_funcionario'>
<input type='hidden' name='hdd_funcionario_andamento'>
<input type='hidden' name='txt_nome' value='<?=$txt_nome;?>'>
<!--Variável que vem por parâmetro de outras Telas que acessam esse Relatório-->
<input type='hidden' name='id_funcionario_current' value="<?=$id_funcionario_current;?>">
<input type='hidden' name='hdd_observacao'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro lá no outro
passo da consulta*/
-->
<input type="hidden" name="hdd_motivo">
<input type="hidden" name="hdd_status_andamento">
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Relatório de Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            <b>Data Inicial:</b>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" maxlength="10" size="12" class="caixadetexto">
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            at&eacute;<b> </b>
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" maxlength="10" size="12" class="caixadetexto">
            <img src="../../../imagem/calendario.gif" width="12" height="12"  border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <b>Data Final</b>
            &nbsp;-&nbsp;
            <b>Chefe: </b>
            <select name="cmb_chefe" title="Selecione o Chefe" class="<?=$class_combo;?>" <?=$disabled_combo;?>>
            <?
//Listagem de todos os Funcionários que são Chefes na Empresa ...
                    $sql = "SELECT DISTINCT(id_funcionario_superior) as id_funcionario_superior 
                            FROM `funcionarios` 
                            WHERE `id_funcionario_superior` <> '0' ";
                    $campos_chefes = bancos::sql($sql);
                    $linhas_chefes = count($campos_chefes);
                    for($i = 0; $i < $linhas_chefes; $i++) $id_funcs_chefe.= $campos_chefes[$i]['id_funcionario_superior'].', ';
//Significa que não carregou essa variável no Loop ...
                    if(strlen($id_funcs_chefe) == 0) {
                        $id_funcs_chefe = 0;
                    }else {
                        $id_funcs_chefe = substr($id_funcs_chefe, 0, strlen($id_funcs_chefe) - 2);
                    }
//Busca do nome dos Chefes ...
                    $sql = "SELECT id_funcionario, nome 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` IN ($id_funcs_chefe) ORDER BY nome ";
                    echo combos::combo($sql, $cmb_chefe);
            ?>
            </select>
            <br/>
            <b>Motivo: </b>
            <?
//Macete p/ não dar erro na combo ...
                if($hdd_motivo == 1) {//Entrada
                    $motivo0 = 'selected';
                }else if($hdd_motivo == 2) {//Saída
                    $motivo1 = 'selected';
                }else if($hdd_motivo == 3) {//Falta
                    $motivo2 = 'selected';
                }else {//Independente do Motivo
                    $motivo = 'selected';
                }
            ?>
            <select name="cmb_motivo" title="Motivo" onchange="controle_combos()" class='combo'>
                <option value='' style="color:red" <?=$motivo;?>>SELECIONE</option>
                <option value='0' <?=$motivo0;?>>Entrada</option>
                <option value='1' <?=$motivo1;?>>Saída</option>
                <option value='2' <?=$motivo2;?>>Falta</option>
            </select>
            &nbsp;-&nbsp;
            <b>Status: </b>
            <?
//Macete p/ não dar erro na combo ...			
                if($hdd_status_andamento == 1) {//Status Andamento = Chefia Liberar
                    $status_andamento0 = 'selected';
                }else if($hdd_status_andamento == 2) {//Status Andamento = Rh Liberar
                    $status_andamento1 = 'selected';
                }else if($hdd_status_andamento == 3) {//Status Andamento = Rh Liberado
                    $status_andamento2 = 'selected';
                }else {//Independente do Status Andamento ...
                    $status_andamento = 'selected';
                }
            ?>
            <select name="cmb_status_andamento" title="Status Andamento" onchange="controle_combos()" class='combo'>
                <option value="" style="color:red" <?=$status_andamento;?>>SELECIONE</option>
                <option value="0" <?=$status_andamento0;?>>Chefia Liberar</option>
                <option value="1" <?=$status_andamento1;?>>Rh Liberar</option>
                <option value="2" <?=$status_andamento2;?>>Rh Liberado</option>
            </select>
            &nbsp;-&nbsp;
            <b>Atestado: </b>
            <?
                if($cmb_atestado == '') {//Selecione ...
                    $selected = 'selected';
                }else if($cmb_atestado == 'S') {//Sim ...
                    $selecteds = 'selected';
                }else if($cmb_atestado == 'N') {//Não ...
                    $selectedn = 'selected';
                }
            ?>
            <select name='cmb_atestado' title='Atestado' class='combo'>
                <option value='' style='color:red' <?=$selected;?>>SELECIONE</option>
                <option value='S' <?=$selecteds;?>>SIM</option>
                <option value='N' <?=$selectedn;?>>NÃO</option>
            </select>
            &nbsp;-
            <?
                //Controle com o Checkbox ...
                if(!empty($chkt_sem_cracha)) $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_sem_cracha' value='S' title='Sem Crachá' id="sem_cracha" class='checkbox' <?=$checked;?>>
            <label for="sem_cracha">
                Sem Crachá
            </label>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
<?
        if($linhas == 0) {//Não encontrou nenhum registro ...
?>
    <tr>
        <td></td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='8'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='8'>
        <?
//Se existir esse parâmetro, então eu não exibo esse botão de Voltar ...
            if(empty($id_funcionario_current)) {
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio.php'" class='botao'>
        <?
            }else {
                echo '&nbsp';
            }
        ?>
        </td>
    </tr>
<?
            }else {//Encontrou mais de 1 ...
//Criei esse array p/ facilitar na Visualização mais abaixo ...
			$motivos = array('Entrada', 'Saída', 'Falta');
			$status_andamento = array('<font color="red">Chefia Liberar</font>', '<font color="darkblue">RH Liberar</font>', '<font color="darkgreen">RH Liberado</font>');
//Essa variável será utilizada mais abaixo ...
			$id_empresa_anterior = '';
			$id_funcionario_anterior = '';
			$id_funcionario_superior_anterior = '';
			for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listado
no loop, se for então eu atribuo o Empresa Atual p/ o Empresa Anterior ...*/
/**************************Empresa**************************/
				if($id_empresa_anterior != $campos[$i]['id_empresa']) {
					$id_empresa_anterior = $campos[$i]['id_empresa'];
?>
	<tr class='linhacabecalho'>
		<td colspan='8'>
			<font color='yellow'>
				<b>Empresa: </b>
			</font>
			<?=$campos[$i]['nomefantasia'];?>
		</td>
	</tr>
<?
				}
/*Aqui eu verifico se o Funcionário Anterior é Diferente do Funcionário Atual que está 
sendo listado no loop, se for então eu atribuo o Funcionário Atual p/ o Funcionário 
Anterior ...*/
/***********************************************************/
/**************************Funcionário**************************/
				if($id_funcionario_anterior != $campos[$i]['id_funcionario']) {
					$id_funcionario_anterior = $campos[$i]['id_funcionario'];
?>
    <tr class="linhanormaldestaque">
        <td colspan="3">
                <b>Funcionário: </b><?=$campos[$i]['nome'];?>
        </td>
        <td colspan="2">
                <b>Cargo: </b><?=$campos[$i]['cargo'];?>
        </td>
        <td colspan="3">
            <b>Chefe: </b>
            <?	
//Busca do Nome e Status do Chefe do Funcionário ...
                $sql = "SELECT nome, status 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
                $campos_chefe   = bancos::sql($sql);
                echo $campos_chefe[0]['nome'];
                if($campos_chefe[0]['status'] == 0) echo '<font color="red"><b> (Férias)</b></font>';
//Verifica se o Superior tem login no ERP ...
                $sql = "SELECT l.id_login 
                        FROM `funcionarios` f 
                        INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                        WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                if(count($campos_login) == 1) {
/*Verifico se o Funcionário Corrente possui pelo menos 1 Pendência que está no Estágio 
de 'Chefia Liberar' p/ poder exibir a Figura de Enviar E-mail p/ o Chefe ...*/
                    $sql = "SELECT id_funcionario_acompanhamento 
                            FROM `funcionarios_acompanhamentos` 
                            WHERE `id_funcionario_acompanhado` = '".$campos[$i]['id_funcionario']."' 
                            AND `registro_portaria` = 'S' 
                            AND `status_andamento` = '0' LIMIT 1 ";
                    $campos_chefia_liberar = bancos::sql($sql);
                    if(count($campos_chefia_liberar) == 1 && $executar_funcao == 1) {
        ?>
            - <img src="../../../imagem/novo_email.jpeg" width="27" height="17" border="0" title="Enviar E-mail p/ Chefe" onclick="enviar_email_chefe('<?=$campos[$i]['id_funcionario_superior'];?>')" style="cursor:pointer">
        <?
                    }
                }
        ?>
        </td>
    </tr>
    <tr class='linhanormaldestaque' align='center'>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Data</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Hora</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Motivo</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Abonar</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Descontar PLR</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Atestado</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Descontar</b>
            </font>
        </td>
        <td bgcolor='#D9D9D9'>
            <font color='#000000'>
                <b>Status</b>
            </font>
        </td>
    </tr>
<?
				}
/***************************************************************/
/**************************Rotina Normal**************************/
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
<?
/*Se o Andamento já passou pela Fase de Chefia Liberar e está na parte de RH Liberar 
ou RH Liberado eu habilito essa ferrametinha p/ q a pessoa do DP pessoal possa estornar 
essa ocorrência ...*/
				if($campos[$i]['status_andamento'] > 0 && $executar_funcao == 1) {
?>
		<td onclick="javascript:estornar_atraso_falta_saida(<?=$campos[$i]['id_funcionario_acompanhamento'];?>, <?=$campos[$i]['status_andamento'];?>)">
			<a href="#" title="Estornar Atraso / Falta / Saída" style="cursor:help" class="link">
				<IMG src="../../../imagem/estornar.jpeg" title="Estornar Atraso / Falta / Saída" alt="Estornar Atraso / Falta / Saída" style="cursor:help" border="0">
				&nbsp;<?=data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/');?>
			</a>
		</td>
<?
				}else {//Ainda está no Estágio de Chefia Liberar ...
?>
		<td>
			<?=data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/');?>
		</td>
<?
				}
?>
		<td>
		<?
			if($campos[$i]['motivo'] == 2) {//Falta
				echo '-';
			}else {//Em alguma outra situação ...
				echo substr($campos[$i]['data_ocorrencia'], 11, 8);
			}
		?>
		</td>
		<td>
			<?
				echo '<font color="darkblue"><b>'.$motivos[$campos[$i]['motivo']].'</b></font>';
				if($campos[$i]['sem_cracha'] == 'S') {
					echo '<font color="red"><b> (Sem Crachá)</b></font>';
				}
			?>
		</td>
		<td>
		<?
			if($campos[$i]['abonar'] == 'S') {
				echo '<font color="darkblue"><b>SIM</b></font>';
			}else if($campos[$i]['abonar'] == 'N') {
				echo '<font color="red"><b>NÃO</b></font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td>
		<?
			if($campos[$i]['descontar_plr'] == 'S') {
				$horario_descontar_plr = '<font color="red"><b> ('.str_replace('.', ':', $campos[$i]['hora_inicial_plr']).' às '.str_replace('.', ':', $campos[$i]['hora_final_plr']).')';
				echo '<font color="darkblue"><b>SIM</b></font>'.$horario_descontar_plr;
			}else if($campos[$i]['descontar_plr'] == 'N') {
				echo '<font color="red"><b>NÃO</b></font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td>
		<?
			if($campos[$i]['atestado'] == 'S') {
				$horario_atestado = '<font color="red"><b> ('.str_replace('.', ':', $campos[$i]['hora_inicial_atestado']).' às '.str_replace('.', ':', $campos[$i]['hora_final_atestado']).')';
				echo '<font color="darkblue"><b>SIM</b></font>'.$horario_atestado;
			}else if($campos[$i]['atestado'] == 'N') {
				echo '<font color="red"><b>NÃO</b></font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td>
		<?
			if($campos[$i]['descontar'] == 'S') {
				$horario_descontar = '<font color="red"><b> ('.str_replace('.', ':', $campos[$i]['hora_inicial_descontar']).' às '.str_replace('.', ':', $campos[$i]['hora_final_descontar']).')';
				echo '<font color="darkblue"><b>SIM</b></font>'.$horario_descontar;
			}else if($campos[$i]['descontar'] == 'N') {
				echo '<font color="red"><b>NÃO</b></font>';
			}else {
				echo '-';
			}
		?>
		</td>
		<td>
			<b><?=$status_andamento[$campos[$i]['status_andamento']];?></b>
			<?
//Só posso mostrar esse ícone p/ o Luís, Roberto e Graziella ...
				if($_SESSION['id_login'] == 16 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 77) {
//Somente nos Estágios de RH Liberar ou RH Liberado ...
					if($campos[$i]['status_andamento'] == 1 || $campos[$i]['status_andamento'] == 2) {
			?>
			<img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Registro" alt="Alterar Registro" onClick="alterar_registro(<?=$campos[$i]['id_funcionario_acompanhamento'];?>)">
			<?
					}
				}
			?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<font color="darkblue">
				<b>Observação Geral: </b>
			</font>
		</td>
		<td colspan='7'>
			<?=$campos[$i]['observacao'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='8' bgcolor='#000000'>
		</td>
	</tr>
<?
		}
/*****************************************************************/
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='8'>
		<?
//Se existir esse parâmetro, então eu não exibo esse botão de Voltar ...
				if(empty($id_funcionario_current)) {
		?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio.php'" class='botao'>
			<input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' class='botao'>
		<?
				}else {
		?>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
		<?
				}
		?>
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
<?
            }
?>
</form>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório de Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onLoad="document.form.txt_nome.focus()">
<form name="form" method="post" action=''>
<input type='hidden' name='passo' value='1'>
<table border="0" width="60%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Relatório de Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type="text" name="txt_nome" title="Digite o Nome" size="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_nome.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>