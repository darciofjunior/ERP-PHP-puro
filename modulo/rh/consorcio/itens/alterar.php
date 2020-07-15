<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) ALTERADO(S) COM SUCESSO.</font>";

if(!empty($_POST['id_consorcio_vs_funcionario'])) {
    $data_contemplado = ($_POST['chkt_contemplado'] == 1) ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
//Atualizando os dados do Funcionário no Consórcio ...
    $sql = "UPDATE `consorcios_vs_funcionarios` SET `valor_premio` = '$_POST[txt_valor_premio]', `contemplado` = '$_POST[chkt_contemplado]', `data_contemplado` = '$data_contemplado' WHERE `id_consorcio_vs_funcionario` = '$_POST[id_consorcio_vs_funcionario]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}

$id_consorcio = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_consorcio'] : $_GET['id_consorcio'];

//Seleção da qtde de Funcionários que existe no Consórcio
$sql = "SELECT COUNT(id_consorcio_vs_funcionario) AS qtde_itens 
	FROM `consorcios_vs_funcionarios` cf 
	WHERE `id_consorcio` = '$id_consorcio' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Aqui eu exibo os Funcionários que estão participando do Consórcio ...
$sql = "SELECT cf.*, f.nome 
	FROM `consorcios_vs_funcionarios` cf 
	INNER JOIN `funcionarios` f ON f.id_funcionario = cf.id_funcionario 
	WHERE `id_consorcio` = '$id_consorcio' ";
if(empty($posicao)) $posicao = 1;
$campos         = bancos::sql($sql, ($posicao - 1), $posicao);
$id_consorcio_vs_funcionario = $campos[0]['id_consorcio_vs_funcionario'];
?>
<html>
<head>
<title>.:: Alterar Item ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//Valor do Prêmio
        if(!texto('form', 'txt_valor_premio', '1', '1234567890,.', 'VALOR DO PRÊMIO', '2')) {
            return false
        }
    }
    limpeza_moeda('form', 'txt_valor_premio, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao;
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_valor_premio.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_consorcio' value="<?=$id_consorcio;?>">
<input type='hidden' name='id_consorcio_vs_funcionario' value="<?=$id_consorcio_vs_funcionario;?>">
<!--Controle de Tela-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Item
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <b>Nome:</b>
        </td>
        <td width='80%'>
            <?=$campos[0]['nome'];?>
        </td>
    </tr>
    <?
        if($campos[0]['contemplado'] == 1) $checked = 'checked';
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Contemplado:</b>
        </td>
        <td>
            <input type='checkbox' name='chkt_contemplado' value='1' title='Contemplado' class='checkbox' <?=$checked;?>>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Valor do Prêmio:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_premio' value="<?=number_format($campos[0]['valor_premio'], 2, ',', '.');?>" title='Digite o Valor do Prêmio' size='10' maxlength='8' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_valor_premio.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    if($x<19 && $i>($posicao-10)) {
                        $x++;
                        echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                    }
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>