<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE COMBUSTÍVEL ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
    $data_sys = date('Y-m-d H:i:s');
//Primeiro apaga-se todos os vales do Tipo Combustível p/ poder gerar Novos Vales Válidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '3' 
            AND `data_debito` = '$_POST[cmb_data_holerith]' ";
    bancos::sql($sql);
//Aqui nesse loop eu disparo todos os funcionários da Empresa selecionada ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {
/*Se essa opção for = 'SIM', então significa que eu posso gerar vales com Valor Negativo 
para o funcionário ...*/
        if($_POST['hdd_reembolso_combustivel'][$i] == 'S') {
            if($_POST['txt_vlr_vale'][$i] != 0) {//Valores positivos e negativos ...
                $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor_fatura`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '3', '".$_POST['txt_vlr_mes'][$i]."', '".$_POST['txt_vlr_vale'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', 'PF', '$data_sys') ";
                bancos::sql($sql);
            }
/*Se essa opção não estiver marcada, então significa que eu só posso gerar vales com Valor Positivo 
para o funcionário ...*/
        }else {
            if($_POST['txt_vlr_vale'][$i] > 0) {//Só valores positivos ...
                $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor_fatura`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '3', '".$_POST['txt_vlr_mes'][$i]."', '".$_POST['txt_vlr_vale'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', 'PF', '$data_sys') ";
                bancos::sql($sql);
            }
        }
    }
/*********************************************************************************************/
/****E-mail Informativo p/ que se Compare o Total do Convênio c/ o Total da Folha Impressa****/
/*********************************************************************************************/
    $mensagem_email = 'O valor do Combustível para a Data do Holerith '.data::datetodata($_POST['cmb_data_holerith'], '/').' ficou em R$ '.$_POST['txt_total_vlr_fatura'].'.';
    comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br; sandra@grupoalbafer.com.br', '', 'Combustível - Data de Holerith '.data::datetodata($_POST[cmb_data_holerith], '/'), $mensagem_email);
/*********************************************************************************************/
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>&valor=1'
    </Script>
<?
}else {
/****************************************************************************************************/
//Aqui eu já deixo carregada essa variável porque vou estar utilizando essa nos cálculos em PHP e JavaScript
    $valor_lit_comb = genericas::variavel(29);
//Listagem de Funcionários que ainda estão trabalhando e que estão com a marcação de Celular ...
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 porque estes não são funcionários, 
simplesmente só possuem cadastrado no Sistema p/ poder acessar algumas telas ...*/
//Aqui eu posso exibir os diretores Roberto 62, Dona Sandra 66 e Wilson 68 - Exceção
    $sql = "SELECT `id_funcionario`, `nome`, `qtde_litros_combustivel`, `reembolso_combustivel` 
            FROM `funcionarios` 
            WHERE `status` < '3' 
            AND `debitar_combustivel` = 'S' 
            AND `id_funcionario` NOT IN (1, 2, 91, 114) ORDER BY `nome` ";
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
<title>.:: Incluir Vale(s) - Combustível ::.</title>
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
        document.getElementById('txt_vlr_mes'+i).value  = strtofloat(document.getElementById('txt_vlr_mes'+i).value)
        document.getElementById('txt_vlr_vale'+i).value = strtofloat(document.getElementById('txt_vlr_vale'+i).value)
//Habilito a caixa p/ poder gravar no Banco ...
        document.getElementById('txt_vlr_mes'+i).disabled   = false
        document.getElementById('txt_vlr_vale'+i).disabled  = false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
//Desabilito esse campo, porque o valor do mesmo será enviado via e-mail à Diretoria ...
    document.form.txt_total_vlr_fatura.disabled = false
}

function calcular(indice) {
//Igualando as variáveis ...
    var vlr_direito = eval(strtofloat(document.getElementById('txt_vlr_direito'+indice).value))
    if(document.getElementById('txt_vlr_mes'+indice).value != '' && document.getElementById('txt_vlr_mes'+indice).value != '0,00') {//Se este campo estiver preenchido ...
        vlr_do_mes = eval(strtofloat(document.getElementById('txt_vlr_mes'+indice).value))
    }else {
        vlr_do_mes = 0
    }
//Aqui eu cálculo o Campo Valor do Vale ...
    document.getElementById('txt_vlr_vale'+indice).value = vlr_do_mes - vlr_direito
    document.getElementById('txt_vlr_vale'+indice).value = arred(document.getElementById('txt_vlr_vale'+indice).value, 2, 1)

    var total_vlr_fatura = 0
    
    var elementos = document.form.elements
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
//Aqui eu faço um somatório do Total Vlr Fatura ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_vlr_mes'+i).value != '' && document.getElementById('txt_vlr_mes'+i).value != '0,00') {
            var vlr_mes = eval(strtofloat(document.getElementById('txt_vlr_mes'+i).value))
        }else {
            var vlr_mes = 0
        }
        total_vlr_fatura+= vlr_mes
    }
    document.form.txt_total_vlr_fatura.value = total_vlr_fatura
    document.form.txt_total_vlr_fatura.value = arred(document.form.txt_total_vlr_fatura.value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$_GET['cmb_data_holerith'];?>'>
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
            Incluir Vale(s) - Combustível
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($_GET['cmb_data_holerith'], '/');?>
        </td>
        <td colspan='5'>
            <font color='yellow'>
                Valor do Litro do Combustível: 
            </font>
            <?='R$ '.number_format($valor_lit_comb, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Qtde Litros
        </td>
        <td>
            Vlr Direito
        </td>
        <td>
            Vlr do Mês
        </td>
        <td>
            Vlr Vale
        </td>
        <td>
            Reembolso
        </td>
    </tr>
<?
    $cont = 0;
    for($i = 0; $i < $linhas; $i++) {
//Cálculos e controle com o Pop-Up ... 
        $url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
        $qtde_litros_combustivel = $campos[$i]['qtde_litros_combustivel'];
        $vlr_direito = $qtde_litros_combustivel * $valor_lit_comb;
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="#" onclick="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <input type='text' name='txt_qtde_litros[]' id='txt_qtde_litros<?=$i;?>' value="<?=number_format($qtde_litros_combustivel, 2, ',', '.');?>" title='Qtde de Litros' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_vlr_direito[]' id='txt_vlr_direito<?=$i;?>' value="<?=number_format($vlr_direito, 2, ',', '.');?>" title='Valor de Direito' size='10' class='textdisabled' disabled>
        </td>
        <td>
        <?
            $sql = "SELECT `valor_fatura` 
                    FROM `vales_dps` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `tipo_vale` = '3' 
                    AND `data_debito` = '$_GET[cmb_data_holerith]' LIMIT 1 ";
            $campos_vales_dps   = bancos::sql($sql);
            $vlr_mes            = (count($campos_vales_dps) == 1) ? number_format($campos_vales_dps[0]['valor_fatura'], 2, ',', '.') : '';
        ?>
            <input type='text' name='txt_vlr_mes[]' id='txt_vlr_mes<?=$i;?>' value='<?=$vlr_mes;?>' title='Valor do Mês' size='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>')" tabindex="<?='1'.$cont;?>" class='caixadetexto'>
        </td>
        <td>
        <?
            $vlr_vale = $campos_vales_dps[0]['valor_fatura'] - $vlr_direito;
        ?>
            <input type='text' name='txt_vlr_vale[]' id='txt_vlr_vale<?=$i;?>' value='<?=number_format($vlr_vale, 2, ',', '.');?>' title='Valor do Vale' size='10' class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' value='<?=$campos[$i]['id_funcionario'];?>'>
        </td>
        <td>
        <?
            if($campos[$i]['reembolso_combustivel'] == 'S') {
                $value = 'S';
                $reembolso = 'Sim';
            }else {
                $value = 'N';
                $reembolso = 'Não';
            }
        ?>
            <input type='hidden' name='hdd_reembolso_combustivel[]' value='<?=$value;?>'>
            <?=$reembolso;?>
        </td>
    </tr>
<?
//Essa variável aqui eu apresento mais abaixo no fim do loop ...
        $total_vlr_fatura+= $vlr_mes;
        $cont++;
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
        <td align='right'>
            Total Vlr Fatura R$:
        </td>
        <td>
            <input type='text' name='txt_total_vlr_fatura' value='<?=number_format($total_vlr_fatura, 2, ',', '.');?>' title='Total Vlr Fatura' size='10' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
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