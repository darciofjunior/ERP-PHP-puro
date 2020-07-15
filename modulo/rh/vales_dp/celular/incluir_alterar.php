<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CELULAR ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['cmb_data_holerith'])) {
//Primeiro apaga-se todos os vales do Tipo Celular p/ poder gerar Novos Vales Válidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '9' 
            AND `data_debito` = '$_POST[cmb_data_holerith]' ";
    bancos::sql($sql);
//Gerando Vales p/ todos os Funcionários que foram submetidos da Tela Anterior ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {
//Se o Valor do vale > 0, então eu gero vale para esse funcionário ...
        if($_POST['txt_vlr_vale'][$i] > 0) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor_fatura`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '9', '".$_POST['txt_vlr_fatura'][$i]."', '".$_POST['txt_vlr_vale'][$i]."', '$_POST[cmb_data_holerith]', '".date('Y-m-d')."', 'PF', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
/*********************************************************************************************/
/****E-mail Informativo p/ que se Compare o Total do Convênio c/ o Total da Folha Impressa****/
/*********************************************************************************************/
    $mensagem_email = 'O valor do Celular para a Data do Holerith '.data::datetodata($_POST['cmb_data_holerith'], '/').' ficou em R$ '.$_POST['txt_total_vlr_fatura'].'.';
    comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br; sandra@grupoalbafer.com.br', '', 'Celular - Data de Holerith '.data::datetodata($_POST[cmb_data_holerith], '/'), $mensagem_email);
/*********************************************************************************************/
}
/****************************************************************************************************/
//Listagem de Funcionários que ainda estão trabalhando e que estão com a marcação de Celular ...
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 porque estes não são funcionários, 
simplesmente só possuem cadastrado no Sistema p/ poder acessar algumas telas ...*/
//Aqui eu posso exibir os diretores Roberto 62, Dona Sandra 66 e Wilson 68 - Exceção
$sql = "SELECT `id_funcionario`, `nome` 
        FROM `funcionarios` 
        WHERE `status` < '3' 
        AND `debitar_celular` = 'S' 
        AND `id_funcionario` NOT IN (1, 2, 91, 114) ORDER BY nome ";
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

//Procedimento normal de quando se carrega a Tela ...
$cmb_data_holerith = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_data_holerith'] : $_GET['cmb_data_holerith'];
?>
<html>
<head>
<title>.:: Incluir / Alterar Vale(s) - Celular ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
//Trato os campos p/ poder gravar no Banco de Dados ...
        document.getElementById('txt_vlr_fatura'+i).value   = strtofloat(document.getElementById('txt_vlr_fatura'+i).value)
        document.getElementById('txt_vlr_vale'+i).value     = strtofloat(document.getElementById('txt_vlr_vale'+i).value)
//Habilito os campos p/ poder gravar no Banco de Dados ...
        document.getElementById('txt_vlr_fatura'+i).disabled    = false
        document.getElementById('txt_vlr_vale'+i).disabled      = false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value   = 1
    document.form.passo.value           = 1
//Desabilito esse campo, porque o valor do mesmo será enviado via e-mail à Diretoria ...
    document.form.txt_total_vlr_fatura.disabled = false
}

function calcular(indice) {
    var elementos           = document.form.elements
    var total_vlr_fatura    = 0
        
    if(document.getElementById('txt_vlr_fatura'+indice).value != '') {//Se este campo estiver preenchido ...
        document.getElementById('txt_vlr_vale'+indice).value = document.getElementById('txt_vlr_fatura'+indice).value
    }else {
        document.getElementById('txt_vlr_vale'+indice).value = '0,00'
    }
    
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_vlr_fatura'+i).value != '') {
            var vlr_fatura = eval(strtofloat(document.getElementById('txt_vlr_vale'+i).value))
        }else {
            var vlr_fatura = 0
        }
        total_vlr_fatura+= vlr_fatura
    }
    document.form.txt_total_vlr_fatura.value = total_vlr_fatura
    document.form.txt_total_vlr_fatura.value = arred(document.form.txt_total_vlr_fatura.value, 2, 1)
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        /*Significa que este arquivo dessa vez foi chamado como sendo Pop-UP e que estou dentro da 
        Tela de Itens de Vale e sendo assim recarrego a Tela de Baixo ...*/
        if(opener != null) opener.recarregar_tela()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*********************************Controles de Tela*********************************-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<!--***********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir / Alterar Vale(s) Celular - 
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Vlr Fatura
        </td>
        <td>
            Vlr Vale
        </td>
    </tr>
<?
    $indice = 0;
    for($i = 0; $i < $linhas; $i++) {
        $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' align='center'>
        <td align="left">
            <a href="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
        <?
            /*Verifico se existe algum Vale do Tipo Celular que foi gerado na Data de Holerith passada 
            por parâmetro do Funcionário do Loop ...*/
            $sql = "SELECT `valor_fatura` 
                    FROM `vales_dps` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `tipo_vale` = '9' 
                    AND `data_debito` = '$cmb_data_holerith' LIMIT 1 ";
            $campos_vale    = bancos::sql($sql);
            $vlr_fatura     = (count($campos_vale) == 1) ? number_format($campos_vale[0]['valor_fatura'], 2, ',', '.') : '';
        ?>
            <input type='text' name='txt_vlr_fatura[]' id='txt_vlr_fatura<?=$i;?>' value='<?=$vlr_fatura;?>' title='Digite o Valor da Fatura' size='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>')" tabindex='<?='1'.$indice;?>' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_vlr_vale[]' id='txt_vlr_vale<?=$i;?>' value='<?=number_format($campos_vale[0]['valor_fatura'], 2, ',', '.');?>' title='Valor do Vale' size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
    </tr>
<?
        $total_vlr_fatura+= $campos_vale[0]['valor_fatura'];
        $indice++;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td align='right'>
            Total Vlr Fatura R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_fatura' value="<?=number_format($total_vlr_fatura, 2, ',', '.');?>" title='Total do Vlr Fatura' size='10' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>