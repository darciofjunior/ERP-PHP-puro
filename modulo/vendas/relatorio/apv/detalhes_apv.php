<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/apv/rel_apvs.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) APV(S) EM ABERTO DESSE FUNC. NESSE INTERVALO DE DATA(S).</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) APV(S) CONCLUÍDO(S) DESSE FUNCIONÁRIO NESSE INTERVALO DE DATA(S).</font>";
$mensagem[3] = "<font class='confirmacao'>APV EM ABERTO EXCLUÍDO COM SUCESSO.</font>";

//Exclusão do APV em aberto do Funcionário
if(!empty($_POST['id_log_apv'])) {
    $sql = "DELETE FROM `logs_apvs` WHERE `id_log_apv` = '$_POST[id_log_apv]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 3;
}

//Procedimento quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_funcionario_loop    = $_POST['id_funcionario_loop'];
    $data_inicial           = $_POST['data_inicial'];
    $data_final             = $_POST['data_final'];
}else {
    $id_funcionario_loop    = $_GET['id_funcionario_loop'];
    $data_inicial           = $_GET['data_inicial'];
    $data_final             = $_GET['data_final'];
}


/*Busca do Nome do Funcionário ..., a variável tem que ter esse nome por causa que já existe 
$id_funcionario na sessão do Sistema ...*/
$sql = "SELECT nome 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
$campos     = bancos::sql($sql);
$nome       = $campos[0]['nome'];
?>
<html>
<head>
<title>.:: Detalhes do Relatório APV ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function excluir_apv_em_aberto(id_log_apv) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE APV EM ABERTO ?')
    if(resposta == true) {
        document.form.id_log_apv.value  = id_log_apv
//Aqui é para não atualizar a tela abaixo desse Pop-UP
        document.form.nao_atualizar.value = 1
        atualizar_abaixo()
        document.form.submit()
    }else {
        return false
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Variáveis de Controle p/ excluir o(s) APV(s) em aberto do(s) usuário(s)-->
<input type='hidden' name='id_log_apv'>
<!--Variáveis do Filtro da Consulta da Tela de abaixo desse Pop-UP-->
<input type='hidden' name='id_funcionario_loop' value='<?=$id_funcionario_loop;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Detalhes do Relatório de APV(s) p/ o Funcionário: 
            <font color='yellow' size='-1'>
                <?=$nome;?>
            </font>
        </td>
    </tr>
</table>
<?
//Busca dos APV(s) em aberto do Funcionário ...
$sql = "SELECT la.id_log_apv, CONCAT(DATE_FORMAT(SUBSTRING(`data_ocorrencia`, 1, 10), '%d/%m/%Y'), ' - ', SUBSTRING(`data_ocorrencia`, 12, 8)) AS dados , c.razaosocial, c.nomefantasia 
        FROM `logs_apvs` la 
        INNER JOIN `clientes` c ON c.`id_cliente` = la.`id_cliente` 
        WHERE la.`id_funcionario` = '$id_funcionario_loop' 
        AND SUBSTRING(la.`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
        ORDER BY la.data_ocorrencia DESC, c.razaosocial ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
if($linhas == 0) {//Não existir nenhum APV em Aberto Registrado ...
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
}else {//Existi pelo menos um APV em Aberto Registrado ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            APV(s) em Aberto
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ocorrência
        </td>
        <td>
            Cliente
        </td>
        <td style='cursor:help'>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir APV(s) em Aberto' alt='Excluir APV(s) em Aberto'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['dados'];?>
        </td>
        <td align='left'>
        <?
            if(empty($campos[$i]['razaosocial'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir APV em Aberto' alt='Excluir APV em Aberto' onclick="excluir_apv_em_aberto('<?=$campos[$i]['id_log_apv'];?>')" style='cursor:pointer'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<?
}
?>
<br>
<?
//Busca dos APV(s) Concluídos do Funcionário
$sql = "SELECT la.id_log_apv, CONCAT(DATE_FORMAT(SUBSTRING(`data_ocorrencia`, 1, 10), '%d/%m/%Y'), ' - ', SUBSTRING(`data_ocorrencia`, 12, 8)) AS dados , c.razaosocial, c.nomefantasia 
        FROM `logs_apvs` la 
        INNER JOIN `clientes` c ON c.`id_cliente` = la.`id_cliente` 
        WHERE la.`id_funcionario` = '$id_funcionario_loop' 
        AND SUBSTRING(la.`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
        AND la.`id_cliente_follow_up` <> '0' 
        ORDER BY la.data_ocorrencia DESC, c.razaosocial ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
if($linhas == 0) {//Não existir nenhum APV concluído Registrado ...
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
<?
}else {//Existi pelo menos um APV concluído Registrado ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            APV(s) Concluído(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ocorrência
        </td>
        <td colspan='2'>
            Cliente
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['dados'];?>
        </td>
        <td colspan='3' align='left'>
        <?
            if(empty($campos[$i]['razaosocial'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
    </tr>
<?
//Aqui eu printo a resposta que foi fornecida em relação ao problema do cliente ...
        $sql = "SELECT * 
                FROM `follow_ups` 
                WHERE `id_cliente_follow_up` = ".$campos[$i]['id_cliente_follow_up']." LIMIT 1 ";
        $campos_follow_up = bancos::sql($sql);
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <img src = '../../../../imagem/chat.gif' border='0' title='Resposta do APV' alt='Resposta do APV' style='cursor:pointer'>
            <?=data::datetodata($campos_follow_up[0]['data_sys'], '/').' - '.substr($campos_follow_up[0]['data_sys'], 11, 8);?>
        </td>
        <td bgcolor='#CECECE' align='left'>
            <font title='Contato' style='cursor:help'>
                Ctt:
            </font>
            <?
                //Aqui busca o Contato na Tabela Relacional
                $sql = "SELECT nome 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato = '".$campos_follow_up[0]['id_cliente_contato']."' LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
                echo $campos_contato[0]['nome'];
            ?>
        </td>
        <td bgcolor='#CECECE' align="left">
            <b>Obs: </b>
            <?=$campos_follow_up[0]['observacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<?}?>
</form>
</body>
</html>