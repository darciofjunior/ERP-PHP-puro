<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CONTRIBUI��O ASSISTENCIAL ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
//Primeiro apaga-se todos os vales do Tipo Contribui��o Assistencial p/ poder gerar Novos Vales V�lidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '13' 
            AND `data_debito` = '$_POST[cmb_data_holerith]' ";
    bancos::sql($sql);
    //Aqui nesse loop eu disparo todos os funcion�rios da Empresa selecionada ...
    foreach($_POST['chkt_funcionario'] as $i => $id_funcionario_loop) {//Coloquei esse nome -> $id_funcionario_loop, p/ n�o dar conflito com o $id_funcionario da Sess�o ...
//Se o Valor do vale <> 0, ent�o eu gero vale para esse funcion�rio ...
        if($_POST['txt_vlr_fatura'][$i] != 0) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '13', '".$_POST['txt_vlr_fatura'][$i]."', '$_POST[cmb_data_holerith]', '".date('Y-m-d')."', 'PD', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
    </Script>
<?
}else {
//Aqui eu busco o "id_vale_data" referente ao M�s Corrente da Data de Holerith selecionado pelo usu�rio ...
    $sql = "SELECT id_vale_data 
            FROM `vales_datas` 
            WHERE `data` = '$cmb_data_holerith' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_vale_data = $campos[0]['id_vale_data'];
/****************************************************************************************************/
/*Listagem de Funcion�rios que ainda est�o trabalhando e que est�o com a marca��o de Contribui��o Assistencial ...
* S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...
Tamb�m n�o exibo nenhum funcion�rio que seje da Empresa Grupo -> id 4, at� porque esse tipo de Desconto
s� � feito em cima dos funcion�rios que sejam registrados, ou seja, s� existe desconto do PD*/
    $sql = "SELECT id_funcionario, id_empresa, nome, tipo_salario, salario_pd 
            FROM `funcionarios` 
            WHERE `status` < '3' 
            AND `debitar_contrib_assistencial` = 'S' 
            AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND `id_empresa` <> '4' ORDER BY nome ";
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
<title>.:: Incluir / Alterar Vale(s) - Contribui��o Assistencial ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos                   = document.form.elements
    var linhas                      = (typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') ? 1 : (elementos['chkt_funcionario[]'].length)

//Contribui��o ...
    if(!texto('form', 'txt_contribuicao', '1', '1234567890,.', 'CONTRIBUI��O', '1')) {
        return false
    }

//Verifico se temos pelo menos 1 Funcion�rio selecionado p/ que seja gerado o Vale ...
    var funcionarios_selecionados   = 0
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_funcionario'+i).checked == true) funcionarios_selecionados++
    }
//Se n�o tiver nenhum funcion�rio selecionado, o sistema for�a o preenchimento de pelo menos 1 ...
    if(funcionarios_selecionados == 0) {
        alert('SELECIONE UM FUNCION�RIO P/ GERAR O VALE !')
        return false
    }
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value   = 1
    document.form.passo.value           = 1
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_funcionario'+i).checked == true) {//Fa�o tratamento somente em cima dos Itens que est�o marcados ...
            document.getElementById('txt_vlr_fatura'+i).value       = strtofloat(document.getElementById('txt_vlr_fatura'+i).value)
            document.getElementById('txt_vlr_fatura'+i).disabled    = false//Desabilito este campo p/ poder gravar no BD ...
	}
    }
}

function calcular() {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') ? 1 : (elementos['chkt_funcionario[]'].length)

    if(document.form.txt_contribuicao.value != '') {//Se a taxa de contribui��o estiver digitada ...
        var contribuicao = eval(strtofloat(document.form.txt_contribuicao.value))
    }else {//Se n�o estiver digitada, eu igualo a Contribui��o = 0 ...
        var contribuicao = 0
    }
    var total_vlr_fatura = 0

    for(var i = 0; i < linhas; i++) {
        //S� ir� calcular o Valor de Fatura em cima das linhas selecionadas pelos Usu�rios ...
        if(document.getElementById('chkt_funcionario'+i).checked == true) {
            var salario_pd      = (document.getElementById('txt_salario_pd'+i).value != '') ? eval(strtofloat(document.getElementById('txt_salario_pd'+i).value)) : 0
            var comissao_dsr    = (document.getElementById('txt_comissao_dsr'+i).value != '') ? eval(strtofloat(document.getElementById('txt_comissao_dsr'+i).value)) : 0
            //Na coluna Vlr Total da Fatura eu calculo o Valor de Contribui��o em cima do Sal�rio PD + Comiss�o DSR ...
            document.getElementById('txt_vlr_fatura'+i).value = ((salario_pd + comissao_dsr) * contribuicao) / 100
            document.getElementById('txt_vlr_fatura'+i).value = arred(document.getElementById('txt_vlr_fatura'+i).value, 2, 1)
            total_vlr_fatura+= eval(strtofloat(document.getElementById('txt_vlr_fatura'+i).value))
        }else {
            document.getElementById('txt_vlr_fatura'+i).value = '0,00'
        }
    }
    //Aqui eu igualo o somat�rio da vari�vel "total_vlr_fatura" -> na caixa "Total Vlr Fatura R$"
    document.form.txt_total_vlr_fatura.value = total_vlr_fatura
    document.form.txt_total_vlr_fatura.value = arred(document.form.txt_total_vlr_fatura.value, 2, 1)
}
</Script>
</head>
<body onload='document.form.txt_contribuicao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Incluir / Alterar Vale(s) - Contribui��o Assistencial
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
        </td>
        <td colspan='4'>
            <font color='yellow'>
                Contribui��o: 
            </font>
            <input type='text' name='txt_contribuicao' title='Digite a Contribui��o' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" onblur='calcular()'maxlength='5' size='6' class='caixadetexto'>
            %
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' title='Selecionar Tudo' onclick="selecionar_tudo(totallinhas, '#E8E8E8');calcular()" class='checkbox'>
        </td>
        <td>
            Funcion�rio
        </td>
        <td>
            Empresa
        </td>
        <td>
            Tipo de Sal�rio
        </td>
        <td>
            Vlr Hora
        </td>
        <td>
            Sal�rio PD
        </td>
        <td>
            Comiss�o + DSR
        </td>
        <td>
            Vlr Fatura
        </td>
    </tr>
<?
        $cont = 0;
        for($i = 0; $i < $linhas; $i++) {
//C�lculos e controle com o Pop-Up ...
            $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');calcular()" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_funcionario[]' id='chkt_funcionario<?=$i;?>' value="<?=$campos[$i]['id_funcionario'];?>" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <a href="#" onclick="<?=$url;?>" title='Detalhes Funcion�rio' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Horista
                echo 'HORISTA';
            }else {//Mensalista
                echo 'MENSALISTA';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Horista
                echo number_format($campos[$i]['salario_pd'], 2, ',', '.');
            }else {//Mensalista
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_salario'] == 1) {//Horista
                $salario_pd = 220 * $campos[$i]['salario_pd'];
            }else {//Mensalista
                $salario_pd = $campos[$i]['salario_pd'];
            }
        ?>
            <input type='text' name='txt_salario_pd[]' id='txt_salario_pd<?=$i;?>' value='<?=number_format($salario_pd, 2, ',', '.');?>' title='Sal�rio PD' size='10' class='textdisabled' disabled>
        </td>
        <td>
        <?
//Aqui eu busco a Comiss�o do Funcion�rio referente ao M�s Corrente da Data de Holerith ...
            $sql = "SELECT `comissao_alba`, `comissao_tool`, `comissao_grupo`, `dsr_alba`, `dsr_tool`, `dsr_grupo` 
                    FROM `funcionarios_vs_holeriths` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `id_vale_data` = '$id_vale_data' ";
            $campos_com_dsr = bancos::sql($sql);
            if(count($campos_com_dsr) == 1) {//Se encontrar alguma ...
                if($campos[$i]['id_empresa'] == 1) {//Albafer ...
                    //$comissao_dsr = $campos_com_dsr[0]['comissao_alba'] + $campos_com_dsr[0]['dsr_alba'];
//Na pr�pria comiss�o j� est� embutido o DSR ...
                    $comissao_dsr = $campos_com_dsr[0]['comissao_alba'];
                }else if($campos[$i]['id_empresa'] == 2) {//Tool Master ...
                    //$comissao_dsr = $campos_com_dsr[0]['comissao_tool'] + $campos_com_dsr[0]['dsr_tool'];
//Na pr�pria comiss�o j� est� embutido o DSR ...
                    $comissao_dsr = $campos_com_dsr[0]['comissao_tool'];
                }
            }else {//N�o encontrou comiss�o nenhuma p/ o Funcion�rio ...
                $comissao_dsr = 0;
            }
        ?>
            <input type='text' name='txt_comissao_dsr[]' id='txt_comissao_dsr<?=$i;?>' value='<?=number_format($comissao_dsr, 2, ',', '.');?>' title='Digite a Comiss�o DSR' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');focus(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_vlr_fatura[]' id='txt_vlr_fatura<?=$i;?>' value='0,00' title='Valor da Fatura' size='10' class='textdisabled' disabled>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            &nbsp;
        </td>
        <td align='right'>
            Total Vlr Fatura R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_fatura' value='0,00' title='Total do Vlr Fatura' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.reset();document.form.txt_contribuicao.focus()' style='color:#ff9900' class='botao'>
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
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre><font color='darkblue'>
* Quando o Tipo de Sal�rio � Horista, ent�o o c�lculo do Sal�rio PD = Vlr Hora * 220
</font>
</pre>
<?}?>