<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CONV�NIO ODONTOL�GICO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
    $data_sys = date('Y-m-d H:i:s');
//Primeiro apaga-se todos os vales do Tipo Conv�nio Odontol�gico p/ poder gerar Novos Vales V�lidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '6' 
            AND `data_debito` = '$cmb_data_holerith' ";
    bancos::sql($sql);
//Aqui nesse loop eu disparo todos os funcion�rios da Empresa selecionada ...
    for($i = 0; $i < count($hdd_funcionario); $i++) {
//Busca da Empresa do Funcion�rio porque eu tenho um controle mais abaixo ...
        $sql = "SELECT `id_empresa` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$hdd_funcionario[$i]' ";
        $campos_funcionario = bancos::sql($sql);
//Aqui eu tenho q/ renomear a Empresa p/ n�o dar conflito a vari�vel $id_empresa da Sess�o ...
        $id_empresa_loop    = $campos_funcionario[0]['id_empresa'];
//Se a Empresa for Alba ou Tool, eu tenho que descontar do sal�rio PD do Funcion�rio ...
        if($id_empresa_loop == 1 || $id_empresa_loop == 2) {
            $descontar_pd_pf = 'PD';
//Descontar do sal�rio PF do Funcion�rio quando a Empresa for Grupo ...
        }else {
            $descontar_pd_pf = 'PF';
        }
//Se o Valor do vale <> 0, ent�o eu gero vale para esse funcion�rio ...
        if($txt_vlr_vale[$i] != 0.00) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$hdd_funcionario[$i]', '6', '$txt_vlr_vale[$i]', '$_POST[cmb_data_holerith]', '$data_emissao', '$descontar_pd_pf', '$data_sys') ";
            bancos::sql($sql);
        }
    }
/*********************************************************************************************/
/****E-mail Informativo p/ que se Compare o Total do Conv�nio c/ o Total da Folha Impressa****/
/*********************************************************************************************/
    $mensagem_email = 'O valor do Conv�nio Odontol�gico para a Data do Holerith '.data::datetodata($_POST['cmb_data_holerith'], '/').' ficou em R$ '.$_POST['txt_total_vlr_fatura'].'.';
    comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br; sandra@grupoalbafer.com.br', '', 'Conv�nio Odontol�gico - Data de Holerith '.data::datetodata($_POST[cmb_data_holerith], '/'), $mensagem_email);
/*********************************************************************************************/
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
    </Script>
<?
}else {
/****************************************************************************************************/
//Aqui eu j� deixo carregada essa vari�vel porque vou estar utilizando essa nos c�lculos em PHP e JavaScript
    $valor_base_conv_odonto = genericas::variavel(28);
/*Listagem de Funcion�rios que ainda est�o trabalhando e que est�o com a marca��o de Conv�nio Odontol�gico...
* S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT `id_funcionario`, `nome`, `qtde_plano_odonto` 
            FROM `funcionarios` 
            WHERE `status` < '3' 
            AND `debitar_conv_odonto` = 'S' 
            AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY `nome` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//N�o encontrou nenhum funcion�rio com essa marca��o ...
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
<title>.:: Incluir Vale(s) - Conv�nio Odontol�gico ::.</title>
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
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_qtde_plano_odonto'+i).value    = strtofloat(document.getElementById('txt_qtde_plano_odonto'+i).value)
        document.getElementById('txt_vlr_vale'+i).value             = strtofloat(document.getElementById('txt_vlr_vale'+i).value)
//Habilito a caixa p/ poder gravar no Banco ...
        document.getElementById('txt_qtde_plano_odonto'+i).disabled = false
        document.getElementById('txt_vlr_vale'+i).disabled = false
    }
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
//Desabilito esse campo, porque o valor do mesmo ser� enviado via e-mail � Diretoria ...
    document.form.txt_total_vlr_fatura.disabled = false
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo' onclick="atualizar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir Vale(s) - Conv�nio Odontol�gico
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
        </td>
        <td colspan='2'>
            <font color='yellow'>
                Valor Base do Conv�nio Odontol�gico: 
            </font>
            <?='R$ '.number_format($valor_base_conv_odonto, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcion�rio
        </td>
        <td>
            Qtde de Planos
        </td>
        <td>
            Vlr Vale
        </td>
    </tr>
<?
    $cont = 0;
    for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
        $id_funcionario_loop = $campos[$i]['id_funcionario'];
//C�lculos e controle com o Pop-Up ... 
        $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$id_funcionario_loop."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        $qtde_plano_odonto = $campos[$i]['qtde_plano_odonto'];
        $vlr_vale = $valor_base_conv_odonto * $qtde_plano_odonto;
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="#" onclick="<?=$url;?>" title='Detalhes Funcion�rio' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <input type='text' name='txt_qtde_plano_odonto[]' id='txt_qtde_plano_odonto<?=$i;?>' value="<?=number_format($qtde_plano_odonto, 2, ',', '.');?>" title='Qtde de Planos Odontol�gicos' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_vlr_vale[]' id='txt_vlr_vale<?=$i;?>' value="<?=number_format($vlr_vale, 2, ',', '.');?>" title='40% do Sal�rio' size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>' size='10'>
        </td>
    </tr>
<?
//Essa vari�vel aqui eu apresento mais abaixo no fim do loop ...
        $total_vlr_fatura+= $vlr_vale;
        $cont++;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            Total Vlr Fatura R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_fatura' value='<?=number_format($total_vlr_fatura, 2, ',', '.');?>' title='Total Vlr Fatura' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
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