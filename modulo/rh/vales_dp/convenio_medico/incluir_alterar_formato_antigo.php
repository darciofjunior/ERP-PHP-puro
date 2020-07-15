<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CONVÊNIO MÉDICO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
    $data_sys = date('Y-m-d H:i:s');
//Primeiro apaga-se todos os vales do Tipo Convênio Médico p/ poder gerar Novos Vales Válidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '5' 
            AND `data_debito` = '$cmb_data_holerith' ";
    bancos::sql($sql);
//Aqui nesse loop eu disparo todos os funcionários da Empresa selecionada ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {
//Busca da Empresa do Funcionário porque eu tenho um controle mais abaixo ...
        $sql = "SELECT `id_empresa` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_loop' ";
        $campos_empresa     = bancos::sql($sql);
//Aqui eu tenho q/ renomear a Empresa p/ não dar conflito a variável $id_empresa da Sessão ...
        $id_empresa_loop    = $campos_empresa[0]['id_empresa'];
//Se a Empresa for Alba ou Tool, eu tenho que descontar do salário PD do Funcionário ...
        if($id_empresa_loop == 1 || $id_empresa_loop == 2) {
            $descontar_pd_pf = 'PD';
        }else {//Descontar do salário PF do Funcionário quando a Empresa for Grupo ...
            $descontar_pd_pf = 'PF';
        }
//Se o Valor do vale <> 0, então eu gero vale para esse funcionário ...
        if($_POST['txt_vlr_vale'][$i] != 0) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor_fatura`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '5', '".$_POST['txt_vlr_fatura'][$i]."', '".$_POST['txt_vlr_vale'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', '$descontar_pd_pf', '$data_sys') ";
            bancos::sql($sql);
        }
//Aqui eu atualizo a última Fatura do Convênio Médico no cadastro de funcionários ...
        $sql = "UPDATE `funcionarios` SET `fatura_conv_medico` = '".$_POST['txt_vlr_fatura'][$i]."' WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
        bancos::sql($sql);
    }
/*********************************************************************************************/
/****E-mail Informativo p/ que se Compare o Total do Convênio c/ o Total da Folha Impressa****/
/*********************************************************************************************/
    $mensagem_email = 'O valor do Convênio Médico para a Data do Holerith '.data::datetodata($_POST['cmb_data_holerith'], '/').' ficou em R$ '.$_POST['txt_total_vlr_fatura'].'.';
    comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br; sandra@grupoalbafer.com.br', '', 'Convênio Médico - Data de Holerith '.data::datetodata($_POST[cmb_data_holerith], '/'), $mensagem_email);
/*********************************************************************************************/
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
    </Script>
<?
}else {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao   = date('Y-m-d');
    $data_sys       = date('Y-m-d H:i:s');
/****************************************************************************************************/
//Aqui eu já deixo carregada essa variável porque vou estar utilizando essa nos cálculos em PHP e JavaScript
    $valor_base_convenio_medico                 = genericas::variavel(27);
    $valor_base_acidente_trabalho               = genericas::variavel(30);
    $participacao_titular_convenio_medico       = genericas::variavel(80);
    $participacao_dependente_convenio_medico    = genericas::variavel(81);

/*Listagem de Funcionários que ainda estão trabalhando e que estão com a marcação de Convênio Médico ...
* Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT e.`nomefantasia`, f.`id_funcionario`, f.`id_empresa`, f.`nome`, f.`dependentes_conv_medico`, 
            f.`fatura_conv_medico`, f.`debitar_acidente_trabalho` 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`status` < '3' 
            AND f.`debitar_conv_medico` = 'S' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhum funcionário com essa marcação ...
?>
        <Script Language = 'Javascript'>
            window.location = '../itens/incluir.php?valor=1'
        </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Incluir / Alterar Vale(s) - Convênio Médico ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
//Verifico se existe algum campo em que o Valor da Fatura é maior do que o Valor de Direito ...
    for (var i = 0; i < linhas; i++) {
        var valor_direito   = eval(strtofloat(document.getElementById('txt_vlr_direito'+i).value))
        var valor_fatura    = eval(strtofloat(document.getElementById('txt_vlr_fatura'+i).value))
//Se o Valor da Fatura for > que o Valor de Direito, então o Sistema tem que barrar informando ao usuário...
        if(valor_fatura < valor_direito) {
            alert('VALOR DA FATURA INVÁLIDO !\nVALOR DA FATURA MENOR DO QUE O VALOR DE DIREITO !')
            document.getElementById('txt_vlr_fatura'+i).focus()
            document.getElementById('txt_vlr_fatura'+i).select()
            return false
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
//Tratamento nos objetos Vlr Fatura e Vlr Vale p/ gravar os objetos no BD ...
    for (var i = 0; i < linhas; i++) {
        document.getElementById('txt_vlr_fatura'+i).value       = strtofloat(document.getElementById('txt_vlr_fatura'+i).value)
        document.getElementById('txt_vlr_vale'+i).value         = strtofloat(document.getElementById('txt_vlr_vale'+i).value)   
//Desabilito estes campos p/ poder gravar no Banco de Dados ...
        document.getElementById('txt_vlr_fatura'+i).disabled    = false
        document.getElementById('txt_vlr_vale'+i).disabled      = false
    }
    //Desabilito esse campo, porque o valor do mesmo será enviado via e-mail à Diretoria ...
    document.form.txt_total_vlr_fatura.disabled = false
}

function calcular(indice) {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_dependentes[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
//Igualando as variáveis ...
    qtde_dependentes    = document.getElementById('txt_qtde_dependentes'+indice).value
    vlr_direito         = eval(strtofloat(document.getElementById('txt_vlr_direito'+indice).value))
    if(document.getElementById('txt_vlr_fatura'+indice).value != '' && document.getElementById('txt_vlr_fatura'+indice).value != '0,00') {//Se este campo estiver preenchido ...
        vlr_fatura = eval(strtofloat(document.getElementById('txt_vlr_fatura'+indice).value))
//Aqui eu cálculo o Campo Valor do Vale ...
        document.getElementById('txt_vlr_vale'+indice).value = vlr_fatura - vlr_direito
        document.getElementById('txt_vlr_vale'+indice).value = arred(document.getElementById('txt_vlr_vale'+indice).value, 2, 1)
    }
    var total_vlr_fatura = 0
//Aqui eu faço um somatório do Total Vlr Fatura ...
    for (var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_vlr_fatura'+i).value != '' && document.getElementById('txt_vlr_fatura'+i).value != '0,00') {
            var vlr_fatura = eval(strtofloat(document.getElementById('txt_vlr_fatura'+i).value))
        }else {
            var vlr_fatura = 0
        }
        total_vlr_fatura+= vlr_fatura
    }
    document.form.txt_total_vlr_fatura.value = total_vlr_fatura
    document.form.txt_total_vlr_fatura.value = arred(document.form.txt_total_vlr_fatura.value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Incluir / Alterar Vale(s) - Convênio Médico
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <font color='yellow'>
                Data de Holerith:
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
        </td>
        <td colspan='2'>
            <font color='yellow'>
                Valor Base: 
            </font>
            <?='R$ '.number_format($valor_base_convenio_medico, 2, ',', '.');?>
        </td>
        <td colspan='2'>
            <font color='yellow'>
                Valor Acidente de Trabalho: 
            </font>
            <?='R$ '.number_format($valor_base_acidente_trabalho, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
        <td>
            Qtde de Dep
        </td>
        <td>
            Vlr Direito
        </td>
        <td>
            Vlr Fatura
        </td>
        <td>
            Vlr Vale
        </td>
    </tr>
<?
    $cont = 0;
    for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
        $id_funcionario_loop = $campos[$i]['id_funcionario'];
//Cálculos e controle com o Pop-Up ... 
        $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$id_funcionario_loop."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        $qtde_depentes  = $campos[$i]['dependentes_conv_medico'];
        $vlr_direito    = ((100 - $participacao_titular_convenio_medico) / 100 + $qtde_depentes * (100 - $participacao_dependente_convenio_medico) / 100) * $valor_base_convenio_medico;

        //Somente na Empresa Tool Master que somamos a variável "$valor_base_acidente_trabalho" ...
        if($campos[$i]['debitar_acidente_trabalho'] == 'S') $vlr_direito+= $valor_base_acidente_trabalho;

        $vlr_fatura     = $campos[$i]['fatura_conv_medico'];
        $vlr_vale       = $vlr_fatura - $vlr_direito;
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <input type='text' name='txt_qtde_dependentes[]' id='txt_qtde_dependentes<?=$i;?>' value="<?=$qtde_depentes;?>" title='Qtde de Dependentes' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_vlr_direito[]' id='txt_vlr_direito<?=$i;?>' value="<?=number_format($vlr_direito, 2, ',', '.');?>" title='Valor de Direito' size='10' class='textdisabled' disabled>
            <?
                if($campos[$i]['debitar_acidente_trabalho'] == 'S') {
                    echo '<font title="Debitar Acidente de Trabalho" color="red" style="cursor:help"><b>(DAT)</b></font>';
                }else {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_vlr_fatura[]' id='txt_vlr_fatura<?=$i;?>' value="<?=number_format($vlr_fatura, 2, ',', '.');?>" title="Digite o Valor da Fatura" size="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>')" tabindex="<?='1'.$cont;?>" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_vlr_vale[]' id='txt_vlr_vale<?=$i;?>' value="<?=number_format($vlr_vale, 2, ',', '.');?>" title="Valor do Vale" size="10" class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' id='hdd_funcionario<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
    </tr>
<?
//Essa variável aqui eu apresento mais abaixo no fim do loop ...
        $total_vlr_fatura+= $vlr_fatura;
        $cont++;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
        <td align='right'>
            Total Vlr Fatura R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_fatura' value="<?=number_format($total_vlr_fatura, 2, ',', '.');?>" title='Total Vlr Fatura' size='10' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>