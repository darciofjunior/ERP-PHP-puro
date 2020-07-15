<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/concorrentes/concorrentes.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>FOLLOW-UP REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FOLLOW-UP JÁ REGISTRADO.</font>";
$mensagem[3] = "<font class='confirmacao'>FOLLOW-UP EXCLUÍDO COM SUCESSO.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_concorrente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_concorrente'] : $_GET['id_concorrente'];

if(!empty($_POST['txt_observacao'])) {
/*Verifico se já foi registrado um Follow-Up anterior com a mesma Observação, na mesma Data p/ o Concorrente 
passado por parâmetro etc ...*/
    $sql = "SELECT id_concorrente_follow_up 
            FROM `concorrentes_follow_ups` 
            WHERE `id_concorrente` = '$_POST[id_concorrente]' 
            AND `id_funcionario` = '$_SESSION[id_funcionario]' 
            AND SUBSTRING(`data_sys`, 1, 10) = '".date('Y-m-d')."' 
            AND `observacao` = '".strtolower($_POST[txt_observacao])."' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Ainda não foi registrado esse Follow-UP
        $sql = "INSERT INTO `concorrentes_follow_ups` (`id_concorrente_follow_up`, `id_concorrente`, `id_funcionario`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_concorrente]', '$_SESSION[id_funcionario]', '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já foi registrado esse Follow-UP
        $valor = 2;
    }
}else if(!empty($_POST['opt_concorrente_follow_up'])) {//Exclusão do Follow-up do Concorrente, caso este foi registrado errado ...
    $sql = "DELETE FROM `follow_ups_concorrentes` WHERE `id_concorrente_follow_up` = '$_POST[opt_concorrente_follow_up]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 3;
}
?>
<html>
<title>.:: Follow-up(s) do Concorrente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(document.form.hdd_acao.value == 1) {//Significa que o Usuário está tentando gerar um registro de Follow-UP ...
        //Observação
        if(document.form.txt_observacao.value == '') {
            alert('DIGITE A OBSERVAÇÃO !')
            document.form.txt_observacao.focus()
            return false
        }
    }else if(document.form.hdd_acao.value == 2) {//Significa que o Usuário está tentando excluir um registro de Follow-UP ...
        return option('form', 'opt_concorrente_follow_up', 'SELECIONE UMA OPÇÃO !')
    }
}
</Script>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_concorrente' value='<?=$id_concorrente;?>'>
<input type='hidden' name='hdd_acao'>
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Registrar Follow-up p/ o Concorrente
            <font color='yellow'>
            <?
                //Aqui eu busco o nome do Concorrente passado por parâmetro ...
                $sql = "SELECT nome 
                        FROM `concorrentes` 
                        WHERE `id_concorrente` = '$id_concorrente' LIMIT 1 ";
                $campos_concorrente = bancos::sql($sql);
                echo $campos_concorrente[0]['nome'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='255' cols='80' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_observacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_registrar' value='Registrar' title='Registrar' style='color:green' onclick='document.form.hdd_acao.value=1' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.html5Lightbox.finish()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
//Verifico se existe(m) Follow-Up(s) Registrado(s) p/ o Concorrente passado por parâmetro ...
    $sql = "SELECT f.`nome`, cfu.`id_concorrente_follow_up`, cfu.`observacao`, cfu.`data_sys` 
            FROM `concorrentes_follow_ups` cfu 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = cfu.`id_funcionario` 
            WHERE cfu.`id_concorrente` = '$id_concorrente' ORDER BY cfu.`data_sys` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Follow-up(s) Registrado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Selecionar
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Observação
        </td>
        <td>
            Data da Ocorrência
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='radio' name='opt_concorrente_follow_up' id='opt_concorrente_follow_up<?=$i;?>' value='<?=$campos[$i]['id_concorrente_follow_up'];?>'>
        </td>
        <td align='left'>
            <label for='opt_concorrente_follow_up<?=$i;?>'>
                <?=$campos[$i]['nome'];?>
            </label>
        </td>
        <td align='left'>
            <label for='opt_concorrente_follow_up<?=$i;?>'>
                <?=$campos[$i]['observacao'];?>
            </label>
        </td>
        <td>
            <label for='opt_concorrente_follow_up<?=$i;?>'>
                <?=data::datetodata($campos[$i]['data_sys'], '/').' às '.substr($campos[$i]['data_sys'], 11, 8);?>
            </label>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' onclick='document.form.hdd_acao.value=2' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>