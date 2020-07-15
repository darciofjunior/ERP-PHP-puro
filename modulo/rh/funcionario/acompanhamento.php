<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>ACOMPANHAMENTO REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ACOMPANHAMENTO JÁ REGISTRADO.</font>";
$mensagem[3] = "<font class='confirmacao'>ACOMPANHAMENTO EXCLUÍDO COM SUCESSO.</font>";

$data_hoje = date('Y-m-d');

//Exclui o  acompanhamento do Funcionário passado por parâmetro, caso este foi registrado errado ...
if(!empty($_POST['id_funcionario_acompanhamento'])) {
    $sql = "DELETE FROM `funcionarios_acompanhamentos` WHERE `id_funcionario_acompanhamento` = '$_POST[id_funcionario_acompanhamento]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 3;
}

//Registra um Novo acompanhamento p/ o Funcionário ...
if(!empty($_POST['txt_observacao'])) {
    $sql = "SELECT id_funcionario_acompanhamento 
            FROM `funcionarios_acompanhamentos` 
            WHERE `id_funcionario_acompanhado` = '$id_funcionario_loop' 
            AND `observacao` = '$_POST[txt_observacao]' 
            AND SUBSTRING(`data_ocorrencia`, 1, 10) = '$data_hoje' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Ainda não foi registrado esse Acompanhamento
        $sql = "INSERT INTO `funcionarios_acompanhamentos` (`id_funcionario_acompanhamento`, `id_funcionario_registrou`, `id_funcionario_acompanhado`, `observacao`, `data_ocorrencia`) VALUES (NULL, '$_SESSION[id_funcionario]', '$id_funcionario_loop', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já foi registrado esse Acompanhamento
        $valor = 2;
    }
}

//Procedimento normal de quando se carrega a Tela ...
$id_funcionario_loop = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_loop'] : $_GET['id_funcionario_loop'];

//Busca dos Dados do Funcionário com o id_funcionario_loop passado por parâmetro ...
$sql = "SELECT nome 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Acompanhamento do Funcionário ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(document.form.passo.value == '') {
//Observação
        if(document.form.txt_observacao.value == '') {
            alert('DIGITE A OBSERVAÇÃO !')
            document.form.txt_observacao.focus()
            return false
        }
    }else {
        return option('form', 'opt_funcionario', 'SELECIONE UMA OPÇÃO !')
    }
}

function excluir_acompanhamento(id_funcionario_acompanhamento) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE ACOMPANHAMENTO ?')
    if(resposta == true) {
        document.form.id_funcionario_acompanhamento.value = id_funcionario_acompanhamento
        document.form.passo.value = 1
        document.form.submit()
    }else {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_funcionario_loop' value='<?=$id_funcionario_loop;?>'>
<input type='hidden' name='id_funcionario_acompanhamento'>
<input type='hidden' name='passo'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Acompanhamento(s) do Funcionário: 
            <font color='yellow'>
                <?=$campos[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Registrar Novo Acompanhamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a Observação" cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_observacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_registrar' value='Registrar' title='Registrar' onclick="document.form.passo.value=''" style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
//Aqui busca todos os Acompanhamentos registrados ...
    $sql = "SELECT fa.*, f.nome 
            FROM `funcionarios_acompanhamentos` fa 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = fa.`id_funcionario_registrou` 
            WHERE fa.`id_funcionario_acompanhado` = '$id_funcionario_loop' 
            AND fa.`observacao` LIKE '%$_POST[txt_consultar]%' ORDER BY fa.data_ocorrencia DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Acompanhamento(s) Registrado(s)
            <p/>Consultar por Observação: <input type='text' name='txt_consultar' value='<?=$_POST[txt_consultar];?>' title='Digite o Consultar' size='42' maxlength='40' class='caixadetexto'>
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.submit()' class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário que Registrou
        </td>
        <td>
            Ocorrência
        </td>
        <td>
            Observação
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Follow Up' alt='Excluir Follow Up'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_ocorrencia'], '/').' - '.substr($campos[$i]['data_ocorrencia'], 11, 8);?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td align='center'>
        <?
//Verifico se o Funcionário q registrou esse Follow-Up é o mesmo que está logado no Sistema ...
            if($campos[$i]['id_funcionario_registrou'] == $id_funcionario) {
/*Se existir a palavra aumento, então eu não posso estar excluindo esse acompanhamento porque 
significa que esse foi gerada de forma automática ...*/
                $encontrou = strpos($campos[$i]['observacao'], 'aumento');
                if($encontrou !== false) {//Não pode excluir ...
                    echo '&nbsp;';
                }else {
        ?>
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Follow Up' alt='Excluir Follow Up' style='cursor:pointer' onclick="excluir_acompanhamento('<?=$campos[$i]['id_funcionario_acompanhamento'];?>')">
        <?			
                }
            }else {
                echo '&nbsp;';
            }
        ?>
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
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</form>
</body>
</html>