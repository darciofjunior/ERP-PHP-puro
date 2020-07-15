<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/abono/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>ABONO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ABONO JÁ EXISTENTE NESSA DATA DE HOLERITH.</font>";

$data_emissao   = date('Y-m-d');
$data_sys       = date('Y-m-d H:i:s');

if(!empty($_POST['txt_percentagem_abono'])) {
    if(!empty($_POST['cmb_funcionario'])) {//Significa que será gerado o Abono p/ apenas 1 único funcionário em Específico ...
        if(!empty($_POST['txt_valor_manual']) && $_POST['txt_valor_manual'] > 0) $condicao_vale_pf = " AND `descontar_pd_pf` = 'PF' ";
//Primeiro passo é verificar se já foi inserido algum Abono na Data de Holerith e Funcionário especificados ...
        $sql = "SELECT `id_abono` 
                FROM `abonos` 
                WHERE `id_vale_data` = '$_POST[cmb_data_holerith]' 
                $condicao_vale_pf 
                AND `id_funcionario` = '$_POST[cmb_funcionario]' LIMIT 1 ";
        $campos_abono = bancos::sql($sql);
        if(count($campos_abono) == 0) {//Não foi gerado nenhum abono ainda ...
            if(!empty($_POST['txt_valor_manual']) && $_POST['txt_valor_manual'] > 0) {
                $valor_pf = $_POST['txt_valor_manual'];
            }else {
                $sql = "SELECT tipo_salario, salario_pd, salario_pf 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '$_POST[cmb_funcionario]' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if($campos[0]['tipo_salario'] == 1) {//Se horista, tenho que transformar o Salário em Horas
                    $valor_pd = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[0]['salario_pd'] * 220;
                    $valor_pf = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[0]['salario_pf'] * 220;
                }else {//Se for mensalista
                    $valor_pd = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[0]['salario_pd'];
                    $valor_pf = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[0]['salario_pf'];
                }
            }
//Inserindo o Abono no Salário PD ...
            if($valor_pd > 0) {//Só valores positivos ...
                $sql = "INSERT INTO `abonos` (`id_abono`, `id_funcionario`, `id_vale_data`, `taxa_abono`, `valor`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$_POST[cmb_funcionario]', '$_POST[cmb_data_holerith]', '$_POST[txt_percentagem_abono]', '$valor_pd', '$data_emissao', 'PD', '$data_sys') ";
                bancos::sql($sql);
            }
//Inserindo o Abono no Salário PF ...
            if($valor_pf > 0) {//Só valores positivos ...
                $sql = "INSERT INTO `abonos` (`id_abono`, `id_funcionario`, `id_vale_data`, `taxa_abono`, `valor`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$_POST[cmb_funcionario]', '$_POST[cmb_data_holerith]', '$_POST[txt_percentagem_abono]', '$valor_pf', '$data_emissao', 'PF', '$data_sys') ";
                bancos::sql($sql);
            }
            $valor = 1;
        }else {
            $valor = 2;
        }
    }else {//Significa que será gerado Abono para todos os funcionários ...
        $sql = "SELECT id_abono 
                FROM `abonos` 
                WHERE `id_vale_data` = '$_POST[cmb_data_holerith]' 
                AND `id_funcionario` = '' LIMIT 1 ";
        $campos_abono = bancos::sql($sql);
        if(count($campos_abono) == 0) {//Não foi gerado nenhum abono ainda ...
/*Busca de todos Funcionários que ainda estão trabalhando independente da Empresa, que estejam Data de Admissão <= 
31 de Outubro - Mês 10.
Os funcionários que possuem Admissão em Novembro e Dezembro do Ano "Atual" em que está sendo gerado o abono, 
são admitidos com a % do Dissídio no Salário, evitando assim que os mesmos peguem o Abono ...*/

/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
            $dez_primeiros_meses = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
/*Aqui eu verifico se a pessoa do RH esta gerando o Abono no Início do ano, porque caso seja isso, os funcionários do
ano anterior que foram registrados em Novembro e Dezembro, continuam sem direito ao Abono, eu retrocedo um ano ...*/
            if(in_array(date('m'), $dez_primeiros_meses)) {
                $ano_admissao = date('Y') - 1;
            }else {//Significa que a pessoa do RH está gerando o Abono no Fim do Ano, sendo assim posso manter o mesmo ano ...
                $ano_admissao = date('Y');
            }

            $sql = "SELECT `id_funcionario`, `tipo_salario`, `salario_pd`, `salario_pf`, `salario_premio` 
                    FROM `funcionarios` 
                    WHERE `status` < '3' 
                    AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
                    AND `data_admissao` <= '".$ano_admissao."-10-31' ORDER BY `nome` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
//Disparando Loop ...
            for($i = 0; $i < $linhas; $i++) {
                if($campos[$i]['tipo_salario'] == 1) {//Se horista, tenho que transformar o Salário em Horas
                    $valor_pd = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[$i]['salario_pd'] * 220;
                    $valor_pf = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[$i]['salario_pf'] * 220;
                }else {//Se for mensalista
                    $valor_pd = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[$i]['salario_pd'];
                    $valor_pf = ($_POST['txt_percentagem_abono'] / 100 ) * $campos[$i]['salario_pf'];
                }
//Inserindo o Abono no Salário PD ...
                if($valor_pd > 0) {//Só valores positivos ...
                    $sql = "INSERT INTO `abonos` (`id_abono`, `id_funcionario`, `id_vale_data`, `taxa_abono`, `valor`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_funcionario']."', '$_POST[cmb_data_holerith]', '$_POST[txt_percentagem_abono]', '$valor_pd', '$data_emissao', 'PD', '$data_sys') ";
                    bancos::sql($sql);
                }
//Inserindo o Abono no Salário PF ...
                if($valor_pf > 0) {//Só valores positivos ...
                    $sql = "INSERT INTO `abonos` (`id_abono`, `id_funcionario`, `id_vale_data`, `taxa_abono`, `valor`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_funcionario']."', '$_POST[cmb_data_holerith]', '$_POST[txt_percentagem_abono]', '$valor_pf', '$data_emissao', 'PF', '$data_sys') ";
                    bancos::sql($sql);
                }
            }
            $valor = 1;
        }else {
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Abono ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data do Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE A DATA DE HOLERITH !')) {
        return false
    }
//% de Abono
    if(!texto('form', 'txt_percentagem_abono', '1', '0123456789,.', '% DE ABONO', '1')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    return limpeza_moeda('form', 'txt_percentagem_abono, txt_valor_manual, ')
}

function incluir_data_holerith() {
    nova_janela('../vales/class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../vales/class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0)             window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_percentagem_abono.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Abono
        </td>
    </tr>
    <?
/***********************************************/
//Aqui é uma lógica p/ verificar qual que seria a próxima Data de Holerith acima da Data de Emissão ...
        $sql = "SELECT id_vale_data 
                FROM `vales_datas` 
                WHERE data >= '$data_emissao' ORDER BY data LIMIT 1 ";
        $campos_data_holerith   = bancos::sql($sql);
//Vou utilizar essa variável p/ trazer carregada na combo e numa consulta de SQL mais abaixo ...
        $data_holerith_sql      = $campos_data_holerith[0]['id_vale_data'];
/***********************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith" class='combo'>
            <?
                $data_atual_menos_60 = data::adicionar_data_hora(date('d/m/Y'), -60);
                $data_atual_menos_60 = data::datatodate($data_atual_menos_60, '-');
//Faço uma listagem dos últimos 60 dias em diante ...
                $sql = "SELECT `id_vale_data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_atual_menos_60' 
                        ORDER BY `data` ";
                echo combos::combo($sql, $data_holerith_sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = "../../../imagem/menu/incluir.png" border='0' title="Incluir Data de Holerith" alt="Incluir Data de Holerith" onClick="incluir_data_holerith()">
            &nbsp;&nbsp; <img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Data de Holerith" alt="Alterar Data de Holerith" onClick="alterar_data_holerith()">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Funcionário:
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione o Funcionário' class='combo'>
            <?
/****************************************************************************************************/
/*Busca de todos Funcionários que ainda estão trabalhando independente da Empresa, que estejam Data de Admissão <= 
31 de Outubro - Mês 10.
Os funcionários que possuem Admissão em Novembro e Dezembro do Ano "Atual" em que está sendo gerado o abno, 
são admitidos com a % do Dissídio no Salário, evitando assim que os mesmos peguem o Abono ...*/

/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
                $dez_primeiros_meses = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
/*Aqui eu verifico se a pessoa do RH esta gerando o Abono no Início do ano, porque caso seja isso, os funcionários do
ano anterior que foram registrados em Novembro e Dezembro, continuam sem direito ao Abono, eu retrocedo um ano ...*/
                if(in_array(date('m'), $dez_primeiros_meses)) {
                    $ano_admissao = date('Y') - 1;
                }else {//Significa que a pessoa do RH está gerando o Abono no Fim do Ano, sendo assim posso manter o mesmo ano ...
                    $ano_admissao = date('Y');
                }

                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `status` < '3' 
                        AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
                        AND `data_admissao` <= '".$ano_admissao."-10-31' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>% de Abono:</b>
        </td>
        <td>
            <input type='text' name='txt_percentagem_abono' title='Digite a % de Abono' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Manual:
        </td>
        <td>
            <input type='text' name='txt_valor_manual' title='Digite o Valor Manual' onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" size="7" maxlength="6" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_percentagem_abono.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>