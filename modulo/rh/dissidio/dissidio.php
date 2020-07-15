<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='confirmacao'>INDEXAÇÃO SALARIAL INCLUIDA COM SUCESSO.</font>";

if(!empty($_POST['hdd_funcionario'])) {
//Data e Hora de Atualização dos Dados do Funcionário ...
    $data_sys = date('Y-m-d H:i:s');
/******************************************************************************************/
//Atualizando c/ os Novos Salários dos Funcionários ...
    foreach($_POST['hdd_funcionario'] as $i => $id_funcionario_loop) {
        $sql = "SELECT tipo_salario 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $sql = "UPDATE `funcionarios` SET `salario_pd` = '".$_POST['txt_salario_pd_novo'][$i]."', `salario_pf` = '".$_POST['txt_salario_pf_novo'][$i]."' WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
        bancos::sql($sql);
//Cálculo de % de Aumento do Salário do Funcionário p/ Registrar + abaixo no Acompanhamento ...
        if($_POST['txt_salario_pd_ant'][$i] != 0) {
            $perc_salario_pd = ($_POST['txt_salario_pd_novo'][$i] / $_POST['txt_salario_pd_ant'][$i] - 1) * 100;
            $perc_salario_pd = number_format($perc_salario_pd, 1, ',', '.');
        }
        if($txt_salario_pf_ant[$i] != 0) {
            $perc_salario_pf = ($_POST['txt_salario_pf_novo'][$i] / $_POST['txt_salario_pf_ant'][$i] - 1) * 100;
            $perc_salario_pf = number_format($perc_salario_pf, 1, ',', '.');
        }
        if($txt_salario_total_ant[$i] != 0) {
            $perc_salario_total = ($_POST['txt_salario_total_novo'][$i] / $_POST['txt_salario_total_ant'][$i] - 1) * 100;
            $perc_salario_total = number_format($perc_salario_total, 1, ',', '.');
        }
        $observacao = 'Dissídio - aumento pd -> '.$perc_salario_pd.' %, pf -> '.$perc_salario_pf.' %, total -> '.$perc_salario_total.' %';
        $sql = "INSERT INTO `funcionarios_acompanhamentos` (`id_funcionario_acompanhamento`, `id_funcionario_registrou`, `id_funcionario_acompanhado`, `observacao`, `data_ocorrencia`) VALUES (NULL, '$_SESSION[id_funcionario]', '$id_funcionario_loop', '$observacao', '$data_sys') ";
        bancos::sql($sql);
    }
    $valor = 1;
}

/*Listagem de Funcionários independente da Empresa, que ainda estão trabalhando
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
$sql = "SELECT c.`cargo`, f.`id_funcionario`, f.`tipo_salario`, f.`salario_pd`, f.`salario_pf`, f.`nome`, 
        f.`data_admissao` 
        FROM `funcionarios` f 
        INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
        WHERE f.`status` < '3' 
        AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
$campos = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Dissídio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular_simulativo() {
//Percentagem 
    if(!texto('form', 'txt_percentagem', '1', '-1234567890,.', 'PERCENTAGEM DO DISSÍDIO', '1')) {
        return false
    }
    var elementos   = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas  = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas  = (elementos['hdd_funcionario[]'].length)
    }
//Tratamento nos objetos Vlr Pedido e Vlr Liberado p/ gravar os objetos no BD ...
    for(var i = 0; i < linhas; i++) {
        if(document.form.txt_percentagem.value != '') {//Se estiver preenchida a %, então ...
            var percentagem         = eval(strtofloat(document.form.txt_percentagem.value))
            
            var salario_pd_ant      = eval(strtofloat(document.getElementById('txt_salario_pd_ant'+i).value))
            var salario_pd_novo     = ((percentagem / 100 + 1) * salario_pd_ant)
            document.getElementById('txt_salario_pd_novo'+i).value      = salario_pd_novo
            document.getElementById('txt_salario_pd_novo'+i).value      = arred(document.getElementById('txt_salario_pd_novo'+i).value, 2, 1)

            var salario_pf_ant      = eval(strtofloat(document.getElementById('txt_salario_pf_ant'+i).value))
            var salario_pf_novo     = ((percentagem / 100 + 1) * salario_pf_ant)
            
            document.getElementById('txt_salario_pf_novo'+i).value      = salario_pf_novo
            document.getElementById('txt_salario_pf_novo'+i).value      = arred(document.getElementById('txt_salario_pf_novo'+i).value, 2, 1)

            document.getElementById('txt_salario_total_novo'+i).value   = salario_pd_novo + salario_pf_novo
            document.getElementById('txt_salario_total_novo'+i).value   = arred(document.getElementById('txt_salario_total_novo'+i).value, 2, 1)
        }else {//Se a % estiver vazia, eu limpo os campos com o valor de Salário Novo ...
            document.getElementById('txt_salario_pd_novo'+i).value      = ''
            document.getElementById('txt_salario_pf_novo'+i).value      = ''
            document.getElementById('txt_salario_total_novo'+i).value   = ''
        }
    }
}

function validar() {
//Percentagem 
    if(!texto('form', 'txt_percentagem', '1', '-1234567890,.', 'PERCENTAGEM DO DISSÍDIO', '1')) {
        return false
    }
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATUALIZAR COM O NOVO SALÁRIO ?')
    if(resposta == true) {
        var elementos   = document.form.elements
        //Prepara a Tela p/ poder gravar no BD ...
        if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
            var linhas  = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas  = (elementos['hdd_funcionario[]'].length)
        }
        //Tratamento nos objetos p/ gravar os objetos no BD ...
        for(var i = 0; i < linhas; i++) {
//Desabilito esses campos na hora de Gravar p/ saber o quanto que se teve de aumento ...
            document.getElementById('txt_salario_pd_ant'+i).disabled        = false
            document.getElementById('txt_salario_pf_ant'+i).disabled        = false
            document.getElementById('txt_salario_total_ant'+i).disabled     = false
            document.getElementById('txt_salario_total_novo'+i).disabled    = false
            document.getElementById('txt_salario_pd_ant'+i).value           = strtofloat(document.getElementById('txt_salario_pd_ant'+i).value)
            document.getElementById('txt_salario_pf_ant'+i).value           = strtofloat(document.getElementById('txt_salario_pf_ant'+i).value)
            document.getElementById('txt_salario_total_ant'+i).value        = strtofloat(document.getElementById('txt_salario_total_ant'+i).value)
            document.getElementById('txt_salario_total_novo'+i).value       = strtofloat(document.getElementById('txt_salario_total_novo'+i).value)
//Tratamento com os campos na hora de Gravar no Banco de Dados ...
            document.getElementById('txt_salario_pd_novo'+i).value          = strtofloat(document.getElementById('txt_salario_pd_novo'+i).value)
            document.getElementById('txt_salario_pf_novo'+i).value          = strtofloat(document.getElementById('txt_salario_pf_novo'+i).value)
        }
    }else {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='80%' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Dissídio - <font color='yellow'>Taxa: </font>
            <input type='text' name='txt_percentagem' title='Digite a % do Dissídio' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='7' maxlength='6' class='caixadetexto'> %
            <input type='button' name='cmd_calculo_simulativo' value='Cálculo Simulativo' title='Cálculo Simulativo' onclick='calcular_simulativo()' class='botao'>
            <input type='button' name='cmd_limpar_calculo' value='Limpar Cálculo' title='Limpar Cálculo' onclick="document.form.reset()" style='color:#ff9900' class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Funcionário
        </td>
        <td rowspan='2'>
            Data de <br>Admissão
        </td>
        <td rowspan='2'>
            Cargo
        </td>
        <td rowspan='2'>
            Tipo
        </td>
        <td colspan='2'>
            Salário PD R$
        </td>
        <td colspan='2'>
            Salário PF R$
        </td>
        <td colspan='2'>
            Salário Total R$
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ant
        </td>
        <td>
            Novo
        </td>
        <td>
            Ant
        </td>
        <td>
            Novo
        </td>
        <td>
            Ant
        </td>
        <td>
            Novo
        </td>
    </tr>
<?
//Listando os Holerith(s) Crédito(s) ...
    for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
        $url = "javascript:nova_janela('../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'POP', '', '', '', '', 580, 750, 'c', 'c', '', '', 's', 's', '', '', '') ";
        $salario_pd     = $campos[$i]['salario_pd'];
        $salario_pf     = $campos[$i]['salario_pf'];
        $salario_total  = $salario_pd + $salario_pf;
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left' onclick="<?=$url;?>">
            <a href="<?=$url;?>" title="Visualizar Detalhes" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_admissao'], '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_salario'] == 1) {
                echo '<font title="Horista" style="cursor:help">Hs';
            }else {
                echo '<font title="Horista" style="cursor:help">M';
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_salario_pd_ant[]' id='txt_salario_pd_ant<?=$i;?>' value='<?=number_format($salario_pd, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_salario_pd_novo[]' id='txt_salario_pd_novo<?=$i;?>' size='9' maxlength='8' onfocus="document.form.txt_percentagem.focus()" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_salario_pf_ant[]' id='txt_salario_pf_ant<?=$i;?>' value='<?=number_format($salario_pf, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_salario_pf_novo[]' id='txt_salario_pf_novo<?=$i;?>' size='9' maxlength='8' onfocus="document.form.txt_percentagem.focus()" class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_salario_total_ant[]' id='txt_salario_total_ant<?=$i;?>' value='<?=number_format($salario_total, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_salario_total_novo[]' id='txt_salario_total_novo<?=$i;?>' value='<?=$dias_horas_trabalhadas;?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <!--Aqui é o Id do Holerith (Crédito) -->
        <input type='hidden' name='hdd_funcionario[]' id='hdd_funcionario<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>'>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>