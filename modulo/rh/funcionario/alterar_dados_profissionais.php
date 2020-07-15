<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/dp.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

$mensagem[1] = "FUNCION�RIO ALTERADO COM SUCESSO !";

/***********************************Fun��o***********************************/
//Essa fun��o verifica se o Funcion�rio est� atrelado a alguma M�quina ...
function maquinas_atreladas($id_funcionario_current) {
    //Fazendo verifica��o pois isso pode acarretar no Custo ...
    $sql = "SELECT m.`nome` as maquina 
            FROM `maquinas_vs_funcionarios` mf 
            INNER JOIN `maquinas` m ON m.`id_maquina` = mf.`id_maquina` 
            WHERE mf.`id_funcionario` = '$id_funcionario_current' ";
    $campos_maquina = bancos::sql($sql);
    $linhas_maquina = count($campos_maquina);
    if($linhas_maquina > 0) {//Encontrou pelo menos 1 m�quina
        $maquinas = '';//Limpa a vari�vel p/ n�o continuar com valores do loop anterior ...
        for($i = 0; $i < $linhas_maquina; $i++) $maquinas.= '* '.$campos_maquina[$i]['maquina'].', ';
        $maquinas = substr($maquinas, 0, strlen($maquinas) - 2);
//Verifico o nome do Funcion�rio que est� atrelado ...
        $sql = "SELECT `nome` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_current' LIMIT 1 ";
        $campos_funcionario = bancos::sql($sql);
//Aqui eu monto a Mensagem que irei exibir para a Pessoa respons�vel do RH ...
        $maquinas_atreladas = 'O FUNCION�RIO '.strtoupper($campos_funcionario[0]['nome']).' EST� ATRELADO A(S) SEGUINTE(S) M�QUINA(s): \n\n'.$maquinas.'\n\n';
    }
    return $maquinas_atreladas;
}
/****************************************************************************/

if($passo == 1) {
//Data e Hora de Atualiza��o dos Dados do Funcion�rio ...
    $data_sys                       = date('Y-m-d H:i:s');
//Tratamento com os campos de Data p/ poder gravar no Banco de Dados ...
    $data_demissao                  = data::datatodate($_POST['txt_data_demissao'], '-');
    $data_admissao                  = data::datatodate($_POST['txt_data_admissao'], '-');
    $ultimas_ferias_data_inicial    = data::datatodate($_POST['txt_ultimas_ferias_data_inicial'], '-');
    $ultimas_ferias_data_final      = data::datatodate($_POST['txt_ultimas_ferias_data_final'], '-');
    $periodo_anual_data_inicial     = data::datatodate($_POST['txt_periodo_anual_data_inicial'], '-');
    $periodo_anual_data_final       = data::datatodate($_POST['txt_periodo_anual_data_final'], '-');
    $data_prox_ferias               = data::datatodate($_POST['txt_data_prox_ferias'], '-');
    $data_max_ferias                = data::datatodate($_POST['txt_data_max_ferias'], '-');
//Controle referente ao campo Programa��o de F�rias ...
    $programacao_ferias             = $_POST['cmb_ano'].'-'.$_POST['cmb_mes'].'-00';
/*********************************Controle com os Checkbox*********************************/
    $debitar_convenio_medico        = (empty($_POST['chkt_debitar_convenio_medico'])) ? 'N' : $_POST['chkt_debitar_convenio_medico'];
    $debitar_acidente_trabalho      = (empty($_POST['chkt_debitar_acidente_trabalho'])) ? 'N' : $_POST['chkt_debitar_acidente_trabalho'];
    $debitar_convenio_odonto        = (empty($_POST['chkt_debitar_convenio_odonto'])) ? 'N' : $_POST['chkt_debitar_convenio_odonto'];
    $debitar_combustivel            = (empty($_POST['chkt_debitar_combustivel'])) ? 'N' : $_POST['chkt_debitar_combustivel'];
    $reembolso_combustivel          = (empty($_POST['chkt_reembolso_combustivel'])) ? 'N' : $_POST['chkt_reembolso_combustivel'];
    $debitar_celular                = (empty($_POST['chkt_debitar_celular'])) ? 'N' : $_POST['chkt_debitar_celular'];
    $retirar_vale_dia20             = (empty($_POST['chkt_retirar_vale_dia20'])) ? 'N' : $_POST['chkt_retirar_vale_dia20'];
    $debitar_mensal_sindical        = (empty($_POST['chkt_debitar_mensal_sindical'])) ? 'N' : $_POST['chkt_debitar_mensal_sindical'];
    $debitar_contrib_confederativa  = (empty($_POST['chkt_debitar_contrib_confederativa'])) ? 'N' : $_POST['chkt_debitar_contrib_confederativa'];
    $retira_vale_transporte         = (empty($_POST['chkt_retira_vale_transporte'])) ? 'N' : $_POST['chkt_retira_vale_transporte'];
    $debitar_contrib_assistencial   = (empty($_POST['chkt_debitar_contrib_assistencial'])) ? 'N' : $_POST['chkt_debitar_contrib_assistencial'];
    $conducao_propria               = (empty($_POST['chkt_conducao_propria'])) ? 'N' : $_POST['chkt_conducao_propria'];
    $sindicalizado                  = (empty($_POST['chkt_sindicalizado'])) ? 'N' : $_POST['chkt_sindicalizado'];
    $tem_direito_plr                = (empty($_POST['chkt_tem_direito_plr'])) ? 'N' : $_POST['chkt_tem_direito_plr'];
    $mensalidade_metalcred          = (empty($_POST['chkt_mensalidade_metalcred'])) ? 'N' : $_POST['chkt_mensalidade_metalcred'];
/******************************************************************************************/
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
    $cmb_superior                   = (!empty($_POST[cmb_superior])) ? "'".$_POST[cmb_superior]."'" : 'NULL';
    
//Atualizando os dados na Tabela de Funcion�rios ...
    $sql = "UPDATE `funcionarios` SET `id_funcionario_superior` = $cmb_superior, `id_empresa` = '$_POST[cmb_empresa]', `id_cargo` = '$_POST[cmb_cargo]', `id_departamento` = '$_POST[cmb_departamento]', `codigo_barra` = '$_POST[txt_codigo]', `data_admissao` = '$data_admissao', `data_demissao` = '$data_demissao', `ultimas_ferias_data_inicial` = '$ultimas_ferias_data_inicial', `ultimas_ferias_data_final` = '$ultimas_ferias_data_final', `periodo_anual_data_inicial` = '$periodo_anual_data_inicial', `periodo_anual_data_final` = '$periodo_anual_data_final', `data_prox_ferias` = '$data_prox_ferias', `data_max_ferias` = '$data_max_ferias', `programacao_ferias` = '$programacao_ferias', `tipo_salario` = '$_POST[cmb_tipo_salario]', `status` = '$_POST[cmb_status]', `data_registro` = '$data_sys', `pensao_alimenticia` = '".$_POST['txt_pensao_alimenticia']."', `valor_pensao_alimenticia` = '$_POST[txt_valor_pensao_alimenticia]', `debitar_conv_medico` = '$debitar_convenio_medico', `dependentes_conv_medico` = '$_POST[txt_qtde_dependentes]', `debitar_acidente_trabalho` = '$debitar_acidente_trabalho', `debitar_conv_odonto` = '$debitar_convenio_odonto', `qtde_plano_odonto` = '$_POST[txt_qtde_planos]', `debitar_combustivel` = '$debitar_combustivel', `qtde_litros_combustivel` = '$_POST[txt_qtde_litros]', `reembolso_combustivel` = '$reembolso_combustivel', `debitar_celular` = '$debitar_celular', `retirar_vale_dia_20` = '$retirar_vale_dia20', `perc_vale_pd` = '$_POST[txt_perc_vale_pd]', `debitar_mensal_sindical` = '$debitar_mensal_sindical', `debitar_contrib_federativa` = '$debitar_contrib_confederativa', `debitar_contrib_assistencial` = '$debitar_contrib_assistencial', `retira_vale_transporte` = '$retira_vale_transporte', `conducao_propria` = '$conducao_propria', `sindicalizado` = '$sindicalizado', `tem_direito_plr` = '$tem_direito_plr', `cheque_dinheiro` = '$_POST[cmb_forma_pagamento]', `cod_banco` = '$_POST[cmb_cod_banco]', `agencia` = '$_POST[txt_agencia]', `conta_corrente` = '$_POST[txt_conta_corrente]', `mensalidade_metalcred` = '$mensalidade_metalcred', `valor_metalcred` = '$_POST[txt_valor_metalcred]', `status_superior` = '$chkt_funcionario_superior' WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
    bancos::sql($sql);
//Se o funcion�rio foi demitido, ent�o eu verifico se o mesmo � representante p/ que este seja excluido do sistema ...
    if($_POST['cmb_status'] == 3) {
        $sql = "SELECT id_representante 
                FROM `representantes_vs_funcionarios` 
                WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        $campos_rep = bancos::sql($sql);
        if(count($campos_rep) == 1) {//Significa que o mesmo � representante ...
            $id_representante = $campos_rep[0]['id_representante'];
//Exclui o Representante ...
            $sql = "UPDATE `representantes` SET `ativo` = '0' WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            bancos::sql($sql);
//Deleta o Representante da Tab. Relacional repr_vs_funcs
            $sql = "DELETE FROM `representantes_vs_funcionarios` WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
//Significa que deseja desatrelar o Func das M�quinas ...
    if($_POST['hdd_desatrelar_maquinas'] == 1) {
        $maquinas_atreladas = maquinas_atreladas($_POST['id_funcionario_loop']);
//Significa que o Funcion�rio estava atrelado a pelo menos 1 m�quina ...
//Localiza as M�quinas na qual o Funcion�rio trabalhava ...
        custos::localizar_maquina($_POST['id_funcionario_loop']);//obedecer a ordem primeiro vejo o atrelamento para recalcular, depois apago ele
//Exclui o Funcion�rio de todas as m�quinas no qual estava atrelado ...
        $sql = "DELETE FROM `maquinas_vs_funcionarios` WHERE `id_funcionario` = '".$_POST['id_funcionario_loop']."' ";
        bancos::sql($sql);
        $mensagem_email = str_replace('\n', '<br>', $maquinas_atreladas);
        $mensagem_email.= 'A partir de agora, o mesmo j� foi desatrelado automaticamente dessa(s) m�quina(s). <br>Sendo asism, n�o se esque�a de fazer verifica��es referentes ao Custo.<br><br>Atenciosamente';
/***********************************Email***********************************/
/*Nessa parte aqui o sistema dispara um e-mail autom�tico para o Roberto, 
informando que foi desatrelado um funcion�rio ou mais funcion�rios de uma das M�quinas 
para que eles tomem as provid�ncias necess�rias com rela��o ao Custo ...*/
        $destino = $excluir_funcionario;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Funcion�rio Demitido', $mensagem_email);
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[1];?>')
/*Pop_up = 1, significa que est� tela foi aberta como sendo pop_up e sendo assim eu n�o exibo o bot�o de 
Voltar que existe nessa tela mais abaixo*/
        window.location = 'alterar_dados_profissionais.php?id_funcionario_loop=<?=$_POST['id_funcionario_loop'];?>&pop_up=<?=$_POST['pop_up'];?>'
    </Script>
<?
}else {
    /*****************************Exclus�o dos Item(ns) de Vale Transporte*****************************/
    if(!empty($id_funcionario_vale_transporte)) {
        $sql = "DELETE FROM `funcionarios_vs_vales_transportes` WHERE `id_funcionario_vale_transporte` = '$id_funcionario_vale_transporte' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('VALE TRANSPORTE EXCLU�DO COM SUCESSO !')
    </Script>
<?
    }
    
    /*************************************Exclus�o dos Dependentes*************************************/
    if(!empty($id_dependente)) {
        $sql = "DELETE FROM `dependentes` WHERE `id_dependente` = '$id_dependente' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('DEPENDENTE EXCLU�DO COM SUCESSO !')
    </Script>
<?
    }
    
    $id_funcionario_loop    = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_loop'] : $_GET['id_funcionario_loop'];
/**************************************************************************************************/
//Verifico se o Funcion�rio corrente est� atrelado em alguma M�quina ...
    $maquinas_atreladas     = maquinas_atreladas($id_funcionario_loop);
/**************************************************************************************************/
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
    $sql = "SELECT * 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_loop' limit 1 ";
    $campos                     = bancos::sql($sql);
    $id_funcionario_superior    = $campos[0]['id_funcionario_superior'];
    $codigo_barra               = $campos[0]['codigo_barra'];
    $nome                       = $campos[0]['nome'];
//Aqui eu renomeio essa vari�vel p/ $id_empresa_func, porque j� uma $id_empresa dentro da sess�o do Sistema
    $id_empresa_func            = $campos[0]['id_empresa'];
    $email_interno              = $campos[0]['email_interno'];
    $email_externo              = $campos[0]['email_externo'];
    $id_departamento            = $campos[0]['id_departamento'];
    $id_cargo                   = $campos[0]['id_cargo'];
    $status_superior            = $campos[0]['status_superior'];
    $tipo_salario               = $campos[0]['tipo_salario'];

    $salario_pd                 = number_format($campos[0]['salario_pd'], 2, ',', '.');
    $salario_pf                 = number_format($campos[0]['salario_pf'], 2, ',', '.');
    $salario_premio             = number_format($campos[0]['salario_premio'], 2, ',', '.');
    $garantia_salarial          = number_format($campos[0]['garantia_salarial'], 2, ',', '.');

    $data_admissao              = $campos[0]['data_admissao'];
//Valida��o da Data de Admiss�o
    if($data_admissao == '0000-00-00') {
        $data_admissao      = '';
    }else {
        $data_admissao      = data::datetodata($data_admissao, '/');
    }
    $data_demissao = $campos[0]['data_demissao'];
//Valida��o da Data de Demiss�o
    if($data_demissao == '0000-00-00') {
        $data_demissao = '';
    }else {
        $data_demissao = data::datetodata($data_demissao, '/');
    }
    
    $ultimas_ferias_data_inicial    = data::datetodata($campos[0]['ultimas_ferias_data_inicial'], '/');
    $ultimas_ferias_data_final      = data::datetodata($campos[0]['ultimas_ferias_data_final'], '/');
    $periodo_anual_data_inicial     = data::datetodata($campos[0]['periodo_anual_data_inicial'], '/');
    $periodo_anual_data_final       = data::datetodata($campos[0]['periodo_anual_data_final'], '/');
    $pensao_alimenticia             = $campos[0]['pensao_alimenticia'];
    $valor_pensao_alimenticia       = ($campos[0]['valor_pensao_alimenticia'] == '0.00') ? '' : number_format($campos[0]['valor_pensao_alimenticia'], 2, ',', '.');
    $dependentes_conv_medico        = $campos[0]['dependentes_conv_medico'];
    $qtde_plano_odonto              = $campos[0]['qtde_plano_odonto'];
    $cod_banco                      = $campos[0]['cod_banco'];
    $status                         = $campos[0]['status'];

//Aqui eu verifico todos os Funcion�rios ativos "ainda trabalham na Empresa" que s�o Subordinados ao Funcion�rio Corrente ...
    $sql = "SELECT `nome` 
            FROM `funcionarios` 
            WHERE `id_funcionario_superior` = '$id_funcionario_loop' 
            AND `status` < '3' ";
    $campos_subordinados    = bancos::sql($sql);
    $total_subordinados     = count($campos_subordinados);
/*Aqui eu carrego o nome dos Funcion�rios que s�o subordinados ao Funcion�rio Corrente, p/ mostrar
em Alert caso o Usu�rio tente desmarcar o Funcion�rio Corrente de ser Superior ...*/
    for($i = 0; $i < $total_subordinados; $i++) $func_subordinados.= $campos_subordinados[$i]['nome'].', \n';
    $func_subordinados = substr($func_subordinados, 0, strlen($func_subordinados) - 4);
    $texto = 'VOC� N�O PODE DESMARCAR ESTA OP��O !\nEXISTE(M) FUNCION�RIO(S) QUE ESTA(�O) SUBORDINADO(S) A ESSE FUNCION�RIO SUPERIOR !\n\n'.$func_subordinados.' !!!';
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Dados Profissionais ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function alterar_status() {
    if(document.form.cmb_status.value == 3) {//Se for Demitido habilita esse campo ...
        document.form.txt_data_demissao.value       = '<?=$data_demissao;?>'
        document.form.txt_data_demissao.disabled    = false
//Layout de Habilitado ...
        document.form.txt_data_demissao.className   = 'caixadetexto'
        document.form.txt_data_demissao.focus()
    }else {//Desabilita o Campo ...
        document.form.txt_data_demissao.value       = ''
        document.form.txt_data_demissao.disabled    = true
//Layout de Desabilitado ...
        document.form.txt_data_demissao.className   = 'textdisabled'
    }
}

function validar() {
    var id_funcionario = eval('<?=$id_funcionario_loop?>')
    var pop_up = eval('<?=$pop_up;?>')
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
            return false
    }
//Departamento
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE O DEPARTAMENTO !')) {
            return false
    }
//Cargo
    if(!combo('form', 'cmb_cargo', '', 'SELECIONE O CARGO !')) {
            return false
    }
//Tipo de Sal�rio
    if(!combo('form', 'cmb_tipo_salario', '', 'SELECIONE O TIPO DE SAL�RIO !')) {
            return false
    }
//Data de Admiss�o
    if(!data('form', 'txt_data_admissao', '4000', 'ADMISS�O')) {
            return false
    }
//Data de Demiss�o
    if(document.form.txt_data_demissao.disabled == false) {
        if(!data('form', 'txt_data_demissao', '4000', 'DEMISS�O')) {
            return false
        }
        var data_atual = eval('<?=date('Ymd');?>')
        var data_demissao = document.form.txt_data_demissao.value
        var data_demissao = data_demissao.substr(6,4) + data_demissao.substr(3,2) + data_demissao.substr(0,2)
//A Data de Demiss�o s� pode ter at� 7 dias a mais do que a Data Atual ...
        if(data_demissao > (data_atual + 7)) {
            alert('DATA DE DEMISS�O INV�LIDA !!!\nDATA DE DEMISS�O MAIOR DO QUE A DATA ATUAL !')
            document.form.txt_data_demissao.focus()
            document.form.txt_data_demissao.select()
            return false
        }
    }
/**************************Programa��o de F�rias**************************/
//For�o a preencher o Ano quando estiver preenchido o M�s ...
    if(document.form.cmb_mes.value != '' && document.form.cmb_ano.value == '') {
        alert('SELECIONE UM ANO PARA PROGRAMA��O DE F�RIAS !')
        document.form.cmb_ano.focus()
        return false
    }
//For�o a preencher o M�s quando estiver preenchido o Ano ...
    if(document.form.cmb_mes.value == '' && document.form.cmb_ano.value != '') {
        alert('SELECIONE UM M�S PARA PROGRAMA��O DE F�RIAS !')
        document.form.cmb_mes.focus()
        return false
    }
//Status
    if(!combo('form', 'cmb_status', '', 'SELECIONE O STATUS DO FUNCION�RIO !')) {
        return false
    }
//% Vale PD ...
    if(document.form.txt_perc_vale_pd.disabled == false) {
        if(document.form.txt_perc_vale_pd.value > 40) {
            alert('% DE VALE PD INV�LIDA !!!\n\nESTE VALOR N�O PODE SER MAIOR DO QUE 40% !')
            document.form.txt_perc_vale_pd.focus()
            document.form.txt_perc_vale_pd.select()
            return false
        }
    }
//Ag�ncia
    if(document.form.txt_agencia.value != '') {
        if(!texto('form', 'txt_agencia', '1', '0123456789-', 'AG�NCIA', '1')) {
            return false
        }
    }
//Conta Corrente
    if(document.form.txt_conta_corrente.value != '') {
        if(!texto('form', 'txt_conta_corrente', '1', '0123456789Xx-', 'CONTA CORRENTE', '1')) {
            return false
        }
    }
//Aqui eu verifico se o Funcion�rio tem superior ...
    if(id_funcionario == document.form.cmb_superior.value) {//Ele n�o pode ser chefe dele mesmo (rs) ...
        alert('SUPERIOR INV�LIDO PARA ESTE FUNCION�RIO !')
        document.form.cmb_superior.focus()
        return false
    }
/*************************************************************************************/
/*Caso o funcion�rio est� sendo demitido, ent�o fa�o um controle para ver se o mesmo 
est� atrelado em alguma M�quina ...*/
/*************************************************************************************/
    if(document.form.cmb_status.value == 3) {//Status de Demitido ...
//Significa que o funcion�rio possui M�quinas atreladas ...
        maquinas_atreladas = '<?=$maquinas_atreladas;?>'
        if(maquinas_atreladas != '') {//Significa que existe m�quinas atreladas ...
            maquinas_atreladas+= '\nESTE FUNCION�RIO EST� SENDO DEMITIDO ! DESATRELA O MESMO DA(S) M�QUINA(S) ???\n\n\nOBS: QUANDO O FUNCION�RIO EST� FAZENDO ACORDO, N�O DESATRELE-O DA(S) M�QUINA(S) !\n '
            var resposta = confirm(maquinas_atreladas)
            if(resposta == true) {//Significa que deseja destrelar das M�quinas ...
                document.form.hdd_desatrelar_maquinas.value = 1
            }else {//N�o deseja desatrelar das M�quinas ...
                document.form.hdd_desatrelar_maquinas.value = 0
            }
        }
    }
    //Aqui serve para n�o submeter
    if(document.form.controle.value == 0) return false
    document.form.passo.value = 1
    //Desabilito p/ n�o correr risco de perder o Cargo ...
    document.form.cmb_cargo.disabled            = false
    document.form.txt_data_prox_ferias.disabled = false
    document.form.txt_data_max_ferias.disabled  = false
//Se pop_up for = 1, ent�o significa que est� tela foi aberta como sendo pop_up ...	
    if(pop_up == 1) {
        document.form.nao_atualizar.value = 1//P/ n�o atualizar o frames abaixo desse Pop-UP
        atualizar_abaixo()
    }
    return limpeza_moeda('form', 'txt_valor_metalcred, txt_valor_pensao_alimenticia, ')
}

//Fun��o que controla para n�o submeter
function controlar(valor) {
    document.form.controle.value = valor
}

function controlar_func_superior() {
    var total_subordinados = eval('<?=$total_subordinados;?>')
//Aqui eu verifico se eu tenho algum Funcion�rio Subordinado que est� atrelado ao Funcion�rio Corrente ...
    if(total_subordinados > 0) {
/*J� existe Funcion�rio(s) Subordinado(s) a esse Funcion�rio Corrente, sendo assim n�o posso desmarcar 
essa op��o e retorno essa mensagem informando quais os usu�rios que s�o subordinados ...*/
        if(document.form.chkt_funcionario_superior.checked == false) {
            alert('<?=$texto;?>')
            document.form.chkt_funcionario_superior.checked = true
        }
    }
}

function reembolso_combustivel() {
    if(document.form.chkt_debitar_combustivel.checked == false) document.form.chkt_reembolso_combustivel.checked = false
}

//Exclus�o de Vale(s) Transporte(s) ...
function excluir_vale_transporte(id_funcionario_vale_transporte) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE VALE TRANSPORTE ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.passo.value = 0
        document.form.id_funcionario_vale_transporte.value = id_funcionario_vale_transporte
        document.form.submit()
    }
}

//Exclus�o de Dependente(s) ...
function excluir_dependente(id_dependente) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE DEPENDENTE ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.passo.value = 0
        document.form.id_dependente.value = id_dependente
        document.form.submit()
    }
}

function acertar_salario() {
    nova_janela('acertar_salario.php?id_funcionario_loop=<?=$id_funcionario_loop;?>', 'ACERTAR_SALARIO', '', '', '', '', 220, 680, 'c', 'c', '', '', 's', 's', '', '', '')
}

function calcular_datas_ferias() {
    var data_admissao               = document.form.txt_data_admissao.value
    var periodo_anual_data_inicial  = document.form.txt_periodo_anual_data_inicial.value
    var periodo_anual_data_final    = document.form.txt_periodo_anual_data_final.value
    
    if(data_admissao.length == 10 && periodo_anual_data_inicial.length == 10 && periodo_anual_data_final.length == 10) {
        /**************************************************************************/
        /*************************Data das Pr�ximas F�rias*************************/
        /**************************************************************************/
        /*Isso significa que o Funcion�rio ainda n�o desfrutou nenhuma F�rias aqui na Empresa, ent�o o c�lculo
        p/ o campo $data_prox_ferias � diferente ...*/
        if(periodo_anual_data_inicial == '00/00/0000') {
            var dia_mes_data_admissao   = data_admissao.substr(0, 6)
            var ano_data_admissao       = data_admissao.substr(6, 4)

            //Essa vari�vel a partir de agora � uma montagem feita em cima do $data_admissao ...
            var data_prox_ferias        = dia_mes_data_admissao + String(eval(ano_data_admissao) + 1)//A princ�pio s� adiciono + um ano em cima da data de Admiss�o ...
            /*Na realidade essa $data_prox_ferias, sempre ser� um dia antes de o funcion�rio 
            completar um 1 ano de Empresa pela Data de Admiss�o ...*/
            nova_data(data_prox_ferias, 'document.form.txt_data_prox_ferias', -7)
        }else {//Significa que o Funcion�rio j� desfrutou de 1 a mais F�rias ...
            var dia_mes_final_periodo_anual = periodo_anual_data_final.substr(0, 6)
            var ano_final_periodo_anual     = periodo_anual_data_final.substr(6, 4)

            //Essa vari�vel a partir de agora � uma montagem feita em cima do $periodo_anual_data_inicial ...
            var data_prox_ferias = dia_mes_final_periodo_anual + String(eval(ano_final_periodo_anual) + 1)
            document.form.txt_data_prox_ferias.value = data_prox_ferias
        }
        /**************************************************************************/
        /***************************Data Maxima de F�rias**************************/
        /**************************************************************************/
        var dia_data_max_ferias = data_prox_ferias.substr(0, 2)

        //Aqui eu subtraio 2 dessa vari�vel m�s prox f�rias pq essa ser� utilizada p/ gerar a programa��o de F�rias ...
        var mes_data_max_ferias = data_prox_ferias.substr(3, 2) - 2

        if(mes_data_max_ferias == 0) {//N�o existe m�s 0, sendo assim vira Dezembro p/ aquele Ano ...
            mes_data_max_ferias = 12
        }else if(mes_data_max_ferias == -1) {//N�o existe m�s -1, sendo assim vira Novembro p/ aquele Ano ...
            mes_data_max_ferias = 11
        }
        if(mes_data_max_ferias < 10) mes_data_max_ferias = '0' + mes_data_max_ferias

        var ano_data_max_ferias = data_prox_ferias.substr(6, 4)

        /*Se o m�s for Novembro ou Dezembro, ent�o eu n�o acrescento mais 1 na vari�vel ano, 
        porque significa que eu apenas avancei mais 10 meses na vari�vel do ano atual 
        da Data da(s) Pr�xima(s) F�ria(s): 

        *** Sempre em que os meses forem de Janeiro a Outubro eu somo mais 1 na vari�vel ano ...*/
        if(mes_data_max_ferias <= 10) ano_data_max_ferias = eval(ano_data_max_ferias) + 1

        var resposta_data_valida = validar_data(dia_data_max_ferias, mes_data_max_ferias, ano_data_max_ferias)

        //Data Inv�lida ...
        if(resposta_data_valida == 0) dia_data_max_ferias--;//Tiro um dia ...

        var data_max_ferias     = dia_data_max_ferias + '/' + mes_data_max_ferias + '/' + ano_data_max_ferias
        document.form.txt_data_max_ferias.value = data_max_ferias
        /**************************************************************************/
    }else {
        document.form.txt_data_prox_ferias.value    = ''
        document.form.txt_data_max_ferias.value     = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<?
//Se pop_up for = 1, ent�o significa que est� tela foi aberta como sendo pop_up ... 
if($pop_up == 1) $onunload = "onunload = 'atualizar_abaixo()'";
?>
<body onload='alterar_status();calcular_datas_ferias();document.form.txt_codigo.focus()' <?=$onunload;?>>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<!--Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o-->
<input type='hidden' name='id_funcionario_loop' value='<?=$id_funcionario_loop;?>'>
<!--Significa que est� tela est� sendo aberta como pop_up e sendo assim � para n�o exibir o bot�o de 
Voltar que existe nessa tela mais abaixo-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--Caixa que faz controle para submeter a tela de Cliente-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='controle' value='1'>
<input type='hidden' name='id_funcionario_vale_transporte'>
<input type='hidden' name='id_dependente'>
<input type='hidden' name='hdd_desatrelar_maquinas'>
<input type='hidden' name='passo'>
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Dados Profissionais
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td width='70%'>
            <?=$nome;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            C�digo do Funcion�rio:
        </td>
        <td>
            <input type='text' name='txt_codigo' value='<?=$codigo_barra;?>' size='20' maxlength='10' title='Digite o C�digo do Funcion�rio' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $id_empresa_func);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Email Interno:</b>
        </td>
        <td>
            <input type='text' name="txt_email_interno" value="<?=$email_interno;?>" size="35" maxlength="50" title="Digite o Email Interno" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Email Externo:</b>
        </td>
        <td>
            <input type='text' name="txt_email_externo" value="<?=$email_externo;?>" size="35" maxlength="50" title="Digite o Email Externo" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcion�rio(s) Superior(es):
        </td>
        <td>
            <select name='cmb_superior' title="Selecione o Funcion�rio Superior" class='combo'>
            <?
                /*Aqui nessa combo, eu listo somente o(s) Funcion�rio(s) que s�o Superior(es) e que 
                ainda n�o foram demitidos ...*/
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `status_superior` = '1' 
                        AND `status` < '3' ORDER BY `nome` ";
                echo combos::combo($sql, $id_funcionario_superior);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <select name='cmb_departamento' title="Selecione o Departamento" class='combo'>
            <?
                $sql = "SELECT id_departamento, departamento 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY departamento ";
                echo combos::combo($sql, $id_departamento);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font title='Permiss�o p/ altera��o somente: Roberto, Dona Sandra ou D�rcio' style='cursor:help'>
                <b>Cargo:</b>
            </font>
        </td>
        <td>
        <?
//P/ os usu�rios Roberto, Dona Sandra e D�rcio, a combo de Cargo sempre vir� habilitada p/ que possa ser trocado o cargo - Permiss�o 100% ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98) {
                $disabled   = '';
                $class      = 'caixadetexto';
            }else {//Quando for outros usu�rios, esses cargos ter�o de ser Analisados ...
/*Se forem esses cargos: 25)Supervis�o de Vendas, 27)Vendedor Externo, 47)Vendedor Interno, essa combo sempre
ter� que vir travada ...*/
                if($id_cargo == 25 || $id_cargo == 27 || $id_cargo == 47) {//Sempre ser�o travados ...
                    $disabled   = 'disabled';
                    $class      = 'textdisabled';
                }else {//Em outros cargos essa combo pode ser habilitada ...
                    $disabled   = '';
                    $class      = 'caixadetexto';
                }
            }
        ?>
            <select name='cmb_cargo' title="Selecione o Cargo" class="<?=$class;?>" <?=$disabled;?>>
            <?
                $sql = "SELECT `id_cargo`, `cargo` 
                        FROM `cargos` 
                        WHERE `ativo` = '1' 
                        AND `id_cargo` <> '82' ORDER BY `cargo` ";
                echo combos::combo($sql, $id_cargo);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Este funcion�rio � Superior: 
        </td>
        <td>
        <?
            //Se esse campo = 1, ent�o seleciono o checkbox ...
            if($status_superior == 1) $checked = 'checked';
        ?>
            <input type='checkbox' name="chkt_funcionario_superior" value="1" title="Este funcion�rio � Superior" id="id_funcionario_superior" onclick="controlar_func_superior()" class='checkbox' <?=$checked;?>>
            <label for="id_funcionario_superior">
                Sim
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Sal�rio:</b>
        </td>
        <td>
            <select name='cmb_tipo_salario' title='Selecione o Tipo de Sal�rio' class='combo'>
            <?
                if($tipo_salario == 1) {
                    $selectedh = 'selected';
                }else if($tipo_salario == 2) {
                    $selectedm = 'selected';
                }
            ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selectedh;?>>HORISTA</option>
                <option value='2' <?=$selectedm;?>>MENSALISTA</option>
            </select>
            &nbsp;
            <input type='button' name='cmd_acertar_salario' value='Acertar Sal�rio' title='Acertar Sal�rio' onclick='acertar_salario()' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            S�lario PD:
        </td>
        <td>
            <b>R$ <?=$salario_pd;?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            S�lario PF:
        </td>
        <td>
            <b>R$ <?=$salario_pf;?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Pr�mio PF (N�o Incide 13� + F�rias):
        </td>
        <td>
            <b>R$ <?=$salario_premio;?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Garantia Salarial:
        </td>
        <td>
            <b>R$ <?=$garantia_salarial;?></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Admiss�o:</b>
        </td>
        <td>
            <input type='text' name='txt_data_admissao' value='<?=$data_admissao;?>' size='12' maxlength='10' title='Digite a Data de Admiss�o' onkeyup="verifica(this, 'data', '', '', event);calcular_datas_ferias()" class='caixadetexto'>
            &nbsp; <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_admissao.disabled == true) { return false }else { javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_admissao&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')}">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Demiss�o:</b>
        </td>
        <td>
            <input type='text' name='txt_data_demissao' value='<?=$data_demissao;?>' size='12' maxlength='10' title='Digite a Data de Demiss�o' onkeyup="verifica(this, 'data', '', '', event)" class="textdisabled" disabled>
            &nbsp; <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_demissao.disabled == true) { return false }else { javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_demissao&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')}">
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados de F�rias
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            �ltimas F�rias:
        </td>
        <td>
            <input type='text' name='txt_ultimas_ferias_data_inicial' value='<?=$ultimas_ferias_data_inicial;?>' size='12' maxlength='10' title='Digite a Data Inicial das �ltimas F�rias' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_ultimas_ferias_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            �
            <input type='text' name='txt_ultimas_ferias_data_final' value='<?=$ultimas_ferias_data_final;?>' size='12' maxlength='10' title='Digite a Data Final das �ltimas F�rias' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_ultimas_ferias_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ref. Per�odo Anual:
        </td>
        <td>
            <input type='text' name='txt_periodo_anual_data_inicial' value='<?=$periodo_anual_data_inicial;?>' size='12' maxlength='10' title='Digite a Data Inicial do Per�odo Anual' onkeyup="verifica(this, 'data', '', '', event);calcular_datas_ferias()" class='caixadetexto'>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_periodo_anual_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            �
            <input type='text' name='txt_periodo_anual_data_final' value='<?=$periodo_anual_data_final;?>' size='12' maxlength='10' title='Digite a Data Inicial do Per�odo Anual' onkeyup="verifica(this, 'data', '', '', event);calcular_datas_ferias()" class='caixadetexto'>
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_periodo_anual_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data da(s) Pr�xima(s) F�ria(s):
        </td>
        <td>
            <input type='text' name='txt_data_prox_ferias' size='12' maxlength='10' title='Data da Pr�xima F�rias' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data M�xima de F�rias:
        </td>
        <td>
            <input type='text' name='txt_data_max_ferias' size='12' maxlength='10' title='Data M�xima de F�rias (Venc. M�x. a Gozar)' class='textdisabled' disabled> (60 dias antes do Vencimento das 2� f�rias)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Programa��o de F�rias:
        </td>
        <td>
            M�s: 
            <select name='cmb_mes' title="Selecione o M�s" class='combo'>
            <option value = '' selected style='color:red'>SELECIONE</option>
            <?
                $mes_programacao_ferias = substr($campos[0]['programacao_ferias'], 5, 2);
//Criei esse vetor aqui porque achei + facil, pra listagem dos Meses no Banco de Dados ...
                $vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Mar�o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
                for($mes = 1; $mes < count($vetor_meses); $mes++) {
//Se o m�s selecionado for igual ao m�s do Loop ...
                    if($mes_programacao_ferias == $mes) {
            ?>
                <option value='<?=$mes;?>' selected><?=$vetor_meses[$mes];?></option>
            <?
                }else {
            ?>
                <option value='<?=$mes;?>'><?=$vetor_meses[$mes];?></option>
            <?
                    }
                }
            ?>
            </select>
            Ano: 
            <select name='cmb_ano' title="Selecione o Ano" class='combo'>
            <option value = '' selected style='color:red'>SELECIONE</option>
            <?
                $ano_programacao_ferias = substr($campos[0]['programacao_ferias'], 0, 4);
                for($ano = date('Y'); $ano < (date('Y') + 10); $ano++) {
                    //Se o ano selecionado for igual ao ano do Loop ...
                    if($ano_programacao_ferias == $ano) {
            ?>
                    <option value='<?=$ano;?>' selected><?=$ano;?></option>
            <?
                    }else {
            ?>
                    <option value='<?=$ano;?>'><?=$ano;?></option>
            <?
                    }
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Status:</b>
        </td>
        <td>
            <select name='cmb_status' onchange='alterar_status()' class='combo'>
                <option value = '' selected style='color:red'>SELECIONE</option>
                <?
//Criei esse vetor aqui porque achei + facil, pra na hora de comparar com o valor q retorna do Banco
                    $vetor_status = array('F�rias', 'Ativo', 'Afastado', 'Demitido');
                    for($i = 0; $i < 4; $i++) {
                        if($status == $i) {
                ?>
                <option value="<?=$i?>" selected><?=$vetor_status[$i];?></option>
                <?
                        }else {
                ?>
                <option value="<?=$i?>"><?=$vetor_status[$i];?></option>
                <?
                        }
                    }
                ?>
            </select>
        </td>
    </tr>
<!--*************************************************************************-->
    <tr class='linhadestaque'>
        <td colspan='3'>
<!--Se tiver checado a op��o de Retirar Vale Transporte, ent�o habilita o link de Incluir Vale(s) Transporte(s) -->
            <a href='#' onclick="if('<?=$campos[0]['retira_vale_transporte'];?>' == 'S') {nova_janela('incluir_vale_transporte.php?id_funcionario_loop=<?=$id_funcionario_loop;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')}else {alert('HABILITE A OP��O RETIRAR VALE TRANSPORTE !')}" title='Incluir Vale(s) Transporte(s)'>
                <font color='#FFFF00'>
                    <b><i>Incluir Vale(s) Transporte(s)</i></b>
                </font>
            </a>
        </td>
    </tr>
<?
    if(!empty($id_funcionario_loop)) {
//Aqui traz todos os Vales Transportes que est�o relacionados ao Funcion�rio ...
        $sql = "SELECT vt.`tipo_vt`, vt.`valor_unitario`, fvt.* 
                FROM `funcionarios_vs_vales_transportes` fvt 
                INNER JOIN `vales_transportes` vt ON vt.`id_vale_transporte` = fvt.`id_vale_transporte` 
                WHERE fvt.`id_funcionario` = '$id_funcionario_loop' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        if($linhas2 > 0) {
?>
<table width='80%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Tipo de Vale Transporte
        </td>
        <td>
            Valor Unit�rio
        </td>
        <td>
            Qtde de Vale
        </td>
        <td>
            Valor Total
        </td>
        <td width='26'>
            &nbsp;
        </td>
        <td width='26'>
            &nbsp;
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas2; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos2[$i]['tipo_vt'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos2[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$campos2[$i]['qtde_vale'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos2[$i]['valor_unitario'] * $campos2[$i]['qtde_vale'], 2, ',', '.');?>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar' alt='Alterar' onclick="nova_janela('alterar_vale_transporte.php?id_funcionario_vale_transporte=<?=$campos2[$i]['id_funcionario_vale_transporte'];?>', 'CONSULTAR', '', '', '', '', '350', '700', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir' alt='Excluir' onclick="excluir_vale_transporte('<?=$campos2[$i]['id_funcionario_vale_transporte'];?>')">
        </td>
    </tr>
<?
            }
        }
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <a href="javascript:nova_janela('incluir_dependente.php?id_funcionario_loop=<?=$id_funcionario_loop;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" title='Incluir Dependente(s)'>
                <font color='#FFFF00'>
                    <b><i>Incluir Dependente(s)</i></b>
                </font>
            </a>
        </td>
    </tr>
</table>
<?
//Aqui traz todos os Dependentes que est�o relacionados ao Funcion�rio ...
        $sql = "SELECT `id_dependente`, `nome`, DATE_FORMAT(`data_nascimento`, '%d/%m/%Y') AS data_nascimento 
                FROM `dependentes` 
                WHERE `id_funcionario` = '$id_funcionario_loop' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
        if($linhas2 > 0) {
?>
<table width='80%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Nome
        </td>
        <td>
            Data de Nascimento
        </td>
        <td width='26'>
            &nbsp;
        </td>
        <td width='26'>
            &nbsp;
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas2; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos2[$i]['nome'];?>
        </td>
        <td>
            <?=$campos2[$i]['data_nascimento'];?>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar' alt='Alterar' onclick="nova_janela('alterar_dependente.php?id_dependente=<?=$campos2[$i]['id_dependente'];?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir' alt='Excluir' onclick="excluir_dependente('<?=$campos2[$i]['id_dependente'];?>')">
        </td>
    </tr>
<?
            }
        }
    }
?>
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Controle(s) Extra(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
/*N�o existe um campo p/ gravar o checkbox diretamente no BD, ent�o o controle nesse caso � um 
pouquinho diferente ...*/
                if($pensao_alimenticia > 0) {
                    $checked    = 'checked';
                    $class      = 'caixadetexto';
                    $disabled   = '';
                }else {
                    $checked    = '';
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                    $pensao_alimenticia = '';
                }
            ?>
            <input type='checkbox' name='chkt_pensao_alimenticia' value='S' title='Pens�o Aliment�cia' id='label0' onclick='pensao_alimenticia()' class='checkbox' <?=$checked;?>>
            <label for='label0'>
                    Pens�o Aliment�cia
            </label>
        </td>
        <td>
            <input type='text' name='txt_pensao_alimenticia' value='<?=$pensao_alimenticia;?>' title='Digite a Qtde de Dependentes' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value > 1000) {this.value = ''}" size='12' class='<?=$class;?>' <?=$disabled;?>>
            - R$
            <input type='text' name='txt_valor_pensao_alimenticia' value='<?=$valor_pensao_alimenticia;?>' title='Digite o Valor da Pensao Alimenticia' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($campos[0]['debitar_conv_medico'] == 'S') {
                    $checked    = 'checked';
                    $class      = 'caixadetexto';
                    $disabled   = '';
                    $dependentes_conv_medico = $campos[0]['dependentes_conv_medico'];
                }else {
                    $checked    = '';
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                    $dependentes_conv_medico = '';
                }
            ?>
            <input type='checkbox' name="chkt_debitar_convenio_medico" value='S' title="Debitar Conv�nio M�dico" id='label1' onclick="debitar_convenio_medico()" class='checkbox' <?=$checked;?>>
            <label for='label1'>
                Debitar Conv�nio M�dico
            </label>
        </td>
        <td>
            <input type='text' name="txt_qtde_dependentes" value="<?=$dependentes_conv_medico;?>" title="Digite a Qtde de Dependentes" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value > 1000) {this.value = ''}" size="12" class="<?=$class;?>" <?=$disabled;?>> Qtde de Dependentes
            &nbsp;
            <?$checked = ($campos[0]['debitar_acidente_trabalho'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_debitar_acidente_trabalho' value='S' title='Debitar Acidente de Trabalho' id='label2' class='checkbox' <?=$checked;?>>
            <label for='label2'>
                Debitar Acidente de Trabalho
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($campos[0]['debitar_conv_odonto'] == 'S') {
                    $checked    = 'checked';
                    $class      = 'caixadetexto';
                    $disabled   = '';
                    $qtde_plano_odonto = $campos[0]['qtde_plano_odonto'];
                }else {
                    $checked    = '';
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                    $qtde_plano_odonto = '';
                }
            ?>
            <input type='checkbox' name="chkt_debitar_convenio_odonto" value='S' title="Debitar Conv�nio Odontol�gico" id='label3' onclick="debitar_convenio_odontologico()" class='checkbox' <?=$checked;?>>
            <label for='label3'>
                Debitar Conv�nio Odontol�gico
            </label>
        </td>
        <td>
            <input type='text' name="txt_qtde_planos" value="<?=$qtde_plano_odonto;?>" title="Digite a Qtde de Planos" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value > 1000) {this.value = ''}" size="12" class="<?=$class;?>" <?=$disabled;?>> Qtde de Planos
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($campos[0]['debitar_combustivel'] == 'S') {
                    $checked    = 'checked';
                    $class      = 'caixadetexto';
                    $disabled   = '';
                    $qtde_litros_combustivel = $campos[0]['qtde_litros_combustivel'];
                }else {
                    $checked    = '';
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                    $qtde_litros_combustivel = '';
                }
            ?>
            <input type='checkbox' name="chkt_debitar_combustivel" value='S' title="Debitar Combust�vel" id='label4' onclick="debitar_combustivel()" class='checkbox' <?=$checked;?>>
            <label for='label4'>
                Debitar Combust�vel
            </label>
        </td>
        <td>
            <input type='text' name="txt_qtde_litros" value="<?=$qtde_litros_combustivel;?>" title="Digite a Qtde de Litros" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value > 1000) {this.value = ''}" size="12" class="<?=$class;?>" <?=$disabled;?>> Qtde de Litros
            &nbsp;
            <?$checked = ($campos[0]['reembolso_combustivel'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_reembolso_combustivel" value='S' title="Reembolso Combust�vel" id='labelR' onclick="reembolso_combustivel()" class='checkbox' <?=$checked;?>>
            <label for='labelR'>
                Reembolso
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            if($campos[0]['debitar_celular'] == 'S') {
                $checked                = 'checked';
                $class                  = 'caixadetexto';
                $disabled               = '';
            }else {
                $checked                = '';
                $class                  = 'textdisabled';
                $disabled               = 'disabled';
            }
        ?>
        <input type='checkbox' name='chkt_debitar_celular' value='S' title="Debitar Celular" id='label5' class='checkbox' <?=$checked;?>>
        <label for='label5'>
            Debitar Celular
        </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($campos[0]['retirar_vale_dia_20'] == 'S') {
                    $checked        = 'checked';
                    $class          = 'caixadetexto';
                    $disabled       = '';
                    $perc_vale_pd   = $campos[0]['perc_vale_pd'];
                }else {
                    $checked = '';
                    $class          = 'textdisabled';
                    $disabled       = 'disabled';
                    $perc_vale_pd   = '';
                }
            ?>
            <input type='checkbox' name='chkt_retirar_vale_dia20' value='S' title='Retirar Vale do Dia 20' id='label6' onclick='retirar_vale_dia20()' class='checkbox' <?=$checked;?>>
            <label for='label6'>
                Retirar Vale do Dia 20
            </label>
        </td>
        <td>
            <input type='text' name='txt_perc_vale_pd' value="<?=$perc_vale_pd;?>" title='Digite a % do Vale PD' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" maxlength='2' size='3' class='<?=$class;?>' <?=$disabled;?>> % Vale PD
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['debitar_mensal_sindical'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_debitar_mensal_sindical" value='S' title="Debitar Mensalidade Sindical" id='label7' class='checkbox' <?=$checked;?>>
            <label for='label7'>
                Debitar Mensalidade Sindical
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['debitar_contrib_federativa'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_debitar_contrib_confederativa' value='S' title="Debitar Contribui��o Confederativa" id='label8' class='checkbox' <?=$checked;?>>
            <label for='label8'>
                Debitar Contribui��o Confederativa
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['debitar_contrib_assistencial'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_debitar_contrib_assistencial" value='S' title="Debitar Contribui��o Assistencial" id='label9' class='checkbox' <?=$checked;?>>
            <label for='label9'>
                Debitar Contribui��o Assistencial
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['retira_vale_transporte'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_retira_vale_transporte" value='S' title="Retirar Vale Transporte" id='label10' class='checkbox' <?=$checked;?>>
            <label for='label10'>
                Retirar Vale Transporte
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['conducao_propria'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_conducao_propria' value='S' title="Condu��o Pr�pria" id='label11' class='checkbox' <?=$checked;?>>
            <label for='label11'>
                Condu��o Pr�pria
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['sindicalizado'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_sindicalizado" value='S' title="Sindicalizado" id='label12' class='checkbox' <?=$checked;?>>
            <label for='label12'>
                Sindicalizado
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?$checked = ($campos[0]['tem_direito_plr'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name="chkt_tem_direito_plr" value='S' title="Tem direito � PLR" id='label13' class='checkbox' <?=$checked;?>>
            <label for='label13'>
                Tem direito � PLR
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($campos[0]['mensalidade_metalcred'] == 'S') {
                    $checked            = 'checked';
                    $class              = 'caixadetexto';
                    $disabled           = '';
                    $valor_metalcred    = number_format($campos[0]['valor_metalcred'], 2, ',', '.');
                }else {
                    $checked            = '';
                    $class              = 'textdisabled';
                    $disabled           = 'disabled';
                    $valor_metalcred    = '';
                }
            ?>
            <input type='checkbox' name='chkt_mensalidade_metalcred' value='S' title='Mensalidade MetalCred' id='label14' onclick='mensalidade_metalcred()' class='checkbox' <?=$checked;?>>
            <label for='label14'>
                Mensalidade MetalCred
            </label>
        </td>
        <td>
            R$ <input type='text' name="txt_valor_metalcred" value="<?=$valor_metalcred;?>" title="Digite o Valor MetalCred" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="12" class="<?=$class;?>" <?=$disabled;?>> Valor MetalCred
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            C�digo do Banco:
        </td>
        <td>
            <select name='cmb_cod_banco' title='Selecione o C�digo do Banco' class='combo'>
                <?=combos::combo_array($cadastro_banco, $cod_banco);?>
            </select>
            &nbsp;Forma de Pagamento:
            <select name='cmb_forma_pagamento' title='Selecione a Forma de Pagamento' class='combo'>
            <?
                if($campos[0]['cheque_dinheiro'] == 'N') {
                    $selected1 = 'selected';
                }else if($campos[0]['cheque_dinheiro'] == 'C') {
                    $selected2 = 'selected';
                }else if($campos[0]['cheque_dinheiro'] == 'D') {
                    $selected3 = 'selected';
                }
            ?>
                <option value="N" <?=$selected1;?>>NENHUM</option>
                <option value="C" <?=$selected2;?>>CHEQUE</option>
                <option value="D" <?=$selected3;?>>DINHEIRO</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ag�ncia:
        </td>
        <td>
            <input type='text' name='txt_agencia' value='<?=$campos[0]['agencia'];?>' size='6' maxlength='5' title='Digite a Ag�ncia' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Corrente:
        </td>
        <td>
            <input type='text' name='txt_conta_corrente' value='<?=$campos[0]['conta_corrente'];?>' size='11' maxlength='10' title='Digite a Conta Corrente' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
/*Se pop_up for = 1, ent�o significa que est� tela foi aberta como sendo pop_up e sendo assim eu n�o 
posso exibir esse bot�o*/ 
            if($pop_up != 1) {
        ?>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.parent.location = 'alterar2.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');alterar_status();document.form.txt_codigo.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
/*Se pop_up for = 1, ent�o significa que est� tela foi aberta como sendo pop_up e sendo assim eu exibo
esse bot�o para fechar a Tela*/ 
            if($pop_up == 1) {
        ?>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick='fechar(window)' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
<Script Language = 'JavaScript'>
/*Coloquei essas fun��es em JavaScript aqui na parte de baixo, porque tem algumas vari�veis em PHP
que eu s� fui carregando no decorrer da p�gina ...*/
function pensao_alimenticia() {
    if(document.form.chkt_pensao_alimenticia.checked == true) {//Se estiver checado, habilita a caixa ...
        document.form.txt_pensao_alimenticia.disabled           = false
        document.form.txt_pensao_alimenticia.className          = 'caixadetexto'
        document.form.txt_pensao_alimenticia.value              = '<?=$pensao_alimenticia;?>'
        document.form.txt_valor_pensao_alimenticia.disabled     = false
        document.form.txt_valor_pensao_alimenticia.className    = 'caixadetexto'
        document.form.txt_valor_pensao_alimenticia.value        = '<?=$valor_pensao_alimenticia;?>'
        document.form.txt_pensao_alimenticia.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_pensao_alimenticia.disabled           = true
        document.form.txt_pensao_alimenticia.className          = 'textdisabled'
        document.form.txt_pensao_alimenticia.value              = ''
        document.form.txt_valor_pensao_alimenticia.disabled     = true
        document.form.txt_valor_pensao_alimenticia.className    = 'textdisabled'
        document.form.txt_valor_pensao_alimenticia.value        = ''                
    }
}

function debitar_convenio_medico() {
    if(document.form.chkt_debitar_convenio_medico.checked == true) {//Se estiver checado, habilita a caixa ...
        document.form.txt_qtde_dependentes.disabled     = false
        document.form.txt_qtde_dependentes.className    = 'caixadetexto'
        document.form.txt_qtde_dependentes.value        = '<?=$dependentes_conv_medico;?>'
        document.form.txt_qtde_dependentes.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_qtde_dependentes.disabled     = true
        document.form.txt_qtde_dependentes.className    = 'textdisabled'
        document.form.txt_qtde_dependentes.value        = ''
    }
}

function debitar_convenio_odontologico() {
    if(document.form.chkt_debitar_convenio_odonto.checked == true) {//Se estiver checado, habilita a caixa .
        document.form.txt_qtde_planos.disabled      = false
        document.form.txt_qtde_planos.className     = 'caixadetexto'
        document.form.txt_qtde_planos.value         = '<?=$qtde_plano_odonto;?>'
        document.form.txt_qtde_planos.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_qtde_planos.disabled      = true
        document.form.txt_qtde_planos.className     = 'textdisabled'
        document.form.txt_qtde_planos.value         = ''
    }
}

function debitar_combustivel() {
    if(document.form.chkt_debitar_combustivel.checked == true) {//Se estiver checado, habilita a caixa ..
        document.form.txt_qtde_litros.disabled      = false
        document.form.txt_qtde_litros.className     = 'caixadetexto'
        document.form.txt_qtde_litros.value         = '<?=$qtde_litros_combustivel?>'
        document.form.txt_qtde_litros.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_qtde_litros.disabled      = true
        document.form.txt_qtde_litros.className     = 'textdisabled'
        document.form.txt_qtde_litros.value         = ''
        document.form.chkt_reembolso_combustivel.checked = false
    }
}

function retirar_vale_dia20() {
    if(document.form.chkt_retirar_vale_dia20.checked == true) {//Se estiver checado, habilita a caixa ...
        document.form.txt_perc_vale_pd.disabled     = false
        document.form.txt_perc_vale_pd.className    = 'caixadetexto'
        document.form.txt_perc_vale_pd.value        = '<?=$perc_vale_pd;?>'
        document.form.txt_perc_vale_pd.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_perc_vale_pd.disabled     = true
        document.form.txt_perc_vale_pd.className    = 'textdisabled'
        document.form.txt_perc_vale_pd.value        = ''
    }
}

function mensalidade_metalcred() {
    if(document.form.chkt_mensalidade_metalcred.checked == true) {//Se estiver checado, habilita a caixa ...
        document.form.txt_valor_metalcred.disabled  = false
        document.form.txt_valor_metalcred.className = 'caixadetexto'
        document.form.txt_valor_metalcred.value     = '<?=$valor_metalcred;?>'
        document.form.txt_valor_metalcred.focus()
    }else {//Se n�o estiver, desabilita a caixa ...
        document.form.txt_valor_metalcred.disabled  = true
        document.form.txt_valor_metalcred.className = 'textdisabled'
        document.form.txt_valor_metalcred.value     = ''
    }
}
</Script>
<pre>
<font color='red' size='6'>
Aten��o:</font><font color='blue' size='4'>Essa tela possuem dados que influ�ncia<br>direto no Custo / Comiss�es / Pagamentos do Sistema. 
</font>
</pre>

</html>
<?}?>