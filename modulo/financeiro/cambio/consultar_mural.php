<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/financeiro/cambio/alterar.php', '../../../');

$sql = "SELECT c.*, f.nome 
        FROM `cambios` c 
        INNER JOIN `logins` l ON l.id_login = c.id_funcionario 
        INNER JOIN `funcionarios` f ON f.id_funcionario = l.id_funcionario 
        ORDER BY c.id_cambio DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Câmbio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border="0" cellspacing="1" cellpadding="1" align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Câmbio
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Data
        </td>
        <td>
            Valor Dólar Dia UOL
        </td>
        <td>
            Valor Euro Dia UOL
        </td>
        <td>
            Data Ptax
        </td>
        <td>
            Valor Dólar Ptax BCB
        </td>
        <td>
            Valor Euro Ptax BCB
        </td>
        <td>
            Funcionário
        </td>
    </tr>
<?
	for ($i = 0;  $i < $linhas; $i++) {
            //Só o Valor do Dia que eu deixo com uma outra cor diferente p/ destacar ...	
            $color = ($i == 0) ? 'red' : '';
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <font color='<?=$color;?>'>
                <?=data::datetodata($campos[$i]['data'], '/');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=number_format($campos[$i]['valor_dolar_dia'], 4, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=number_format($campos[$i]['valor_euro_dia'], 4, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=data::datetodata($campos[$i]['data_ptax'], '/');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=number_format($campos[$i]['valor_dolar_ptax'], 4, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=number_format($campos[$i]['valor_euro_ptax'], 4, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=$campos[$i]['nome'];?>
            </font>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
                &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>