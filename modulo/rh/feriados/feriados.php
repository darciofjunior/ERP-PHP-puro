<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>FERIADO EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_POST[id_feriado])) {//Exclusão de Feriados ...
    $sql = "DELETE FROM `feriados` WHERE `id_feriado` = '$_POST[id_feriado]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

/******************************************************************************/
//Rotina para quando acaba de carregar a Tela ...
if(empty($_POST['cmb_opcao'])) {//Aqui é quando acabou de carregar a Tela ...
    $selected1 = 'selected';
}else {//Trago a combo de acordo com a opção escolhida pelo Usuário ...
    if($_POST['cmb_opcao'] == 1) {
        $selected1  = 'selected';
        $condicao   = "WHERE `data_feriado` BETWEEN '".date('Y-m-d')."' AND '".data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 365), '-')."'";
    }else {
        $selected2 = 'selected';
    }
}
/******************************************************************************/
?>
<html>
<head>
<title>.:: Feriado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_feriado) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_feriado.value = id_feriado
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_feriado'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Feriado(s) -
            <a href = 'incluir.php' title='Incluir Feriado'>
                <img src = '../../../imagem/menu/incluir.png' border='0'>
                <font color='#FFFF00'>
                    Incluir Feriado
                </font>
            </a>
            <br/>
            Opção: 
            <select name='cmb_opcao' title='Selecione uma Opção' onchange='document.form.submit()' class='combo'>
                <option value='1' <?=$selected1;?>>PRÓXIMOS 365 DIAS</option>
                <option value='2' <?=$selected2;?>>TODOS FERIADOS</option>
            </select>
        </td>
    </tr>
<?
//Aqui eu busco todos os Feriados cadastrados ...
    $sql = "SELECT `id_feriado`, DATE_FORMAT(`data_feriado`, '%d/%m/%Y') AS data_feriado_formatada, `data_comemorativa` 
            FROM `feriados` 
            $condicao 
            ORDER BY `data_feriado` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <font size='-1'>
                NÃO HÁ FERIADO(S) CADASTRADO(S).
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>Data do Feriado</b>
        </td>
        <td>
            <b>Data Comemorativa</b>
        </td>
        <td width='30'>
            <img src = '../../../imagem/menu/alterar.png' border='0' alt='Alterar Feriado(s)' title='Alterar Feriado(s)'>
        </td>
        <td width='30'>
            <img src = '../../../imagem/menu/excluir.png' border='0' alt='Excluir Feriado(s)' title='Excluir Feriado(s)'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['data_feriado_formatada'];?>
        </td>
        <td>
            <?=$campos[$i]['data_comemorativa'];?>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar.php?id_feriado=<?=$campos[$i]['id_feriado'];?>'" alt='Alterar Feriado' title='Alterar Feriado'>
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_feriado'];?>')" alt='Excluir Feriado' title='Excluir Feriado'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
<pre>
    Conta à Pagar:
        Se Fornecedor "SECRETARIA DA RECEITA FEDERAL - 899" ou "CAIXA ECONOMICA FEDERAL 942 - com a Conta FGTS"

        Pagar no dia do vencimento, e se não for dia útil, pagar no dia útil anterior.
        
        Senão:
            Pagar no dia do vencimento, e se não for dia útil, pagar no próximo útil.

    Conta à Receber:
        Se o vencimento for em dia útil, por mais um ou mais dias no vencimento até encontrar o próximo dia útil para saber o 
            dia do vencimento corrigido.

        Senão:
            Se o vencimento não for em dia útil, por mais um ou mais dias no vencimento até encontrar o proximo dia útil.
            Ao encontrar o próximo dia útil, por mais ou mais dias no vencimento até encontrar o proximo dia útil para saber o 
            dia do vencimento corrigido.
</pre>
</html>