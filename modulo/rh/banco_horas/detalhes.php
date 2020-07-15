<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/banco_horas/relatorio.php', '../../../');

/**********************************Exclusão de Registros**********************************/
if(!empty($_GET['id_banco_hora'])) {
    $sql = "DELETE FROM `bancos_horas` WHERE `id_banco_hora` = '$_GET[id_banco_hora]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('BANCO DE HORA(S) EXCLUÍDO COM SUCESSO !!!')
        //Atualizo a tela de baixo que chamou esse Pop-DIV ...
        parent.location = parent.location.href+'<?=$parametro;?>'
    </Script>
<?
}
/**********************************Exclusão de Registros**********************************/

//Aqui busco todos os registros de Banco de Horas do Funcionário passado por parâmetro ...
$sql = "SELECT `id_banco_hora`, DATE_FORMAT(`data_lancamento`, '%d/%m/%Y') AS data_lancamento_formatada, 
        TIME_FORMAT(`qtde_horas`, '%H:%i') AS qtde_horas, TIME_FORMAT(`hora_inicial`, '%H:%i') AS hora_inicial, 
        TIME_FORMAT(`hora_final`, '%H:%i') AS hora_final, `descontar_hora_almoco`, `observacao` 
        FROM `bancos_horas` 
        WHERE `id_funcionario` = '$_GET[id_funcionario]' ORDER BY `data_lancamento` DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Detalhes de Banco de Hora(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function alterar_registro(id_banco_hora) {
    nova_janela('alterar.php?id_banco_hora='+id_banco_hora, 'CONSULTAR', '', '', '', '', '380', '680', 'c', 'c', '', '', 's', 's', '', '', '')
}

function excluir_registro(id_banco_hora) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE REGISTRO ?')
    if(resposta == true) window.location = 'detalhes.php?id_banco_hora='+id_banco_hora+'&id_funcionario=<?=$_GET['id_funcionario'];?>'
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href+'<?=$parametro;?>'
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form'>
<!--***************Controle de Tela***************-->
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr>
        <td colspan='7'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Detalhes de Banco de Hora(s) =>
            <font color='yellow'>
            <?
                //Busca o nome do Funcionário passado por parâmetro ...
                $sql = "SELECT nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '$_GET[id_funcionario]' LIMIT 1 ";
                $campos_funcionario = bancos::sql($sql);
                echo $campos_funcionario[0]['nome'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Data de Lançamento
        </td>
        <td>
            Qtde Hora(s)
        </td>
        <td>
            Hora Inicial
        </td>
        <td>
            Hora Final
        </td>
        <td>
            Observação
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['data_lancamento_formatada'];?>
        </td>
        <td>
        <?
            echo $campos[$i]['qtde_horas'];
            if($campos[$i]['descontar_hora_almoco'] == 'S') echo ' <font color="darkblue" title="Descontado Hora de Almoço" style="cursor:help"><b>(DESC. ALMOÇO)</b></font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['hora_inicial'];?>
        </td>
        <td>
            <?=$campos[$i]['hora_final'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <img src='../../../imagem/menu/alterar.png' border='0' title='Alterar Registro' alt='Alterar Registro' onClick="alterar_registro('<?=$campos[$i]['id_banco_hora'];?>')">
        </td>
        <td>
            <img src='../../../imagem/menu/excluir.png' border='0' title='Excluir Registro' alt='Excluir Registro' onClick="excluir_registro('<?=$campos[$i]['id_banco_hora'];?>')">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>